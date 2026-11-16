<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name', 'category', 'description', 'starting_price_cents', 'unit', 'swatch_label', 'swatch_color', 'size_options', 'sort_order'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    public const CATEGORIES = ['Interior', 'Exterior', 'Specialty & commercial'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_options' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsToMany<Provider, $this> */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class)->withPivot('book_now_price_cents');
    }

    /**
     * Size options as label + multiplier pairs.
     *
     * @return list<array{label: string, multiplier: float}>
     */
    public function sizes(): array
    {
        return $this->size_options;
    }

    /**
     * Typical price range for a size, rounded to $10.
     *
     * @return array{low: int, high: int}
     */
    public function estimateRange(float $multiplier, bool $customerSuppliesPaint = false): array
    {
        $mid = $this->starting_price_cents * $multiplier * ($customerSuppliesPaint ? 0.85 : 1.0);

        return [
            'low' => (int) (round($mid * 0.85 / 1000) * 1000),
            'high' => (int) (round($mid * 1.3 / 1000) * 1000),
        ];
    }
}
