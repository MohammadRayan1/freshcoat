<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Pro;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\QuoteRequestMediaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/for-painters', [PageController::class, 'forPainters'])->name('for-painters');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
Route::get('/painters/{state}/{city}/{service?}', [LocationController::class, 'city'])->name('locations.city')->where('state', '[a-z]{2}');

Route::get('/painter/{provider}', [ProviderProfileController::class, 'show'])->name('providers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/quote-requests', [QuoteRequestController::class, 'store'])->name('quote-requests.store');

Route::middleware('auth')->group(function () {
    Route::get('/my/requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
    Route::get('/my/requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
    Route::get('/requests/{quoteRequest}/media/{media}', [QuoteRequestMediaController::class, 'show'])->name('quote-requests.media');
    Route::post('/quotes/{quote}/accept', [BookingController::class, 'accept'])->name('quotes.accept');
    Route::get('/my/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/my/bookings/{booking}/card', [BookingController::class, 'confirmCard'])->name('bookings.card');
    Route::post('/my/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/claim', [ClaimController::class, 'index'])->name('claim.index')->withoutMiddleware('auth');
    Route::post('/claim/{provider}/send-code', [ClaimController::class, 'sendCode'])->name('claim.send')->middleware('throttle:5,10');
    Route::get('/claim/{provider}/verify', [ClaimController::class, 'showVerify'])->name('claim.verify');
    Route::post('/claim/{provider}/verify', [ClaimController::class, 'verify'])->middleware('throttle:10,1');
    Route::get('/add-business', [ClaimController::class, 'create'])->name('claim.create');
    Route::post('/add-business', [ClaimController::class, 'store'])->name('claim.store');

    Route::middleware('provider')->prefix('pro')->name('pro.')->group(function () {
        Route::get('/', [Pro\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/requests/{quoteRequest}', [Pro\RequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{quoteRequest}/quote', [Pro\RequestController::class, 'quote'])->name('requests.quote');
        Route::post('/bookings/{booking}/complete', [Pro\BookingController::class, 'complete'])->name('bookings.complete');
        Route::get('/settings', [Pro\SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings', [Pro\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/payouts/connect', [Pro\PayoutController::class, 'connect'])->name('payouts.connect');
        Route::get('/payouts/return', [Pro\PayoutController::class, 'return'])->name('payouts.return');
    });
});

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
