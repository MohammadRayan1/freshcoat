<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Support\Fees;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['quote_request_id', 'provider_id', 'amount_cents', 'earliest_start_date', 'message', 'direct_pay', 'status'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'earliest_start_date' => 'date',
            'direct_pay' => 'boolean',
            'status' => QuoteStatus::class,
        ];
    }

    /** @return BelongsTo<QuoteRequest, $this> */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** @return HasOne<Booking, $this> */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function serviceFeeCents(): int
    {
        return Fees::serviceFee($this->amount_cents);
    }
}
