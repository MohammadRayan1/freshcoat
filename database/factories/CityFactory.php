<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'state_code' => 'WA',
            'state_name' => 'Washington',
            'latitude' => 47.6062,
            'longitude' => -122.3321,
            'is_popular' => false,
        ];
    }
}
