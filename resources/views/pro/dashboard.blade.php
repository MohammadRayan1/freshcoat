@php use App\Support\Fees; use App\Enums\BookingStatus; @endphp
<x-layout title="Painter dashboard">
    <div class="wrap stack-lg">
        <div class="stack-sm">
            <div class="row between">
                <h1 style="font-size: 2.2rem">{{ $provider->business_name }}</h1>
                <a class="btn" href="{{ route('pro.settings') }}">Settings</a>
            </div>
            @unless($provider->canAcceptCardPayments())
                <div class="notice"><strong>Connect payouts to take card payments</strong><span class="muted">Until then you can only send "customer pays me directly" quotes. <a href="{{ route('pro.payouts.connect') }}">Set up payouts with Stripe</a></span></div>
            @endunless
            @error('booking')<p class="error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="stat-row">
            <div class="stat card"><span class="eyebrow">New requests</span><strong>{{ $openRequests->count() }}</strong></div>
            <div class="stat card"><span class="eyebrow">Scheduled jobs</span><strong>{{ $bookings->where('status', BookingStatus::Scheduled)->count() }}</strong></div>
            <div class="stat card"><span class="eyebrow">Earned (after processing)</span><strong class="num">{{ Fees::money($earnedCents) }}</strong></div>
        </div>

        <section class="stack">
            <h2>New requests</h2>
            @forelse($openRequests as $request)
                <a class="card row between" href="{{ route('pro.requests.show', $request) }}" style="color: inherit; text-decoration: none">
                    <div class="stack-sm" style="gap: .2rem">
                        <h3>{{ $request->jobName() }} · {{ $request->city->label() }}</h3>
                        <span class="meta">{{ $request->size_label }} · {{ $request->timingLabel() }} · {{ $request->pivot->distance_miles }} mi away · {{ $request->created_at->diffForHumans() }}</span>
                    </div>
                    <span class="btn btn-primary">Quote</span>
                </a>
            @empty
                <p class="muted">No new requests. Widen your service radius or add services in <a href="{{ route('pro.settings') }}">settings</a> to get more.</p>
            @endforelse
        </section>

        <section class="stack">
            <h2>Bookings</h2>
            @forelse($bookings as $booking)
                <div class="card row between">
                    <div class="stack-sm" style="gap: .2rem">
                        <h3>{{ $booking->quote->quoteRequest->jobName() }} · {{ $booking->user->name }}</h3>
                        <span class="meta">{{ $booking->quote->quoteRequest->city->label() }} · {{ Fees::money($booking->amount_cents) }} · {{ $booking->payment_mode->value === 'direct' ? 'Customer pays you directly' : 'Card on FreshCoat' }}</span>
                    </div>
                    <div class="row">
                        <span class="pill {{ match($booking->status) { BookingStatus::Completed, BookingStatus::Scheduled => 'pill-ok', BookingStatus::PaymentFailed => 'pill-bad', BookingStatus::Cancelled => 'pill-muted', default => 'pill-warn' } }}">{{ $booking->status->label() }}</span>
                        @if(in_array($booking->status, [BookingStatus::Scheduled, BookingStatus::PaymentFailed], true))
                            <form method="POST" action="{{ route('pro.bookings.complete', $booking) }}">@csrf<button class="btn btn-primary" type="submit">Mark complete</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted">No bookings yet.</p>
            @endforelse
        </section>

        <section class="stack">
            <h2>Quotes you've sent</h2>
            @forelse($sentQuotes as $quote)
                <div class="row between card">
                    <span>{{ $quote->quoteRequest->jobName() }} · {{ $quote->quoteRequest->city->label() }}</span>
                    <span class="row"><span class="num">{{ Fees::money($quote->amount_cents) }}</span><span class="pill pill-muted">{{ ucfirst($quote->status->value) }}</span></span>
                </div>
            @empty
                <p class="muted">You haven't sent any quotes yet.</p>
            @endforelse
        </section>
    </div>
</x-layout>
