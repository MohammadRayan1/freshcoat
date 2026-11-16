<?php

namespace App\Http\Controllers\Pro;

use App\Enums\QuoteRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Notifications\QuoteReceived;
use App\Support\Fees;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function show(Request $request, QuoteRequest $quoteRequest): View
    {
        $provider = $request->user()->provider;
        $invite = $provider->quoteRequests()->whereKey($quoteRequest->id)->first();
        abort_if($invite === null, 403);

        $quoteRequest->load(['service', 'city', 'media']);

        return view('pro.requests.show', [
            'provider' => $provider,
            'quoteRequest' => $quoteRequest,
            'distance' => (float) $invite->pivot->distance_miles,
            'existingQuote' => $quoteRequest->quotes()->where('provider_id', $provider->id)->first(),
            'suggested' => $quoteRequest->estimateRange(),
        ]);
    }

    public function quote(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $provider = $request->user()->provider;
        abort_unless($provider->quoteRequests()->whereKey($quoteRequest->id)->exists(), 403);

        if ($quoteRequest->status !== QuoteRequestStatus::Open) {
            throw ValidationException::withMessages(['amount' => 'This homeowner already booked a painter.']);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:25', 'max:200000'],
            'earliest_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:2000'],
            'direct_pay' => ['nullable', 'boolean'],
        ]);

        if (! $request->boolean('direct_pay') && ! $provider->canAcceptCardPayments()) {
            throw ValidationException::withMessages(['amount' => 'Connect payouts before sending a card-payment quote, or choose "Customer pays me directly".']);
        }

        $quote = $quoteRequest->quotes()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'amount_cents' => (int) round($data['amount'] * 100),
                'earliest_start_date' => $data['earliest_start_date'] ?? null,
                'message' => $data['message'] ?? null,
                'direct_pay' => $request->boolean('direct_pay'),
            ],
        );

        $quoteRequest->user->notify(new QuoteReceived($quote));

        return redirect()->route('pro.dashboard')->with('status', 'Quote sent: '.Fees::money($quote->amount_cents).'. You\'ll be notified if they book.');
    }
}
