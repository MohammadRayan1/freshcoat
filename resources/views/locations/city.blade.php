@php
    $title = ($service ? $service->name : 'Painters').' in '.$city->label();
@endphp
<x-layout :title="$title" :description="'Compare quotes from '.($service ? strtolower($service->name).' ' : '').'painters near '.$city->label().'. Your card is charged only after the job is done.'">
    <div class="wrap stack-lg">
        <section class="hero">
            <div class="stack" style="padding-top: 1rem">
                <a class="muted small" href="{{ route('locations.index') }}" style="text-decoration: none">← All cities</a>
                <h1>{{ $title }}</h1>
                <p class="muted">Painting businesses near {{ $city->name }} who offer {{ strtolower($selectedService->name) }}, closest first.</p>
                <div class="chips">
                    @foreach($services as $s)
                        <a href="{{ route('locations.city', [$city->stateSlug(), $city->slug, $s]) }}" @if($s->is($selectedService)) aria-current="page" @endif>{{ $s->name }}</a>
                    @endforeach
                </div>
            </div>
            <x-quote-form :services="$services" :cities="$cities" :timings="$timings" :selected-service="$selectedService" :selected-city="$city" />
        </section>
        <section class="stack">
            <h2>{{ $painters->count() }} {{ str('painter')->plural($painters->count()) }} near {{ $city->name }}</h2>
            @forelse($painters as $row)
                <x-painter-row :provider="$row['provider']" :distance="$row['distance']">
                    <a class="btn" href="{{ route('providers.show', $row['provider']) }}">View</a>
                </x-painter-row>
            @empty
                <p class="muted">No painters listed here yet. Send a request anyway and we'll invite local painters.</p>
            @endforelse
        </section>
    </div>
</x-layout>
