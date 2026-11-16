<?php

namespace App\Payments;

use App\Models\Booking;
use App\Models\Provider;
use Illuminate\Support\Str;

/**
 * Stand-in used when no Stripe keys are configured, so the full flow can be tried locally.
 */
class FakeGateway implements PaymentGateway
{
    public bool $failNextCharge = false;

    public function isLive(): bool
    {
        return false;
    }

    public function onboardingUrl(Provider $provider, string $returnUrl, string $refreshUrl): string
    {
        if ($provider->stripe_account_id === null) {
            $provider->forceFill(['stripe_account_id' => 'acct_fake_'.Str::lower(Str::random(12))])->save();
        }

        return $returnUrl;
    }

    public function syncAccountStatus(Provider $provider): bool
    {
        $enabled = $provider->stripe_account_id !== null;
        $provider->forceFill(['stripe_charges_enabled' => $enabled])->save();

        return $enabled;
    }

    public function startCardSetup(Booking $booking): array
    {
        return [
            'customer_id' => $booking->stripe_customer_id ?? 'cus_fake_'.Str::lower(Str::random(12)),
            'setup_intent_id' => 'seti_fake_'.Str::lower(Str::random(12)),
            'client_secret' => 'fake_secret',
        ];
    }

    public function confirmCardSetup(Booking $booking): string
    {
        return 'pm_fake_'.Str::lower(Str::random(12));
    }

    public function chargeBooking(Booking $booking): string
    {
        if ($this->failNextCharge) {
            $this->failNextCharge = false;

            throw new PaymentFailedException('The card was declined.');
        }

        return 'pi_fake_'.Str::lower(Str::random(12));
    }
}
