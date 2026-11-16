<x-layout :title="$service->name" :description="$service->description.' Compare quotes from local painters on FreshCoat.'">
    <div class="wrap stack-lg">
        <section class="hero">
            <div class="stack" style="padding-top: 1rem">
                <a class="muted small" href="{{ route('services.index') }}" style="text-decoration: none">← All services</a>
                <span class="tape">{{ $service->category }}</span>
                <h1>{{ $service->name }}</h1>
                <p class="muted" style="max-width: 52ch">{{ $service->description }}</p>
                <p><span class="muted">Typical starting price</span> <strong class="num">{{ \App\Support\Fees::money($service->starting_price_cents) }} / {{ $service->unit }}</strong></p>
            </div>
            <x-quote-form :services="$services" :cities="$cities" :timings="$timings" :selected-service="$service" :heading="'Get '.$service->name.' quotes'" />
        </section>
        <section class="stack">
            <h2>{{ $service->name }} by city</h2>
            <div class="cols">
                @foreach($popularCities as $city)
                    <a href="{{ route('locations.city', [$city->stateSlug(), $city->slug, $service]) }}">{{ $city->label() }}</a>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
