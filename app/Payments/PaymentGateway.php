<?php

namespace App\Payments;

use App\Models\Booking;
use App\Models\Provider;

/**
 * Money movement for the marketplace.
 *
 * Painters use Stripe accounts where Stripe handles pricing ("Standard"-style), so FreshCoat pays no
 * Connect account or payout fees. FreshCoat earns its service fee as an application fee on each charge.
 */
interface PaymentGateway
{
    /**
     * Whether this gateway talks to real Stripe.
     */
    public function isLive(): bool;

    /**
     * Create the painter's connected account if needed and return a Stripe onboarding URL.
     */
    public function onboardingUrl(Provider $provider, string $returnUrl, string $refreshUrl): string;

    /**
     * Re-read the connected account and store whether it can take charges.
     */
    public function syncAccountStatus(Provider $provider): bool;

    /**
     * Start saving the customer's card for a booking without charging it.
     *
     * @return array{customer_id: string, setup_intent_id: string, client_secret: string}
     */
    public function startCardSetup(Booking $booking): array;

    /**
     * Confirm the card was saved and return its payment method id.
     *
     * @throws PaymentFailedException
     */
    public function confirmCardSetup(Booking $booking): string;

    /**
     * Charge the saved card on the painter's account, taking the service fee as FreshCoat's application fee.
     *
     * @throws PaymentFailedException
     */
    public function chargeBooking(Booking $booking): string;
}
