<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Provider;
use App\Models\QuoteRequest;
use App\Models\Service;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('locations.index', [
            'cities' => City::query()->launched()->orderBy('name')->get(),
        ]);
    }

    public function city(string $state, string $city, ?Service $service = null): View
    {
        $cityModel = City::query()
            ->launched()
            ->where('state_code', strtoupper($state))
            ->where('slug', $city)
            ->firstOrFail();

        $services = Service::query()->orderBy('sort_order')->get();
        $selected = $service ?? $services->first();

        $painters = Provider::nearestFor($selected, $cityModel->latitude, $cityModel->longitude, limit: 24);

        return view('locations.city', [
            'city' => $cityModel,
            'service' => $service,
            'selectedService' => $selected,
            'services' => $services,
            'cities' => City::query()->launched()->orderBy('name')->get(),
            'painters' => $painters,
            'timings' => QuoteRequest::TIMINGS,
        ]);
    }
}
