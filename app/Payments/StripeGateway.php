<?php

namespace App\Payments;

use App\Models\Booking;
use App\Models\Provider;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    public function __construct(private StripeClient $stripe) {}

    public function isLive(): bool
    {
        return true;
    }

    public function onboardingUrl(Provider $provider, string $returnUrl, string $refreshUrl): string
    {
        if ($provider->stripe_account_id === null) {
            $account = $this->stripe->accounts->create([
                'country' => 'US',
                'email' => $provider->email ?? $provider->user?->email,
                'business_profile' => [
                    'name' => $provider->business_name,
                    'mcc' => '1799',
                ],
                'controller' => [
                    'fees' => ['payer' => 'account'],
                    'losses' => ['payments' => 'stripe'],
                    'stripe_dashboard' => ['type' => 'full'],
                ],
                'metadata' => ['provider_id' => (string) $provider->id],
            ]);

            $provider->forceFill(['stripe_account_id' => $account->id])->save();
        }

        $link = $this->stripe->accountLinks->create([
            'account' => $provider->stripe_account_id,
            'type' => 'account_onboarding',
            'return_url' => $returnUrl,
            'refresh_url' => $refreshUrl,
        ]);

        return $link->url;
    }

    public function syncAccountStatus(Provider $provider): bool
    {
        if ($provider->stripe_account_id === null) {
            return false;
        }

        $account = $this->stripe->accounts->retrieve($provider->stripe_account_id);
        $provider->forceFill(['stripe_charges_enabled' => (bool) $account->charges_enabled])->save();

        return (bool) $account->charges_enabled;
    }

    public function startCardSetup(Booking $booking): array
    {
        $customerId = $booking->stripe_customer_id;

        if ($customerId === null) {
            $customer = $this->stripe->customers->create([
                'email' => $booking->user->email,
                'name' => $booking->user->name,
                'metadata' => ['user_id' => (string) $booking->user_id],
            ]);
            $customerId = $customer->id;
        }

        $intent = $this->stripe->setupIntents->create([
            'customer' => $customerId,
            'usage' => 'off_session',
            'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            'metadata' => ['booking_id' => (string) $booking->id],
        ]);

        return [
            'customer_id' => $customerId,
            'setup_intent_id' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function confirmCardSetup(Booking $booking): string
    {
        $intent = $this->stripe->setupIntents->retrieve($booking->stripe_setup_intent_id);

        if ($intent->status !== 'succeeded' || ! $intent->payment_method) {
            throw new PaymentFailedException('The card was not saved. Try entering it again.');
        }

        return is_string($intent->payment_method) ? $intent->payment_method : $intent->payment_method->id;
    }

    public function chargeBooking(Booking $booking): string
    {
        $account = $booking->provider->stripe_account_id;

        try {
            $cloned = $this->stripe->paymentMethods->create([
                'customer' => $booking->stripe_customer_id,
                'payment_method' => $booking->stripe_payment_method_id,
            ], ['stripe_account' => $account]);

            $intent = $this->stripe->paymentIntents->create([
                'amount' => $booking->totalCents(),
                'currency' => 'usd',
                'payment_method' => $cloned->id,
                'confirm' => true,
                'off_session' => true,
                'application_fee_amount' => $booking->service_fee_cents,
                'description' => "FreshCoat booking #{$booking->id}",
                'metadata' => ['booking_id' => (string) $booking->id],
            ], [
                'stripe_account' => $account,
                'idempotency_key' => "booking-charge-{$booking->id}",
            ]);
        } catch (CardException $e) {
            throw new PaymentFailedException($e->getMessage() ?: 'The card was declined.', previous: $e);
        } catch (ApiErrorException $e) {
            throw new PaymentFailedException('We could not reach the payment processor. Try again in a few minutes.', previous: $e);
        }

        if ($intent->status !== 'succeeded') {
            throw new PaymentFailedException('The charge needs the customer to confirm their card.');
        }

        return $intent->id;
    }
}
