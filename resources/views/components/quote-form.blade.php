@props(['services', 'cities', 'timings', 'selectedService' => null, 'selectedCity' => null, 'heading' => 'Get free quotes'])
@php
    $serviceData = $services->map(fn ($s) => ['id' => $s->id, 'price' => $s->starting_price_cents, 'sizes' => $s->sizes()])->values();
    $current = $services->firstWhere('id', old('service_id', $selectedService?->id)) ?? $services->first();
    $isCustom = old('service_id') === \App\Models\QuoteRequest::CUSTOM;
    $cityId = old('city_id', $selectedCity?->id ?? $cities->firstWhere('name', 'Seattle')?->id);
@endphp
<form class="card-raised stack-sm" method="POST" action="{{ route('quote-requests.store') }}" enctype="multipart/form-data" data-quote-form data-services='@json($serviceData)'>
    @csrf
    <h3>{{ $heading }}</h3>
    <label class="field" for="qf-service">What needs painting?
        <select id="qf-service" name="service_id">
            @foreach($services->groupBy('category') as $category => $group)
                <optgroup label="{{ $category }}">
                    @foreach($group as $service)
                        <option value="{{ $service->id }}" @selected($service->id === $current->id)>{{ $service->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
            <optgroup label="Other">
                <option value="{{ \App\Models\QuoteRequest::CUSTOM }}" @selected($isCustom)>Something else (describe it)</option>
            </optgroup>
        </select>
    </label>
    <label class="field" for="qf-custom" data-custom-only @unless($isCustom) hidden @endunless>What's the job?
        <input id="qf-custom" name="custom_title" maxlength="120" value="{{ old('custom_title') }}" placeholder="e.g. Garage floor, stair railing, front door" @if($isCustom) required @else disabled @endif>
    </label>
    <div class="grid2">
        <label class="field" for="qf-size" data-preset-only @if($isCustom) hidden @endif>Size
            <select id="qf-size" name="size">
                @foreach($current->sizes() as $i => $size)
                    <option value="{{ $i }}" @selected((int) old('size', 1) === $i)>{{ $size['label'] }}</option>
                @endforeach
            </select>
        </label>
        <label class="field" for="qf-timing">When
            <select id="qf-timing" name="timing">
                @foreach($timings as $value => $label)
                    <option value="{{ $value }}" @selected(old('timing', 'two_weeks') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div class="grid2">
        <label class="field" for="qf-city">City
            <select id="qf-city" name="city_id">
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" @selected((int) $cityId === $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field" for="qf-zip">ZIP code
            <input id="qf-zip" name="zip" inputmode="numeric" maxlength="5" autocomplete="postal-code" value="{{ old('zip') }}" placeholder="98103" required>
        </label>
    </div>
    <details class="more" @if($isCustom || old('details') || old('customer_supplies_paint') === '1' || $errors->has('media*')) open @endif>
        <summary>Add photos &amp; details <span data-preset-only @if($isCustom) hidden @endif>(optional)</span></summary>
        <div class="field">
            <span>Photos or video</span>
            <label class="upload" for="qf-media">
                <input id="qf-media" name="media[]" type="file" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,video/webm" data-max-files="{{ \App\Models\QuoteRequestMedia::MAX_FILES }}" data-max-mb="{{ \App\Models\QuoteRequestMedia::MAX_KILOBYTES / 1024 }}">
                <strong>Add photos or a short video</strong>
                <span class="muted small">Helps painters quote without a visit. Up to {{ \App\Models\QuoteRequestMedia::MAX_FILES }} files, {{ \App\Models\QuoteRequestMedia::MAX_KILOBYTES / 1024 }} MB each.</span>
            </label>
            <div class="upload-previews" data-media-previews></div>
        </div>
        <label class="field" for="qf-paint">Paint
            <select id="qf-paint" name="customer_supplies_paint">
                <option value="0">Painter supplies paint</option>
                <option value="1" @selected(old('customer_supplies_paint') === '1')>I have my own paint</option>
            </select>
        </label>
        <label class="field" for="qf-details">Notes for the painter
            <textarea id="qf-details" name="details" rows="2" placeholder="Ceiling height, colors, wall condition, anything painters should know">{{ old('details') }}</textarea>
        </label>
    </details>
    @if($errors->any())
        <p class="error" role="alert">{{ $errors->first() }}</p>
    @endif
    <div class="estimate">
        <span class="muted" data-estimate-label>Typical range</span>
        <strong data-estimate>—</strong>
    </div>
    <button class="btn btn-primary" type="submit">Get quotes</button>
    <p class="muted small">Free. No card needed to get quotes.</p>
</form>
