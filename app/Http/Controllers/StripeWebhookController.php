<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        abort_if(! $secret, 404);

        try {
            $event = Webhook::constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'), $secret);
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'account.updated') {
            $account = $event->data->object;
            Provider::query()
                ->where('stripe_account_id', $account->id)
                ->update(['stripe_charges_enabled' => (bool) $account->charges_enabled]);
        }

        return response('ok');
    }
}
