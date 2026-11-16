<x-layout title="Painter settings">
    <div class="wrap stack" style="max-width: 820px">
        <a class="muted small" href="{{ route('pro.dashboard') }}" style="text-decoration: none">← Dashboard</a>
        <h1 style="font-size: 2.2rem">Settings</h1>

        <div class="card row between">
            <div class="stack-sm" style="gap: .2rem">
                <h3>Payouts</h3>
                <span class="meta">{{ $provider->canAcceptCardPayments() ? 'Connected. Card payments go to your Stripe account.' : 'Not connected. Connect Stripe to take card payments.' }}</span>
            </div>
            @if($provider->canAcceptCardPayments())
                <span class="pill pill-ok">Connected</span>
            @else
                <a class="btn btn-primary" href="{{ route('pro.payouts.connect') }}">{{ $provider->stripe_account_id ? 'Continue payout setup' : 'Connect payouts' }}</a>
            @endif
        </div>

        <form class="card stack" method="POST" action="{{ route('pro.settings.update') }}">
            @csrf @method('PUT')
            <h3>Business profile</h3>
            <label class="field" for="business_name">Business name<input id="business_name" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required></label>
            <div class="grid2">
                <label class="field" for="phone">Phone<input id="phone" name="phone" value="{{ old('phone', $provider->phone) }}"></label>
                <label class="field" for="website">Website<input id="website" name="website" type="url" value="{{ old('website', $provider->website) }}" placeholder="https://"></label>
            </div>
            <div class="grid2">
                <label class="field" for="service_radius_miles">Service radius (miles)<input id="service_radius_miles" name="service_radius_miles" type="number" min="1" max="100" value="{{ old('service_radius_miles', $provider->service_radius_miles) }}" required></label>
                <label class="field" for="years_in_business">Years in business<input id="years_in_business" name="years_in_business" type="number" min="0" value="{{ old('years_in_business', $provider->years_in_business) }}"></label>
            </div>
            <label class="field" for="contractor_registration_number">State contractor registration #<input id="contractor_registration_number" name="contractor_registration_number" value="{{ old('contractor_registration_number', $provider->contractor_registration_number) }}"></label>
            <label class="field" for="description">About your business<textarea id="description" name="description" rows="3">{{ old('description', $provider->description) }}</textarea></label>
            <label class="check" for="allows_direct_pay"><input id="allows_direct_pay" type="checkbox" name="allows_direct_pay" value="1" @checked(old('allows_direct_pay', $provider->allows_direct_pay))> Let customers pay me directly (I prepay the service fee)</label>

            <h3>Services and starting prices</h3>
            <div class="table-scroll">
                <table class="ledger">
                    <thead><tr><th>Offer</th><th>Service</th><th class="amt">Your starting price ($)</th></tr></thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td><input type="checkbox" id="svc-{{ $service->id }}" name="services[]" value="{{ $service->id }}" @checked($offered->has($service->id)) aria-label="Offer {{ $service->name }}"></td>
                                <td><label for="svc-{{ $service->id }}">{{ $service->name }}</label></td>
                                <td class="amt" style="width: 160px"><input id="price-{{ $service->id }}" name="prices[{{ $service->id }}]" type="number" min="0" step="1" value="{{ $offered->has($service->id) && $offered[$service->id]->pivot->book_now_price_cents ? $offered[$service->id]->pivot->book_now_price_cents / 100 : '' }}" placeholder="{{ $service->starting_price_cents / 100 }}" aria-label="{{ $service->name }} starting price"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
            <button class="btn btn-primary" type="submit">Save settings</button>
        </form>
    </div>
</x-layout>
