<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\QuoteRequest;
use App\Models\Service;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'services' => Service::query()->orderBy('sort_order')->get(),
            'cities' => City::query()->launched()->orderBy('name')->get(),
            'timings' => QuoteRequest::TIMINGS,
        ]);
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    public function pricing(): View
    {
        return view('pages.pricing');
    }

    public function forPainters(): View
    {
        return view('pages.for-painters');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
