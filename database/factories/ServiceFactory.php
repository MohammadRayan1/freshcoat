<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => str($name)->slug()->toString(),
            'name' => ucfirst($name),
            'category' => 'Interior',
            'description' => fake()->sentence(),
            'starting_price_cents' => 45000,
            'unit' => 'room',
            'swatch_label' => 'Sage · eggshell',
            'swatch_color' => '#C9D6C1',
            'size_options' => [
                ['label' => 'Small', 'multiplier' => 0.8],
                ['label' => 'Medium', 'multiplier' => 1.0],
                ['label' => 'Large', 'multiplier' => 1.5],
            ],
            'sort_order' => 0,
        ];
    }
}
