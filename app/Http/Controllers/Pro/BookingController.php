<?php

namespace App\Http\Controllers\Pro;

use App\Actions\ManageBooking;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private ManageBooking $bookings) {}

    public function complete(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->provider_id === $request->user()->provider->id, 403);

        $this->bookings->complete($booking);

        return redirect()->route('pro.dashboard')->with('status', 'Job marked complete. Payment is on its way to your bank account.');
    }
}
