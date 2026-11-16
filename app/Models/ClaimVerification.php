<?php

namespace App\Models;

use Database\Factories\ClaimVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['provider_id', 'user_id', 'channel', 'destination', 'code_hash', 'attempts', 'expires_at', 'verified_at'])]
#[Hidden(['code_hash'])]
class ClaimVerification extends Model
{
    /** @use HasFactory<ClaimVerificationFactory> */
    use HasFactory;

    public const MAX_ATTEMPTS = 5;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
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

    public function isUsable(): bool
    {
        return $this->verified_at === null && $this->expires_at->isFuture() && $this->attempts < self::MAX_ATTEMPTS;
    }

    /**
     * Shows only the last few characters of the phone or email the code went to.
     */
    public function maskedDestination(): string
    {
        if ($this->channel === 'email') {
            [$name, $domain] = array_pad(explode('@', $this->destination, 2), 2, '');

            return substr($name, 0, 1).'•••@'.$domain;
        }

        return '•••-•••-'.substr(preg_replace('/\D/', '', $this->destination), -4);
    }
}
