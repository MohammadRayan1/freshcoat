<x-layout title="Claim your business">
    <div class="wrap stack" style="max-width: 760px">
        <span class="tape">For painters</span>
        <h1 style="font-size: clamp(1.8rem, 4vw, 2.6rem)">Find your business</h1>
        <form class="card row" method="GET" action="{{ route('claim.index') }}" style="align-items: end">
            <label class="field" for="name" style="flex: 2 1 220px">Business name
                <input id="name" name="name" value="{{ $name }}" placeholder="e.g. Fremont Finish Co." required>
            </label>
            <label class="field" for="city_id" style="flex: 1 1 180px">City (optional)
                <select id="city_id" name="city_id">
                    <option value="">Any city</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected($cityId === $city->id)>{{ $city->label() }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        @error('claim')<p class="error" role="alert">{{ $message }}</p>@enderror

        @if($name !== '')
            <div class="list">
                @forelse($results as $provider)
                    <x-painter-row :provider="$provider">
                        @if($provider->isClaimed())
                            <span class="pill pill-muted">Already claimed</span>
                        @else
                            @auth
                                <form method="POST" action="{{ route('claim.send', $provider) }}">@csrf<button class="btn btn-primary" type="submit">Claim</button></form>
                            @else
                                <a class="btn btn-primary" href="{{ route('register') }}">Sign up to claim</a>
                            @endauth
                        @endif
                    </x-painter-row>
                @empty
                    <p class="muted">No listings match "{{ $name }}".</p>
                @endforelse
            </div>
        @endif
        <p class="muted">Not listed? <a href="{{ route('claim.create') }}">Add your business</a></p>
    </div>
</x-layout>
