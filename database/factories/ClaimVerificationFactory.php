<?php

namespace Database\Factories;

use App\Models\ClaimVerification;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ClaimVerification>
 */
class ClaimVerificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'user_id' => User::factory(),
            'channel' => 'sms',
            'destination' => '206-555-0100',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
        ];
    }
}
