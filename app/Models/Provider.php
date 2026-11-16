<?php

namespace App\Models;

use App\Support\Geo;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'user_id', 'city_id', 'business_name', 'slug', 'phone', 'email', 'website', 'description',
    'latitude', 'longitude', 'service_radius_miles', 'years_in_business', 'rating', 'reviews_count',
    'contractor_registration_number', 'insurance_verified_at', 'claimed_at', 'pro_trial_ends_at',
    'stripe_account_id', 'stripe_charges_enabled', 'allows_direct_pay',
])]
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'rating' => 'float',
            'insurance_verified_at' => 'datetime',
            'claimed_at' => 'datetime',
            'pro_trial_ends_at' => 'datetime',
            'stripe_charges_enabled' => 'boolean',
            'allows_direct_pay' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withPivot('book_now_price_cents');
    }

    /** @return BelongsToMany<QuoteRequest, $this> */
    public function quoteRequests(): BelongsToMany
    {
        return $this->belongsToMany(QuoteRequest::class)->withPivot(['distance_miles', 'notified_at']);
    }

    /** @return HasMany<Quote, $this> */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    public function canAcceptCardPayments(): bool
    {
        return $this->stripe_account_id !== null && $this->stripe_charges_enabled;
    }

    /**
     * @param  Builder<Provider>  $query
     */
    public function scopeClaimed(Builder $query): void
    {
        $query->whereNotNull('claimed_at');
    }

    /**
     * Providers offering a service whose service radius covers a point, nearest first.
     *
     * @return Collection<int, array{provider: Provider, distance: float}>
     */
    public static function nearestFor(?Service $service, float $lat, float $lng, int $limit = 10, int $searchRadiusMiles = 50): Collection
    {
        $box = Geo::boundingBox($lat, $lng, $searchRadiusMiles);

        return static::query()
            ->with('city')
            ->when($service, fn (Builder $query) => $query->whereHas('services', fn (Builder $q) => $q->whereKey($service->id)))
            ->whereBetween('latitude', [$box['minLat'], $box['maxLat']])
            ->whereBetween('longitude', [$box['minLng'], $box['maxLng']])
            ->get()
            ->map(fn (Provider $provider) => [
                'provider' => $provider,
                'distance' => round(Geo::distanceMiles($lat, $lng, $provider->latitude, $provider->longitude), 1),
            ])
            ->filter(fn (array $row) => $row['distance'] <= $row['provider']->service_radius_miles)
            ->sortBy('distance')
            ->take($limit)
            ->values();
    }
}
