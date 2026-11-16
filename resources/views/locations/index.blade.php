<x-layout title="Locations">
    <div class="wrap stack-lg">
        <div class="stack"><span class="tape">King County, WA</span><h1>Find painters near you</h1></div>
        <div class="cols">
            @foreach($cities as $city)
                <a href="{{ route('locations.city', [$city->stateSlug(), $city->slug]) }}">{{ $city->name }}</a>
            @endforeach
        </div>
    </div>
</x-layout>
