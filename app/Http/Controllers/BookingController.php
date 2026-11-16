<?php

namespace App\Http\Controllers;

use App\Actions\ManageBooking;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Quote;
use App\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private ManageBooking $bookings, private PaymentGateway $payments) {}

    public function accept(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->quoteRequest->user_id === $request->user()->id, 403);

        $booking = $this->bookings->accept($quote);

        return redirect()->route('bookings.show', $booking);
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load(['provider.city', 'quote.quoteRequest.service']);

        $clientSecret = in_array($booking->status, [BookingStatus::AwaitingCard, BookingStatus::PaymentFailed], true)
            ? $this->bookings->startCardSetup($booking)
            : null;

        if ($booking->status === BookingStatus::PaymentFailed) {
            $booking->update(['status' => BookingStatus::AwaitingCard]);
        }

        return view('customer.bookings.show', [
            'booking' => $booking,
            'clientSecret' => $clientSecret,
            'stripeKey' => config('services.stripe.key'),
            'paymentsLive' => $this->payments->isLive(),
        ]);
    }

    public function confirmCard(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $this->bookings->confirmCard($booking);

        return redirect()->route('bookings.show', $booking)->with('status', 'Card saved. You won\'t be charged until the painter finishes the job.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $this->bookings->cancel($booking);

        return redirect()->route('quote-requests.show', $booking->quote->quote_request_id)->with('status', 'Booking cancelled. Nothing was charged.');
    }
}
