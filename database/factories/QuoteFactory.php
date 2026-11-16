<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Quote;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote_request_id' => QuoteRequest::factory(),
            'provider_id' => Provider::factory()->claimed()->payoutsReady(),
            'amount_cents' => 48000,
            'earliest_start_date' => now()->addWeek(),
            'direct_pay' => false,
        ];
    }
}
