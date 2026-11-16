<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\QuoteRequest;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'servicesByCategory' => Service::query()->orderBy('sort_order')->withCount('providers')->get()->groupBy('category'),
        ]);
    }

    public function show(Service $service): View
    {
        return view('services.show', [
            'service' => $service,
            'services' => Service::query()->orderBy('sort_order')->get(),
            'cities' => City::query()->launched()->orderBy('name')->get(),
            'timings' => QuoteRequest::TIMINGS,
            'popularCities' => City::query()->launched()->orderBy('name')->get(),
        ]);
    }
}
