@php use App\Support\Fees; use App\Enums\QuoteStatus; @endphp
<x-layout :title="$quoteRequest->jobName().' quotes'">
    <div class="wrap stack">
        <a class="muted small" href="{{ route('quote-requests.index') }}" style="text-decoration: none">← My requests</a>
        <h1 style="font-size: clamp(1.8rem, 4vw, 2.6rem)">{{ $quoteRequest->jobName() }} in {{ $quoteRequest->city->label() }}</h1>
        <p class="muted">{{ $quoteRequest->size_label }} · {{ $quoteRequest->timingLabel() }} · ZIP {{ $quoteRequest->zip }}
            @if($range = $quoteRequest->estimateRange())
                · typical range {{ Fees::money($range['low']) }}–{{ Fees::money($range['high']) }}
            @endif</p>
        @if($quoteRequest->details)<p class="card">{{ $quoteRequest->details }}</p>@endif
        <x-media-gallery :quote-request="$quoteRequest" />
        @error('quote')<p class="error" role="alert">{{ $message }}</p>@enderror

        <h2>Quotes</h2>
        @forelse($quotes as $quote)
            <x-painter-row :provider="$quote->provider">
                <div class="amount">{{ Fees::money($quote->amount_cents) }}</div>
                <span class="meta">+ {{ Fees::money($quote->serviceFeeCents(), true) }} fee
                    @if($quote->earliest_start_date) · starts {{ $quote->earliest_start_date->format('M j') }} @endif
                    @if($quote->direct_pay) · pay painter directly @endif</span>
                @if($quote->status === QuoteStatus::Pending)
                    <form method="POST" action="{{ route('quotes.accept', $quote) }}">@csrf<button class="btn btn-primary" type="submit">Book</button></form>
                @elseif($quote->status === QuoteStatus::Accepted && $quote->booking)
                    <a class="btn" href="{{ route('bookings.show', $quote->booking) }}">View booking</a>
                @else
                    <span class="pill pill-muted">Not selected</span>
                @endif
            </x-painter-row>
            @if($quote->message)<p class="muted small" style="margin-top: -.4rem; padding-left: 1.1rem">“{{ $quote->message }}”</p>@endif
        @empty
            <div class="notice"><strong>Waiting for quotes</strong><span class="muted">We've sent your request to painters near you. You'll get an email when a quote arrives.</span></div>
        @endforelse
    </div>
</x-layout>
