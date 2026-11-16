<?php

namespace Database\Seeders;

use App\Models\City;
use App\Support\States;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * All 39 incorporated cities and towns in King County, WA, with approximate city-center coordinates.
     */
    public function run(): void
    {
        $popular = ['Seattle', 'Bellevue', 'Redmond', 'Kirkland', 'Renton', 'Kent', 'Sammamish', 'Federal Way', 'Auburn', 'Issaquah', 'Shoreline', 'Bothell'];

        $kingCounty = [
            ['Algona', 47.2815, -122.2507], ['Auburn', 47.3073, -122.2285], ['Beaux Arts Village', 47.5854, -122.2018],
            ['Bellevue', 47.6101, -122.2015], ['Black Diamond', 47.3087, -122.0032], ['Bothell', 47.7623, -122.2054],
            ['Burien', 47.4704, -122.3468], ['Carnation', 47.6479, -121.9140], ['Clyde Hill', 47.6318, -122.2179],
            ['Covington', 47.3582, -122.1218], ['Des Moines', 47.4018, -122.3243], ['Duvall', 47.7423, -121.9857],
            ['Enumclaw', 47.2043, -121.9915], ['Federal Way', 47.3223, -122.3126], ['Hunts Point', 47.6432, -122.2307],
            ['Issaquah', 47.5301, -122.0326], ['Kenmore', 47.7573, -122.2440], ['Kent', 47.3809, -122.2348],
            ['Kirkland', 47.6815, -122.2087], ['Lake Forest Park', 47.7568, -122.2807], ['Maple Valley', 47.3926, -122.0465],
            ['Medina', 47.6209, -122.2276], ['Mercer Island', 47.5707, -122.2221], ['Milton', 47.2481, -122.3129],
            ['Newcastle', 47.5390, -122.1557], ['Normandy Park', 47.4362, -122.3407], ['North Bend', 47.4957, -121.7868],
            ['Pacific', 47.2645, -122.2501], ['Redmond', 47.6740, -122.1215], ['Renton', 47.4829, -122.2171],
            ['Sammamish', 47.6163, -122.0356], ['SeaTac', 47.4436, -122.2961], ['Seattle', 47.6062, -122.3321],
            ['Shoreline', 47.7557, -122.3415], ['Skykomish', 47.7093, -121.3590], ['Snoqualmie', 47.5287, -121.8254],
            ['Tukwila', 47.4740, -122.2610], ['Woodinville', 47.7543, -122.1635], ['Yarrow Point', 47.6462, -122.2173],
        ];

        foreach ($kingCounty as [$name, $lat, $lng]) {
            City::updateOrCreate(
                ['state_code' => 'WA', 'slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'state_name' => States::ALL['WA'],
                    'county' => 'King',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_popular' => in_array($name, $popular, true),
                ],
            );
        }
    }
}
