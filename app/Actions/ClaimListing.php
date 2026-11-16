<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\ClaimVerification;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\ClaimCode;
use App\Support\Fees;
use App\Support\SmsSender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Lets a business prove it controls a listing's phone or email, then hands the listing to that user.
 */
class ClaimListing
{
    public function __construct(private SmsSender $sms) {}

    /**
     * Send a 6-digit code to the listing's phone (preferred) or email.
     *
     * @return array{verification: ClaimVerification, code: string}
     */
    public function start(Provider $provider, User $user): array
    {
        if ($provider->isClaimed()) {
            throw ValidationException::withMessages(['claim' => 'This listing has already been claimed. Contact support if it belongs to you.']);
        }

        if ($provider->phone === null && $provider->email === null) {
            throw ValidationException::withMessages(['claim' => 'This listing has no phone or email to verify. Contact support to claim it.']);
        }

        $code = (string) random_int(100000, 999999);
        $channel = $provider->phone !== null ? 'sms' : 'email';
        $destination = $provider->phone ?? $provider->email;

        ClaimVerification::query()
            ->where('provider_id', $provider->id)
            ->where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        $verification = ClaimVerification::create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $message = "Your FreshCoat code to claim {$provider->business_name} is {$code}. It expires in 15 minutes.";

        if ($channel === 'sms') {
            $this->sms->send($destination, $message);
        } else {
            Notification::route('mail', $destination)->notify(new ClaimCode($provider, $code));
        }

        return ['verification' => $verification, 'code' => $code];
    }

    public function verify(ClaimVerification $verification, string $code): Provider
    {
        if (! $verification->isUsable()) {
            throw ValidationException::withMessages(['code' => 'This code has expired. Send a new one.']);
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages(['code' => 'That code doesn\'t match. Check the text message and try again.']);
        }

        return DB::transaction(function () use ($verification) {
            $provider = Provider::query()->lockForUpdate()->findOrFail($verification->provider_id);

            if ($provider->isClaimed()) {
                throw ValidationException::withMessages(['code' => 'This listing was claimed by someone else.']);
            }

            $verification->forceFill(['verified_at' => now()])->save();

            $provider->forceFill([
                'user_id' => $verification->user_id,
                'claimed_at' => now(),
                'pro_trial_ends_at' => now()->addMonths(Fees::PRO_TRIAL_MONTHS),
            ])->save();

            $verification->user->forceFill(['role' => UserRole::Provider])->save();

            return $provider;
        });
    }
}
