<?php

namespace App\Http\Controllers\Pro;

use App\Enums\BookingStatus;
use App\Enums\QuoteRequestStatus;
use App\Http\Controllers\Controller;
use App\Support\Fees;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $provider = $request->user()->provider;

        $openRequests = $provider->quoteRequests()
            ->with(['service', 'city'])
            ->where('status', QuoteRequestStatus::Open)
            ->whereDoesntHave('quotes', fn ($q) => $q->where('provider_id', $provider->id))
            ->latest('quote_requests.created_at')
            ->get();

        $sentQuotes = $provider->quotes()->with(['quoteRequest.service', 'quoteRequest.city'])->latest()->limit(20)->get();
        $bookings = $provider->bookings()->with(['quote.quoteRequest.service', 'quote.quoteRequest.city', 'user'])->latest()->get();

        $earnedCents = $bookings->where('status', BookingStatus::Completed)
            ->sum(fn ($booking) => Fees::providerPayout($booking->amount_cents));

        return view('pro.dashboard', [
            'provider' => $provider,
            'openRequests' => $openRequests,
            'sentQuotes' => $sentQuotes,
            'bookings' => $bookings,
            'earnedCents' => $earnedCents,
        ]);
    }
}
