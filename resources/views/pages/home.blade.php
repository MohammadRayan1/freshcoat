<x-layout>
    <div class="wrap stack-lg">
        <section class="hero">
            <div class="stack" style="padding-top: 1rem">
                <span class="tape">King County, WA</span>
                <h1>Local painters. Pay after the job.</h1>
                <p class="muted">Free quotes from painters near you, for interior, exterior and cabinets.</p>
                <div class="swatches" aria-hidden="true"><span style="background:#C9D6C1"></span><span style="background:#E9D9B8"></span><span style="background:#B9C7D6"></span><span style="background:#D8C1BC"></span><span style="background:#3E4B5A"></span></div>
            </div>
            <x-quote-form :services="$services" :cities="$cities" :timings="$timings" />
        </section>

        <section class="facts">
            <div class="fact"><strong>Free quotes</strong><span class="muted">Compare prices side by side.</span></div>
            <div class="fact"><strong>Closest painters first</strong><span class="muted">Up to 8 local pros per job.</span></div>
            <div class="fact"><strong>Charged when it's done</strong><span class="muted">5% fee, never more than $6.95.</span></div>
        </section>

        <section class="painter-band">
            <div>
                <h2>Own a painting business?</h2>
                <p>Claim your free listing. No monthly fee, no lead fees.</p>
            </div>
            <a class="btn btn-primary btn-lg" href="{{ route('claim.index') }}">Claim my business</a>
        </section>

        <section class="stack">
            <h2>Painters in King County</h2>
            <div class="cols">
                @foreach($cities as $city)
                    <a href="{{ route('locations.city', [$city->stateSlug(), $city->slug]) }}">{{ $city->name }}</a>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
