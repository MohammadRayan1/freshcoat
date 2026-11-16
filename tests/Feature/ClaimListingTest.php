<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ClaimVerification;
use App\Models\Provider;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimListingTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{phone: string, message: string}> */
    private array $texts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(SmsSender::class, new class($this->texts) implements SmsSender
        {
            /** @param list<array{phone: string, message: string}> $texts */
            public function __construct(private array &$texts) {}

            public function send(string $phone, string $message): void
            {
                $this->texts[] = ['phone' => $phone, 'message' => $message];
            }
        });
    }

    public function test_painter_claims_a_listing_with_the_texted_code(): void
    {
        $provider = Provider::factory()->create(['business_name' => 'Fremont Finish Co.', 'phone' => '206-555-0142']);
        $user = User::factory()->create();

        $this->get(route('claim.index', ['name' => 'Fremont']))->assertOk()->assertSee('Fremont Finish Co.');

        $this->actingAs($user)->post(route('claim.send', $provider))->assertRedirect(route('claim.verify', $provider));

        $this->assertCount(1, $this->texts);
        $this->assertSame('206-555-0142', $this->texts[0]['phone']);
        preg_match('/\b(\d{6})\b/', $this->texts[0]['message'], $match);

        $this->actingAs($user)->post(route('claim.verify', $provider), ['code' => $match[1]])->assertRedirect(route('pro.settings'));

        $provider->refresh();
        $this->assertSame($user->id, $provider->user_id);
        $this->assertNotNull($provider->claimed_at);
        $this->assertTrue($provider->pro_trial_ends_at->between(now()->addMonths(6)->subDay(), now()->addMonths(6)->addDay()));
        $this->assertSame(UserRole::Provider, $user->fresh()->role);
    }

    public function test_wrong_code_counts_attempts_and_locks_after_five(): void
    {
        $verification = ClaimVerification::factory()->create();
        $this->actingAs($verification->user);

        foreach (range(1, 5) as $i) {
            $this->post(route('claim.verify', $verification->provider), ['code' => '000000'])->assertSessionHasErrors('code');
        }

        $this->post(route('claim.verify', $verification->provider), ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertNull($verification->provider->fresh()->claimed_at);
    }

    public function test_expired_code_is_rejected(): void
    {
        $verification = ClaimVerification::factory()->create(['expires_at' => now()->subMinute()]);

        $this->actingAs($verification->user)
            ->post(route('claim.verify', $verification->provider), ['code' => '123456'])
            ->assertSessionHasErrors('code');
    }

    public function test_claimed_listing_cannot_be_claimed_again(): void
    {
        $provider = Provider::factory()->claimed()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('claim.send', $provider))
            ->assertSessionHasErrors('claim');
        $this->assertCount(0, $this->texts);
    }

    public function test_dashboard_requires_a_business(): void
    {
        $this->actingAs(User::factory()->create())->get(route('pro.dashboard'))->assertRedirect(route('claim.index'));
    }
}
