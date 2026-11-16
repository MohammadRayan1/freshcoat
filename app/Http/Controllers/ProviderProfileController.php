<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function show(Provider $provider): View
    {
        $provider->load(['city', 'services']);

        return view('providers.show', ['provider' => $provider]);
    }
}
