<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use App\Enums\QuoteRequestStatus;
use App\Enums\QuoteStatus;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private FakeGateway $payments;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->payments = new FakeGateway;
        $this->app->instance(PaymentGateway::class, $this->payments);
    }

    private function invitedRequest(Provider $provider): QuoteRequest
    {
        $quoteRequest = QuoteRequest::factory()->create();
        $quoteRequest->invitedProviders()->attach($provider, ['distance_miles' => 2.4, 'notified_at' => now()]);

        return $quoteRequest;
    }

    public function test_full_card_flow_from_quote_to_charge(): void
    {
        $provider = Provider::factory()->claimed()->payoutsReady()->create();
        $quoteRequest = $this->invitedRequest($provider);

        $this->actingAs($provider->user)
            ->post(route('pro.requests.quote', $quoteRequest), ['amount' => 480, 'message' => 'Two coats, eggshell'])
            ->assertRedirect(route('pro.dashboard'));

        $quote = Quote::firstOrFail();
        $this->assertSame(48000, $quote->amount_cents);

        $this->actingAs($quoteRequest->user)->post(route('quotes.accept', $quote));
        $booking = Booking::firstOrFail();
        $this->assertSame(BookingStatus::AwaitingCard, $booking->status);
        $this->assertSame(695, $booking->service_fee_cents);
        $this->assertSame(QuoteRequestStatus::Booked, $quoteRequest->fresh()->status);

        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('Save test card and book');
        $this->post(route('bookings.card', $booking))->assertRedirect(route('bookings.show', $booking));
        $this->assertSame(BookingStatus::Scheduled, $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->stripe_payment_method_id);

        $this->actingAs($provider->user)->post(route('pro.bookings.complete', $booking))->assertRedirect(route('pro.dashboard'));

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertStringStartsWith('pi_fake_', $booking->stripe_payment_intent_id);
        $this->assertNotNull($booking->charged_at);
    }

    public function test_failed_charge_marks_booking_and_lets_painter_retry(): void
    {
        $booking = Booking::factory()->create();
        $this->payments->failNextCharge = true;

        $this->actingAs($booking->provider->user)
            ->post(route('pro.bookings.complete', $booking))
            ->assertSessionHasErrors('booking');
        $this->assertSame(BookingStatus::PaymentFailed, $booking->fresh()->status);

        $this->post(route('pro.bookings.complete', $booking));
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_painter_without_payouts_can_only_send_direct_pay_quotes(): void
    {
        $provider = Provider::factory()->claimed()->create();
        $quoteRequest = $this->invitedRequest($provider);

        $this->actingAs($provider->user)
            ->post(route('pro.requests.quote', $quoteRequest), ['amount' => 480])
            ->assertSessionHasErrors('amount');

        $this->post(route('pro.requests.quote', $quoteRequest), ['amount' => 480, 'direct_pay' => '1'])
            ->assertRedirect(route('pro.dashboard'));

        $quote = Quote::firstOrFail();
        $this->actingAs($quoteRequest->user)->post(route('quotes.accept', $quote));

        $booking = Booking::firstOrFail();
        $this->assertSame(PaymentMode::Direct, $booking->payment_mode);
        $this->assertSame(BookingStatus::Scheduled, $booking->status);
    }

    public function test_painters_cannot_quote_requests_they_were_not_sent(): void
    {
        $provider = Provider::factory()->claimed()->payoutsReady()->create();
        $quoteRequest = QuoteRequest::factory()->create();

        $this->actingAs($provider->user)->get(route('pro.requests.show', $quoteRequest))->assertForbidden();
        $this->post(route('pro.requests.quote', $quoteRequest), ['amount' => 480])->assertForbidden();
    }

    public function test_second_quote_cannot_be_accepted_after_booking(): void
    {
        $quoteRequest = QuoteRequest::factory()->create();
        $first = Quote::factory()->create(['quote_request_id' => $quoteRequest->id]);
        $second = Quote::factory()->create(['quote_request_id' => $quoteRequest->id]);

        $this->actingAs($quoteRequest->user)->post(route('quotes.accept', $first));
        $this->post(route('quotes.accept', $second))->assertSessionHasErrors('quote');

        $this->assertSame(1, Booking::count());
        $this->assertSame(QuoteStatus::Declined, $second->fresh()->status);
    }

    public function test_cancelling_reopens_the_request_for_other_quotes(): void
    {
        $quoteRequest = QuoteRequest::factory()->create();
        $first = Quote::factory()->create(['quote_request_id' => $quoteRequest->id]);
        $second = Quote::factory()->create(['quote_request_id' => $quoteRequest->id]);

        $this->actingAs($quoteRequest->user)->post(route('quotes.accept', $first));
        $booking = Booking::firstOrFail();
        $this->post(route('bookings.cancel', $booking));

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame(QuoteRequestStatus::Open, $quoteRequest->fresh()->status);
        $this->assertSame(QuoteStatus::Pending, $second->fresh()->status);
        $this->assertSame(QuoteStatus::Declined, $first->fresh()->status);

        $this->post(route('quotes.accept', $second))->assertRedirect();
        $this->assertSame(2, Booking::count());
    }

    public function test_other_customers_cannot_see_or_cancel_a_booking(): void
    {
        $booking = Booking::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('bookings.show', $booking))->assertForbidden();
        $this->post(route('bookings.cancel', $booking))->assertForbidden();
    }
}
