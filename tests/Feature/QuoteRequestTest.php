<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Provider;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Notifications\NewQuoteRequest;
use Database\Seeders\CitySeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    private City $seattle;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ServiceSeeder::class, CitySeeder::class]);
        $this->seattle = City::where('slug', 'seattle')->where('state_code', 'WA')->firstOrFail();
        $this->service = Service::where('slug', 'interior-room-painting')->firstOrFail();
        Notification::fake();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'service_id' => $this->service->id,
            'size' => 2,
            'timing' => 'two_weeks',
            'customer_supplies_paint' => '0',
            'city_id' => $this->seattle->id,
            'zip' => '98103',
            'details' => 'Living room with 9 ft ceilings',
            ...$overrides,
        ];
    }

    public function test_request_is_sent_only_to_nearby_claimed_painters_offering_the_service(): void
    {
        $claimed = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id]);
        $unclaimed = Provider::factory()->create(['city_id' => $this->seattle->id]);
        $wrongService = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id]);
        $tooFar = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id, 'latitude' => 45.52, 'longitude' => -122.68]);

        foreach ([$claimed, $unclaimed, $tooFar] as $provider) {
            $provider->services()->attach($this->service);
        }
        $wrongService->services()->attach(Service::where('slug', 'deck-staining')->first());

        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->post(route('quote-requests.store'), $this->payload());

        $quoteRequest = QuoteRequest::firstOrFail();
        $response->assertRedirect(route('quote-requests.show', $quoteRequest));
        $this->assertSame('Large room (200–350 sq ft)', $quoteRequest->size_label);
        $this->assertSame([$claimed->id], $quoteRequest->invitedProviders()->pluck('providers.id')->all());
        Notification::assertSentTo($claimed->user, NewQuoteRequest::class);
        Notification::assertCount(1);
    }

    public function test_custom_job_goes_to_nearby_painters_regardless_of_services(): void
    {
        $deckPainter = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id]);
        $deckPainter->services()->attach(Service::where('slug', 'deck-staining')->first());

        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('quote-requests.store'), $this->payload([
            'service_id' => QuoteRequest::CUSTOM,
            'custom_title' => 'Garage floor epoxy',
            'size' => null,
            'details' => 'Two-car garage, about 400 sq ft',
        ]))->assertRedirect();

        $quoteRequest = QuoteRequest::firstOrFail();
        $this->assertTrue($quoteRequest->isCustom());
        $this->assertSame('Garage floor epoxy', $quoteRequest->jobName());
        $this->assertNull($quoteRequest->estimateRange());
        $this->assertSame([$deckPainter->id], $quoteRequest->invitedProviders()->pluck('providers.id')->all());

        $this->get(route('quote-requests.show', $quoteRequest))->assertOk()->assertSee('Garage floor epoxy in Seattle, WA');
        $this->actingAs($deckPainter->user)->get(route('pro.requests.show', $quoteRequest))->assertOk()->assertSee('Garage floor epoxy')->assertSee('Custom job');
    }

    public function test_custom_job_needs_a_description(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('quote-requests.store'), $this->payload(['service_id' => QuoteRequest::CUSTOM, 'custom_title' => '', 'size' => null]))
            ->assertSessionHasErrors(['custom_title' => 'Tell painters what needs painting.']);

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_photos_and_video_are_attached_and_only_visible_to_the_homeowner_and_invited_painters(): void
    {
        Storage::fake('local');
        $invited = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id]);
        $invited->services()->attach($this->service);
        $outsider = Provider::factory()->claimed()->create(['city_id' => $this->seattle->id]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('quote-requests.store'), $this->payload([
            'media' => [
                UploadedFile::fake()->image('wall.jpg', 800, 600),
                UploadedFile::fake()->create('walkthrough.mp4', 2000, 'video/mp4'),
            ],
        ]))->assertRedirect();

        $quoteRequest = QuoteRequest::firstOrFail();
        $this->assertCount(2, $quoteRequest->media);
        [$photo, $video] = $quoteRequest->media;
        Storage::disk('local')->assertExists($photo->path);
        $this->assertTrue($video->isVideo());

        $url = route('quote-requests.media', [$quoteRequest, $photo]);
        $this->get(route('quote-requests.show', $quoteRequest))->assertSee($url, false);
        $this->get($url)->assertOk();
        $this->actingAs($invited->user)->get($url)->assertOk();
        $this->actingAs($outsider->user)->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    }

    public function test_media_uploads_are_validated(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('quote-requests.store'), $this->payload([
            'media' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasErrors('media.0');

        $this->actingAs($customer)->post(route('quote-requests.store'), $this->payload([
            'media' => array_map(fn (int $i) => UploadedFile::fake()->image("room{$i}.jpg"), range(1, 7)),
        ]))->assertSessionHasErrors('media');

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_guest_uploads_survive_sign_up(): void
    {
        Storage::fake('local');

        $this->post(route('quote-requests.store'), $this->payload([
            'media' => [UploadedFile::fake()->image('ceiling.jpg')],
        ]))->assertRedirect(route('register'));

        $this->post(route('register'), [
            'name' => 'Pat Homeowner',
            'email' => 'pat@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $media = QuoteRequest::firstOrFail()->media()->firstOrFail();
        Storage::disk('local')->assertExists($media->path);
    }

    public function test_guest_request_is_kept_and_sent_after_signing_up(): void
    {
        $this->post(route('quote-requests.store'), $this->payload())->assertRedirect(route('register'));
        $this->assertSame(0, QuoteRequest::count());

        $this->post(route('register'), [
            'name' => 'Pat Homeowner',
            'email' => 'pat@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $quoteRequest = QuoteRequest::firstOrFail();
        $this->assertSame('pat@example.test', $quoteRequest->user->email);
        $this->assertSame('Living room with 9 ft ceilings', $quoteRequest->details);
    }

    public function test_zip_must_be_five_digits(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('quote-requests.store'), $this->payload(['zip' => '981']))
            ->assertSessionHasErrors('zip');
    }

    public function test_customers_cannot_view_other_customers_requests(): void
    {
        $quoteRequest = QuoteRequest::factory()->create(['service_id' => $this->service->id, 'city_id' => $this->seattle->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('quote-requests.show', $quoteRequest))
            ->assertForbidden();
    }

    public function test_requests_outside_washington_are_rejected(): void
    {
        $portland = City::factory()->create(['state_code' => 'OR', 'state_name' => 'Oregon']);

        $this->actingAs(User::factory()->create())
            ->post(route('quote-requests.store'), $this->payload(['city_id' => $portland->id]))
            ->assertSessionHasErrors('city_id');
    }
}
