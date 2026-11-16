<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Provider;
use App\Models\Service;
use Database\Seeders\CitySeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ServiceSeeder::class, CitySeeder::class]);
    }

    public function test_marketing_pages_render(): void
    {
        foreach (['home', 'how-it-works', 'pricing', 'for-painters', 'services.index', 'locations.index', 'terms', 'claim.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_pricing_page_shows_fee_cap(): void
    {
        $this->get(route('pricing'))->assertOk()->assertSee('$6.95')->assertSee('2.9% + 30¢');
    }

    public function test_service_state_and_city_pages_render(): void
    {
        $service = Service::where('slug', 'cabinet-painting')->firstOrFail();

        $this->get(route('services.show', $service))->assertOk()->assertSee('Kitchen cabinets');
        $this->get(route('locations.index'))->assertOk()->assertSee('Seattle')->assertDontSee('Oregon');
        $this->get(route('locations.city', ['wa', 'seattle']))->assertOk()->assertSee('Painters in Seattle, WA');
        $this->get(route('locations.city', ['wa', 'seattle', $service]))->assertOk()->assertSee('Kitchen cabinets in Seattle, WA');
    }

    public function test_unknown_state_and_city_return_404(): void
    {
        $this->get('/locations/zz')->assertNotFound();
        $this->get('/painters/wa/not-a-city')->assertNotFound();
    }

    public function test_city_page_lists_nearby_painters_offering_the_service_closest_first(): void
    {
        $seattle = City::where('slug', 'seattle')->firstOrFail();
        $auburn = City::where('slug', 'auburn')->firstOrFail();
        $service = Service::where('slug', 'interior-room-painting')->firstOrFail();

        $near = Provider::factory()->create(['business_name' => 'Near Painting', 'city_id' => $seattle->id, 'latitude' => 47.61, 'longitude' => -122.33]);
        $far = Provider::factory()->create(['business_name' => 'Far Painting', 'city_id' => $auburn->id, 'latitude' => 47.3073, 'longitude' => -122.2285, 'service_radius_miles' => 40]);
        $outOfRange = Provider::factory()->create(['business_name' => 'Tiny Radius Painting', 'city_id' => $auburn->id, 'latitude' => 47.3073, 'longitude' => -122.2285, 'service_radius_miles' => 5]);
        $otherService = Provider::factory()->create(['business_name' => 'Deck Only Painting', 'city_id' => $seattle->id]);

        foreach ([$near, $far, $outOfRange] as $provider) {
            $provider->services()->attach($service);
        }
        $otherService->services()->attach(Service::where('slug', 'deck-staining')->first());

        $this->get(route('locations.city', ['wa', 'seattle', $service]))
            ->assertOk()
            ->assertSeeInOrder(['Near Painting', 'Far Painting'])
            ->assertDontSee('Tiny Radius Painting')
            ->assertDontSee('Deck Only Painting');
    }

    public function test_only_washington_is_open(): void
    {
        City::factory()->create(['name' => 'Portland', 'slug' => 'portland', 'state_code' => 'OR', 'state_name' => 'Oregon']);

        $this->get('/locations/wa')->assertNotFound();
        $this->get('/locations/or')->assertNotFound();
        $this->get('/painters/or/portland')->assertNotFound();
        $this->get(route('home'))->assertOk()->assertDontSee('Portland')->assertSee('Yarrow Point');
    }

    public function test_home_lists_every_king_county_city_and_the_claim_button(): void
    {
        $this->assertSame(39, City::where('county', 'King')->count());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Own a painting business?')
            ->assertSee('Claim my business')
            ->assertSee('Skykomish');
    }
}
