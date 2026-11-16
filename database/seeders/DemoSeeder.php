<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample listings for local development only. Every business here is fictional and labelled "(Sample)".
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::updateOrCreate(['email' => 'homeowner@example.test'], [
            'name' => 'Sample Homeowner', 'password' => 'password', 'role' => UserRole::Customer,
        ]);
        $painter = User::updateOrCreate(['email' => 'painter@example.test'], [
            'name' => 'Sample Painter', 'password' => 'password', 'role' => UserRole::Provider,
        ]);

        $names = ['Brush & Roller Co.', 'Cut-In Painting', 'Two Coats', 'Fresh Finish Painters', 'Eggshell & Oak', 'Primer Line Painting', 'True Edge Coatings', 'Harbor House Painters', 'Drop Cloth Crew', 'Satin Street Painting', 'Northlight Finishes', 'Ridgeback Painting'];
        $areaCities = City::query()->where('state_code', 'WA')->whereIn('name', ['Seattle', 'Bellevue', 'Kirkland', 'Shoreline', 'Renton', 'Redmond', 'Kent', 'Sammamish'])->orderByRaw("name = 'Seattle' desc")->orderBy('name')->get()->values();
        $services = Service::all();

        foreach ($names as $i => $name) {
            $city = $areaCities[$i % $areaCities->count()];
            $business = "{$name} (Sample)";

            $provider = Provider::updateOrCreate(['slug' => Str::slug("{$name} {$city->name} sample")], [
                'business_name' => $business,
                'city_id' => $city->id,
                'phone' => '206-555-01'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => 'listing'.$i.'@example.test',
                'latitude' => $city->latitude + (($i % 3) - 1) * 0.02,
                'longitude' => $city->longitude + (($i % 4) - 1.5) * 0.02,
                'service_radius_miles' => 15 + ($i % 3) * 5,
                'years_in_business' => 2 + ($i * 3) % 20,
                'rating' => round(4.3 + ($i % 7) / 10, 1),
                'reviews_count' => 8 + ($i * 17) % 190,
                'description' => 'Sample listing for testing. Not a real business.',
            ]);

            $provider->services()->sync($services->random(min(8, $services->count()))->pluck('id'));
        }

        $claimed = Provider::where('business_name', 'Brush & Roller Co. (Sample)')->first();
        $claimed?->forceFill([
            'user_id' => $painter->id,
            'claimed_at' => now(),
            'pro_trial_ends_at' => now()->addMonths(6),
            'insurance_verified_at' => now(),
        ])->save();
        $claimed?->services()->sync($services->pluck('id'));

    }
}
