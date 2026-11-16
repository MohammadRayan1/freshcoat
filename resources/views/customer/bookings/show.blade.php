@php use App\Support\Fees; use App\Enums\BookingStatus; use App\Enums\PaymentMode; @endphp
<x-layout title="Booking">
    @if($clientSecret && $paymentsLive)
        @push('head')<script src="https://js.stripe.com/v3/"></script>@endpush
    @endif
    <div class="wrap stack narrow" style="max-width: 640px">
        <a class="muted small" href="{{ route('quote-requests.show', $booking->quote->quote_request_id) }}" style="text-decoration: none">← Back to quotes</a>
        <div class="row between">
            <h1 style="font-size: 2.2rem">{{ $booking->quote->quoteRequest->jobName() }}</h1>
            <span class="pill {{ match($booking->status) { BookingStatus::Completed, BookingStatus::Scheduled => 'pill-ok', BookingStatus::Cancelled => 'pill-muted', BookingStatus::PaymentFailed => 'pill-bad', default => 'pill-warn' } }}">{{ $booking->status->label() }}</span>
        </div>
        <p class="muted">with <a href="{{ route('providers.show', $booking->provider) }}">{{ $booking->provider->business_name }}</a> · {{ $booking->provider->city->label() }}</p>

        <table class="ledger card">
            <tr><td>Job price</td><td class="amt">{{ Fees::money($booking->amount_cents, true) }}</td></tr>
            <tr><td>Service fee (5%, max $6.95)</td><td class="amt">{{ Fees::money($booking->service_fee_cents, true) }}</td></tr>
            <tr class="total"><td>{{ $booking->status === BookingStatus::Completed ? 'Charged' : 'You\'ll pay after the job' }}</td><td class="amt">{{ Fees::money($booking->totalCents(), true) }}</td></tr>
        </table>

        @if($booking->payment_mode === PaymentMode::Direct)
            <div class="notice"><strong>You'll pay the painter directly</strong><span class="muted">Pay {{ $booking->provider->business_name }} {{ Fees::money($booking->amount_cents, true) }} by check or cash when the job is done. No card is needed on FreshCoat.</span></div>
        @elseif($clientSecret)
            <form class="card stack-sm" method="POST" action="{{ route('bookings.card', $booking) }}" id="card-form">
                @csrf
                <h3>Save your card</h3>
                <p class="muted small">Your card is saved now and charged only after the painter marks the job complete.</p>
                @if($paymentsLive)
                    <div id="payment-element"></div>
                    <p class="error" id="card-error" role="alert" hidden></p>
                    <button class="btn btn-primary" type="submit" id="card-submit">Save card and book</button>
                    <script>
                        (() => {
                            const stripe = Stripe(@json($stripeKey));
                            const elements = stripe.elements({ clientSecret: @json($clientSecret) });
                            elements.create('payment').mount('#payment-element');
                            const form = document.getElementById('card-form');
                            const error = document.getElementById('card-error');
                            form.addEventListener('submit', async (event) => {
                                event.preventDefault();
                                document.getElementById('card-submit').disabled = true;
                                const { error: stripeError } = await stripe.confirmSetup({ elements, redirect: 'if_required' });
                                if (stripeError) {
                                    error.textContent = stripeError.message;
                                    error.hidden = false;
                                    document.getElementById('card-submit').disabled = false;
                                    return;
                                }
                                form.submit();
                            });
                        })();
                    </script>
                @else
                    <p class="demo-note">Test mode: Stripe keys aren't set, so no card form is shown. Saving uses a test card.</p>
                    <button class="btn btn-primary" type="submit">Save test card and book</button>
                @endif
                @error('card')<p class="error" role="alert">{{ $message }}</p>@enderror
            </form>
        @endif

        @if($booking->status === BookingStatus::Scheduled)
            <div class="notice"><strong>You're booked</strong><span class="muted">{{ $booking->provider->business_name }} will reach out to confirm the start date. You'll be charged after they mark the job complete.</span></div>
        @endif

        @if(in_array($booking->status, [BookingStatus::AwaitingCard, BookingStatus::Scheduled], true))
            <form method="POST" action="{{ route('bookings.cancel', $booking) }}">@csrf<button class="btn-link" type="submit">Cancel booking</button></form>
            @error('booking')<p class="error">{{ $message }}</p>@enderror
        @endif
    </div>
</x-layout>
