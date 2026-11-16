<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(private PaymentGateway $payments) {}

    public function connect(Request $request): RedirectResponse
    {
        $url = $this->payments->onboardingUrl(
            $request->user()->provider,
            returnUrl: route('pro.payouts.return'),
            refreshUrl: route('pro.payouts.connect'),
        );

        return redirect()->away($url);
    }

    public function return(Request $request): RedirectResponse
    {
        $enabled = $this->payments->syncAccountStatus($request->user()->provider);

        return redirect()->route('pro.settings')->with('status', $enabled
            ? 'Payouts connected. You can now take card payments.'
            : 'Stripe still needs a few details before you can take card payments. Use "Continue payout setup" to finish.');
    }
}
