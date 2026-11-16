<?php

namespace Database\Factories;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequestMedia>
 */
class QuoteRequestMediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote_request_id' => QuoteRequest::factory(),
            'path' => 'quote-media/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(50_000, 3_000_000),
            'original_name' => 'photo.jpg',
        ];
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'path' => 'quote-media/'.fake()->uuid().'.mp4',
            'mime_type' => 'video/mp4',
            'original_name' => 'walkthrough.mp4',
        ]);
    }
}
