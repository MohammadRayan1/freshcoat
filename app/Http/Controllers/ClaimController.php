<?php

namespace App\Http\Controllers;

use App\Actions\ClaimListing;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\ClaimVerification;
use App\Models\Provider;
use App\Models\Service;
use App\Support\Fees;
use App\Support\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function __construct(private ClaimListing $claims) {}

    public function index(Request $request): View
    {
        $name = trim((string) $request->query('name', ''));
        $cityId = $request->integer('city_id') ?: null;

        $results = $name === '' ? collect() : Provider::query()
            ->with('city')
            ->where('business_name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $name).'%')
            ->when($cityId, fn ($q) => $q->where('city_id', $cityId))
            ->orderByRaw('claimed_at is not null')
            ->orderBy('business_name')
            ->limit(20)
            ->get();

        return view('claim.index', [
            'name' => $name,
            'cityId' => $cityId,
            'cities' => City::query()->launched()->orderBy('name')->get(),
            'results' => $results,
        ]);
    }

    public function sendCode(Request $request, Provider $provider): RedirectResponse
    {
        $result = $this->claims->start($provider, $request->user());

        $redirect = redirect()->route('claim.verify', $provider)
            ->with('status', 'We sent a 6-digit code to '.$result['verification']->maskedDestination().'.');

        if (app()->isLocal()) {
            $redirect->with('dev_code', $result['code']);
        }

        return $redirect;
    }

    public function showVerify(Request $request, Provider $provider): View|RedirectResponse
    {
        $verification = $this->latestVerification($request, $provider);

        if ($verification === null) {
            return redirect()->route('claim.index', ['name' => $provider->business_name]);
        }

        return view('claim.verify', ['provider' => $provider, 'verification' => $verification]);
    }

    public function verify(Request $request, Provider $provider): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $verification = $this->latestVerification($request, $provider);
        abort_if($verification === null, 404);

        $this->claims->verify($verification, $data['code']);

        return redirect()->route('pro.settings')->with('status', 'Listing claimed. Your Pro toolkit is free for '.Fees::PRO_TRIAL_MONTHS.' months. Set your services and service area to start getting requests.');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->provider) {
            return redirect()->route('pro.dashboard');
        }

        return view('claim.create', [
            'cities' => City::query()->launched()->orderBy('name')->get(),
            'services' => Service::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->provider !== null, 403);

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'city_id' => ['required', Rule::exists('cities', 'id')->whereIn('state_code', array_keys(States::launched()))],
            'contractor_registration_number' => ['nullable', 'string', 'max:60'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['exists:services,id'],
        ]);

        $city = City::findOrFail($data['city_id']);

        $provider = Provider::create([
            'user_id' => $request->user()->id,
            'city_id' => $city->id,
            'business_name' => $data['business_name'],
            'slug' => $this->uniqueSlug($data['business_name'], $city),
            'phone' => $data['phone'],
            'email' => $request->user()->email,
            'latitude' => $city->latitude,
            'longitude' => $city->longitude,
            'contractor_registration_number' => $data['contractor_registration_number'] ?? null,
            'claimed_at' => now(),
            'pro_trial_ends_at' => now()->addMonths(Fees::PRO_TRIAL_MONTHS),
        ]);
        $provider->services()->sync($data['services']);
        $request->user()->forceFill(['role' => UserRole::Provider])->save();

        return redirect()->route('pro.settings')->with('status', 'Business added. Set your prices and service area, then connect payouts.');
    }

    private function latestVerification(Request $request, Provider $provider): ?ClaimVerification
    {
        return ClaimVerification::query()
            ->where('provider_id', $provider->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();
    }

    private function uniqueSlug(string $name, City $city): string
    {
        $base = Str::slug("{$name} {$city->name} {$city->state_code}");
        $slug = $base;
        $n = 2;

        while (Provider::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
