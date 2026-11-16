<?php

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use Database\Factories\QuoteRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'service_id', 'custom_title', 'city_id', 'size_label', 'size_multiplier', 'timing',
    'customer_supplies_paint', 'zip', 'address', 'details', 'status',
])]
class QuoteRequest extends Model
{
    /** @use HasFactory<QuoteRequestFactory> */
    use HasFactory;

    public const TIMINGS = [
        'asap' => 'As soon as possible',
        'two_weeks' => 'Within 2 weeks',
        'month' => 'This month',
        'flexible' => "I'm flexible",
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_multiplier' => 'float',
            'customer_supplies_paint' => 'boolean',
            'status' => QuoteRequestStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsToMany<Provider, $this> */
    public function invitedProviders(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class)->withPivot(['distance_miles', 'notified_at']);
    }

    /** @return HasMany<QuoteRequestMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(QuoteRequestMedia::class);
    }

    /** @return HasMany<Quote, $this> */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function timingLabel(): string
    {
        return self::TIMINGS[$this->timing] ?? $this->timing;
    }

    public const CUSTOM = 'custom';

    public function isCustom(): bool
    {
        return $this->service_id === null;
    }

    /**
     * What the homeowner wants painted: the preset service, or their own description.
     */
    public function jobName(): string
    {
        return $this->service?->name ?? $this->custom_title ?? 'Custom job';
    }

    /**
     * Typical price range, or null for custom jobs that have no preset pricing.
     *
     * @return array{low: int, high: int}|null
     */
    public function estimateRange(): ?array
    {
        return $this->service?->estimateRange($this->size_multiplier, $this->customer_supplies_paint);
    }
}
