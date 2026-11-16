@props(['provider', 'distance' => null])
@php
    $initials = collect(explode(' ', preg_replace('/[^A-Za-z ]/', '', $provider->business_name)))->filter()->take(2)->map(fn ($w) => $w[0])->implode('');
    $colors = ['#C9D6C1', '#E9D9B8', '#B9C7D6', '#D8C1BC'];
@endphp
<article class="pro">
    <div class="avatar" style="background: {{ $colors[$provider->id % 4] }}">{{ strtoupper($initials) }}</div>
    <div>
        <h3><a href="{{ route('providers.show', $provider) }}" style="color: inherit; text-decoration: none">{{ $provider->business_name }}</a></h3>
        <div class="meta">
            @if($distance !== null){{ $distance }} mi away · @endif
            {{ $provider->city->label() }}
            @if($provider->rating) · ★ {{ number_format($provider->rating, 1) }} ({{ $provider->reviews_count }}) @endif
            @if($provider->years_in_business) · {{ $provider->years_in_business }} yrs @endif
        </div>
        <div class="row" style="gap: .35rem; margin-top: .3rem">
            @if($provider->isClaimed())<span class="pill pill-ok">Responds on FreshCoat</span>@else<span class="pill pill-muted">Unclaimed listing</span>@endif
            @if($provider->insurance_verified_at)<span class="pill">Insurance on file</span>@endif
        </div>
    </div>
    <div style="display: grid; gap: .4rem; justify-items: end">{{ $slot }}</div>
</article>
