@props(['service'])
<article class="svc">
    <div class="chip" style="background: {{ $service->swatch_color ?? '#C9D6C1' }}"></div>
    <div class="body">
        <span class="swatch-name">{{ $service->swatch_label }}</span>
        <h3><a href="{{ route('services.show', $service) }}" style="color: inherit; text-decoration: none">{{ $service->name }}</a></h3>
        <p class="muted small">{{ $service->description }}</p>
        <div class="price-row">
            <span class="muted small">from</span>
            <span class="num" style="font-weight: 700">{{ \App\Support\Fees::money($service->starting_price_cents) }} <span class="muted" style="font-weight: 500">/ {{ $service->unit }}</span></span>
        </div>
        <a class="btn" href="{{ route('services.show', $service) }}" style="margin-top: .4rem">Get quotes</a>
    </div>
</article>
