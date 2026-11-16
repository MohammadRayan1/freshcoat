<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequest>
 */
class QuoteRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'city_id' => City::factory(),
            'size_label' => 'Medium',
            'size_multiplier' => 1.0,
            'timing' => 'two_weeks',
            'customer_supplies_paint' => false,
            'zip' => '98103',
        ];
    }
}
