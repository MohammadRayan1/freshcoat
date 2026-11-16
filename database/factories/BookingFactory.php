<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use App\Models\Booking;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
            'provider_id' => fn (array $attributes) => Quote::find($attributes['quote_id'])->provider_id,
            'user_id' => fn (array $attributes) => Quote::find($attributes['quote_id'])->quoteRequest->user_id,
            'amount_cents' => 48000,
            'service_fee_cents' => 695,
            'payment_mode' => PaymentMode::Platform,
            'status' => BookingStatus::Scheduled,
            'stripe_customer_id' => 'cus_test',
            'stripe_payment_method_id' => 'pm_test',
        ];
    }
}
