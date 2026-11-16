<x-layout :title="$provider->business_name">
    <div class="wrap stack-lg">
        <div class="split">
            <div class="stack">
                <a class="muted small" href="{{ route('locations.city', [$provider->city->stateSlug(), $provider->city->slug]) }}" style="text-decoration: none">← Painters in {{ $provider->city->label() }}</a>
                <h1>{{ $provider->business_name }}</h1>
                <p class="muted">{{ $provider->city->label() }} · serves up to {{ $provider->service_radius_miles }} miles
                    @if($provider->years_in_business) · {{ $provider->years_in_business }} years in business @endif</p>
                <div class="row">
                    @if($provider->rating)<span class="pill">★ {{ number_format($provider->rating, 1) }} · {{ $provider->reviews_count }} reviews</span>@endif
                    @if($provider->insurance_verified_at)<span class="pill pill-ok">Insurance on file</span>@endif
                    @if($provider->contractor_registration_number)<span class="pill">Reg. # {{ $provider->contractor_registration_number }}</span>@endif
                    @unless($provider->isClaimed())<span class="pill pill-muted">Unclaimed listing</span>@endunless
                </div>
                @if($provider->description)<p style="max-width: 60ch">{{ $provider->description }}</p>@endif
            </div>
            <div class="card stack-sm">
                @if($provider->isClaimed())
                    <h3>Get a quote</h3>
                    <p class="muted small">Send a request and nearby painters, including this one, can quote it.</p>
                    <a class="btn btn-primary" href="{{ route('home') }}">Request quotes</a>
                @else
                    <h3>Is this your business?</h3>
                    <p class="muted small">Claim it for free to respond to homeowners near you.</p>
                    <a class="btn btn-primary" href="{{ route('claim.index', ['name' => $provider->business_name]) }}">Claim this listing</a>
                @endif
            </div>
        </div>
        <section class="stack">
            <h2>Services</h2>
            <div class="chips">
                @foreach($provider->services as $service)
                    <a href="{{ route('services.show', $service) }}">{{ $service->name }}@if($service->pivot->book_now_price_cents) · from {{ \App\Support\Fees::money($service->pivot->book_now_price_cents) }}@endif</a>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
