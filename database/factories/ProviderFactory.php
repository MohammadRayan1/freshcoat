<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->lastName().' Painting';

        return [
            'city_id' => City::factory(),
            'business_name' => $name,
            'slug' => str($name)->slug()->append('-'.fake()->unique()->numberBetween(1, 99999))->toString(),
            'phone' => fake()->numerify('206-555-####'),
            'email' => fake()->unique()->safeEmail(),
            'latitude' => 47.6062,
            'longitude' => -122.3321,
            'service_radius_miles' => 20,
            'years_in_business' => fake()->numberBetween(1, 25),
        ];
    }

    public function claimed(?User $user = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user?->id ?? User::factory()->provider(),
            'claimed_at' => now(),
            'pro_trial_ends_at' => now()->addMonths(6),
        ]);
    }

    public function payoutsReady(): static
    {
        return $this->state(fn () => [
            'stripe_account_id' => 'acct_test_'.fake()->unique()->bothify('########'),
            'stripe_charges_enabled' => true,
        ]);
    }
}
