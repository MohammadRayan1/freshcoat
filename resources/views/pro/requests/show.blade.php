@php use App\Support\Fees; @endphp
<x-layout title="Quote request">
    <div class="wrap split">
        <div class="stack">
            <a class="muted small" href="{{ route('pro.dashboard') }}" style="text-decoration: none">← Dashboard</a>
            <h1 style="font-size: 2.2rem">{{ $quoteRequest->jobName() }}</h1>
            <table class="ledger">
                <tr><td>Location</td><td class="amt">{{ $quoteRequest->city->label() }} {{ $quoteRequest->zip }}</td></tr>
                <tr><td>Distance</td><td class="amt">{{ $distance }} mi</td></tr>
                @unless($quoteRequest->isCustom())
                    <tr><td>Size</td><td class="amt">{{ $quoteRequest->size_label }}</td></tr>
                @endunless
                <tr><td>Timing</td><td class="amt">{{ $quoteRequest->timingLabel() }}</td></tr>
                <tr><td>Paint</td><td class="amt">{{ $quoteRequest->customer_supplies_paint ? 'Homeowner has paint' : 'You supply paint' }}</td></tr>
            </table>
            @if($quoteRequest->details)<div class="card"><span class="eyebrow">Homeowner notes</span><p>{{ $quoteRequest->details }}</p></div>@endif
            <x-media-gallery :quote-request="$quoteRequest" />
            @if($suggested)
                <p class="muted small">Typical range for this job on FreshCoat: {{ Fees::money($suggested['low']) }}–{{ Fees::money($suggested['high']) }}</p>
            @else
                <p class="muted small">Custom job: read the homeowner's notes and price it as you normally would.</p>
            @endif
        </div>
        <form class="card-raised stack-sm" method="POST" action="{{ route('pro.requests.quote', $quoteRequest) }}">
            @csrf
            <h3>{{ $existingQuote ? 'Update your quote' : 'Send a quote' }}</h3>
            <label class="field" for="amount">Your price ($)
                <input id="amount" name="amount" type="number" min="25" step="1" value="{{ old('amount', $existingQuote ? $existingQuote->amount_cents / 100 : '') }}" required>
            </label>
            <label class="field" for="earliest_start_date">Earliest start date
                <input id="earliest_start_date" name="earliest_start_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('earliest_start_date', $existingQuote?->earliest_start_date?->toDateString()) }}">
            </label>
            <label class="field" for="message">Message to homeowner
                <textarea id="message" name="message" rows="4" placeholder="What's included: prep, coats, sheen, cleanup">{{ old('message', $existingQuote?->message) }}</textarea>
            </label>
            @if($provider->allows_direct_pay || ! $provider->canAcceptCardPayments())
                <label class="check" for="direct_pay"><input id="direct_pay" type="checkbox" name="direct_pay" value="1" @checked(old('direct_pay', $existingQuote?->direct_pay ?? ! $provider->canAcceptCardPayments()))> Customer pays me directly (service fee billed to me)</label>
            @endif
            @error('amount')<p class="error" role="alert">{{ $message }}</p>@enderror
            <button class="btn btn-primary" type="submit">{{ $existingQuote ? 'Update quote' : 'Send quote' }}</button>
            <p class="muted small">On card jobs you receive your price minus Stripe processing (2.9% + 30¢ of the total charged).</p>
        </form>
    </div>
</x-layout>
