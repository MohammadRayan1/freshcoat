<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use App\Enums\QuoteRequestStatus;
use App\Enums\QuoteStatus;
use App\Models\Booking;
use App\Models\Quote;
use App\Notifications\BookingUpdate;
use App\Payments\PaymentFailedException;
use App\Payments\PaymentGateway;
use App\Support\Fees;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Booking lifecycle: accept a quote, save a card, complete and charge, or cancel.
 *
 * Cards are saved at booking and charged on completion (no card hold), because holds expire
 * after about 7 days and painting jobs are often scheduled further out.
 */
class ManageBooking
{
    public function __construct(private PaymentGateway $payments) {}

    public function accept(Quote $quote): Booking
    {
        return DB::transaction(function () use ($quote) {
            $quoteRequest = $quote->quoteRequest()->lockForUpdate()->firstOrFail();

            if ($quoteRequest->status !== QuoteRequestStatus::Open || $quote->status !== QuoteStatus::Pending) {
                throw ValidationException::withMessages(['quote' => 'This request is already booked.']);
            }

            $mode = $quote->direct_pay ? PaymentMode::Direct : PaymentMode::Platform;

            if ($mode === PaymentMode::Platform && ! $quote->provider->canAcceptCardPayments()) {
                throw ValidationException::withMessages(['quote' => 'This painter hasn\'t finished setting up card payments yet. Message them or pick another quote.']);
            }

            $quote->update(['status' => QuoteStatus::Accepted]);
            $quoteRequest->quotes()->whereKeyNot($quote->id)->update(['status' => QuoteStatus::Declined]);
            $quoteRequest->update(['status' => QuoteRequestStatus::Booked]);

            $booking = Booking::create([
                'quote_id' => $quote->id,
                'provider_id' => $quote->provider_id,
                'user_id' => $quoteRequest->user_id,
                'amount_cents' => $quote->amount_cents,
                'service_fee_cents' => Fees::serviceFee($quote->amount_cents),
                'payment_mode' => $mode,
                'status' => $mode === PaymentMode::Direct ? BookingStatus::Scheduled : BookingStatus::AwaitingCard,
            ]);

            if ($mode === PaymentMode::Direct) {
                $quote->provider->user?->notify(new BookingUpdate($booking, 'New booking: the customer will pay you directly.'));
            }

            return $booking;
        });
    }

    /**
     * @return string Client secret for Stripe.js to collect the card.
     */
    public function startCardSetup(Booking $booking): string
    {
        $setup = $this->payments->startCardSetup($booking);

        $booking->update([
            'stripe_customer_id' => $setup['customer_id'],
            'stripe_setup_intent_id' => $setup['setup_intent_id'],
        ]);

        return $setup['client_secret'];
    }

    public function confirmCard(Booking $booking): void
    {
        if ($booking->status !== BookingStatus::AwaitingCard) {
            return;
        }

        try {
            $paymentMethod = $this->payments->confirmCardSetup($booking);
        } catch (PaymentFailedException $e) {
            throw ValidationException::withMessages(['card' => $e->getMessage()]);
        }

        $booking->update([
            'stripe_payment_method_id' => $paymentMethod,
            'status' => BookingStatus::Scheduled,
        ]);

        $booking->provider->user?->notify(new BookingUpdate($booking, 'New booking confirmed. The customer\'s card is on file.'));
    }

    public function complete(Booking $booking): void
    {
        if (! in_array($booking->status, [BookingStatus::Scheduled, BookingStatus::PaymentFailed], true)) {
            throw ValidationException::withMessages(['booking' => 'Only scheduled jobs can be marked complete.']);
        }

        if ($booking->payment_mode === PaymentMode::Direct) {
            $booking->update(['status' => BookingStatus::Completed, 'completed_at' => now()]);

            return;
        }

        try {
            $paymentIntent = $this->payments->chargeBooking($booking);
        } catch (PaymentFailedException $e) {
            $booking->update(['status' => BookingStatus::PaymentFailed]);
            $booking->user->notify(new BookingUpdate($booking, 'We couldn\'t charge your card for a completed painting job. Update your card to finish the booking.'));

            throw ValidationException::withMessages(['booking' => "The charge failed: {$e->getMessage()} We've asked the customer to update their card."]);
        }

        $booking->update([
            'status' => BookingStatus::Completed,
            'stripe_payment_intent_id' => $paymentIntent,
            'completed_at' => now(),
            'charged_at' => now(),
        ]);

        $booking->user->notify(new BookingUpdate($booking, 'Your painting job is complete and your card was charged.'));
    }

    public function cancel(Booking $booking): void
    {
        if (! in_array($booking->status, [BookingStatus::AwaitingCard, BookingStatus::Scheduled], true)) {
            throw ValidationException::withMessages(['booking' => 'This booking can no longer be cancelled.']);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::Cancelled, 'cancelled_at' => now()]);
            $booking->quote->quoteRequest->update(['status' => QuoteRequestStatus::Open]);
            $booking->quote->quoteRequest->quotes()->whereKeyNot($booking->quote_id)->update(['status' => QuoteStatus::Pending]);
            $booking->quote->update(['status' => QuoteStatus::Declined]);
        });
    }
}
