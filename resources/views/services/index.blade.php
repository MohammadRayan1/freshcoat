<x-layout title="Painting services" description="Interior, exterior and specialty painting services with typical starting prices.">
    <div class="wrap stack-lg">
        <div class="stack">
            <span class="tape">Services</span>
            <h1>Painting services</h1>
            <p class="muted" style="max-width: 60ch">Typical starting prices for a standard job. Your final price comes from the painter's quote and depends on size, prep work, coats and sheen.</p>
        </div>
        @foreach($servicesByCategory as $category => $services)
            <section class="stack">
                <h2>{{ $category }}</h2>
                <div class="svc-grid">
                    @foreach($services as $service)
                        <x-service-card :service="$service" />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-layout>
