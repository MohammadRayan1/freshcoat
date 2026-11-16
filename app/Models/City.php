<?php

namespace App\Models;

use App\Support\States;
use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'state_code', 'state_name', 'county', 'latitude', 'longitude', 'is_popular'])]
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_popular' => 'boolean',
        ];
    }

    /**
     * Cities in states FreshCoat currently serves.
     *
     * @param  Builder<City>  $query
     */
    public function scopeLaunched(Builder $query): void
    {
        $query->whereIn('state_code', array_keys(States::launched()));
    }

    /** @return HasMany<Provider, $this> */
    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function label(): string
    {
        return "{$this->name}, {$this->state_code}";
    }

    public function stateSlug(): string
    {
        return strtolower($this->state_code);
    }
}
