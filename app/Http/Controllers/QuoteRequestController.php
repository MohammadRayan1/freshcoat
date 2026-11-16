<?php

namespace App\Http\Controllers;

use App\Actions\DispatchQuoteRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestMedia;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class QuoteRequestController extends Controller
{
    public const PENDING_SESSION_KEY = 'pending_quote_request';

    public function __construct(private DispatchQuoteRequest $dispatcher) {}

    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $input['media'] = $this->storeUploads($request->file('media', []));

        if ($request->user() === null) {
            $request->session()->put(self::PENDING_SESSION_KEY, $input);

            return redirect()->route('register')->with('status', 'Create a free account to send your request. We\'ll send it as soon as you sign up.');
        }

        return $this->createFor($request->user(), $input);
    }

    /**
     * Saves uploads to the private disk right away, so a guest's files survive the sign-up step.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array{path: string, mime_type: string, size_bytes: int, original_name: string}>
     */
    private function storeUploads(array $files): array
    {
        return collect($files)->map(fn (UploadedFile $file) => [
            'path' => $file->store('quote-media', QuoteRequestMedia::DISK),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize(),
            'original_name' => str($file->getClientOriginalName())->limit(200, '')->toString(),
        ])->filter(fn (array $media) => $media['path'] !== false)->values()->all();
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function createFor(User $user, array $input): RedirectResponse
    {
        if ($input['service_id'] === QuoteRequest::CUSTOM) {
            $service = null;
            $size = ['label' => 'Custom job', 'multiplier' => 1];
        } else {
            $service = Service::findOrFail($input['service_id']);
            $sizes = $service->sizes();
            $size = $sizes[(int) ($input['size'] ?? 0)] ?? $sizes[0];
        }

        $quoteRequest = $user->quoteRequests()->create([
            'service_id' => $service?->id,
            'custom_title' => $service ? null : trim($input['custom_title']),
            'city_id' => $input['city_id'],
            'size_label' => $size['label'],
            'size_multiplier' => $size['multiplier'],
            'timing' => $input['timing'],
            'customer_supplies_paint' => (bool) ($input['customer_supplies_paint'] ?? false),
            'zip' => $input['zip'],
            'details' => $input['details'] ?? null,
        ]);

        $quoteRequest->media()->createMany($input['media'] ?? []);

        $sentTo = $this->dispatcher->handle($quoteRequest);

        $message = $sentTo > 0
            ? "Request sent to {$sentTo} ".str('painter')->plural($sentTo).' near you. Quotes usually arrive within a day.'
            : 'Request saved. No painters on FreshCoat cover this area yet. We\'ll invite local painters and email you when quotes come in.';

        return redirect()->route('quote-requests.show', $quoteRequest)->with('status', $message);
    }

    public function index(Request $request): View
    {
        return view('customer.requests.index', [
            'quoteRequests' => $request->user()->quoteRequests()
                ->with(['service', 'city'])
                ->withCount('quotes')
                ->latest()
                ->get(),
        ]);
    }

    public function show(Request $request, QuoteRequest $quoteRequest): View
    {
        abort_unless($quoteRequest->user_id === $request->user()->id, 403);

        $quoteRequest->load(['service', 'city', 'media', 'quotes.provider.city', 'quotes.booking']);

        return view('customer.requests.show', [
            'quoteRequest' => $quoteRequest,
            'quotes' => $quoteRequest->quotes->sortBy('amount_cents')->values(),
        ]);
    }
}
