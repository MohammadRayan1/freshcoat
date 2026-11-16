<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quote_id', 'provider_id', 'user_id', 'amount_cents', 'service_fee_cents', 'payment_mode', 'status',
    'stripe_customer_id', 'stripe_setup_intent_id', 'stripe_payment_method_id', 'stripe_payment_intent_id',
    'completed_at', 'charged_at', 'cancelled_at',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_mode' => PaymentMode::class,
            'status' => BookingStatus::class,
            'completed_at' => 'datetime',
            'charged_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Quote, $this> */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalCents(): int
    {
        return $this->amount_cents + $this->service_fee_cents;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [BookingStatus::AwaitingCard, BookingStatus::Scheduled, BookingStatus::PaymentFailed], true);
    }
}
