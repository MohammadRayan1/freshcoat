<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $provider = $request->user()->provider->load('services');

        return view('pro.settings', [
            'provider' => $provider,
            'services' => Service::query()->orderBy('sort_order')->get(),
            'offered' => $provider->services->keyBy('id'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $provider = $request->user()->provider;

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'service_radius_miles' => ['required', 'integer', 'min:1', 'max:100'],
            'years_in_business' => ['nullable', 'integer', 'min:0', 'max:150'],
            'contractor_registration_number' => ['nullable', 'string', 'max:60'],
            'allows_direct_pay' => ['nullable', 'boolean'],
            'services' => ['array'],
            'services.*' => ['exists:services,id'],
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:200000'],
        ]);

        $provider->update([
            ...collect($data)->except(['services', 'prices'])->all(),
            'allows_direct_pay' => $request->boolean('allows_direct_pay'),
        ]);

        $sync = collect($data['services'] ?? [])->mapWithKeys(fn ($id) => [
            $id => ['book_now_price_cents' => isset($data['prices'][$id]) ? (int) round($data['prices'][$id] * 100) : null],
        ]);
        $provider->services()->sync($sync);

        return back()->with('status', 'Settings saved.');
    }
}
