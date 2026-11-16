<?php

namespace App\Actions;

use App\Models\Provider;
use App\Models\QuoteRequest;
use App\Notifications\NewQuoteRequest;

/**
 * Sends a new quote request to the closest claimed painters who cover that area.
 */
class DispatchQuoteRequest
{
    public const MAX_PAINTERS = 8;

    /**
     * @return int Number of painters the request was sent to.
     */
    public function handle(QuoteRequest $quoteRequest): int
    {
        $city = $quoteRequest->city;

        $matches = Provider::nearestFor($quoteRequest->service, $city->latitude, $city->longitude, limit: 50)
            ->filter(fn (array $row) => $row['provider']->isClaimed() && $row['provider']->user_id !== null)
            ->take(self::MAX_PAINTERS);

        foreach ($matches as $row) {
            $quoteRequest->invitedProviders()->syncWithoutDetaching([
                $row['provider']->id => ['distance_miles' => $row['distance'], 'notified_at' => now()],
            ]);

            $row['provider']->user->notify(new NewQuoteRequest($quoteRequest, $row['distance']));
        }

        return $matches->count();
    }
}
