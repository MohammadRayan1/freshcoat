<x-layout title="Add your business">
    <div class="wrap">
        <form class="card-raised stack-sm" style="margin: 0 auto; max-width: 640px" method="POST" action="{{ route('claim.store') }}">
            @csrf
            <h1 style="font-size: 1.9rem">Add your painting business</h1>
            <label class="field" for="business_name">Business name
                <input id="business_name" name="business_name" value="{{ old('business_name') }}" required>
            </label>
            <div class="grid2">
                <label class="field" for="phone">Business phone
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required>
                </label>
                <label class="field" for="city_id">City
                    <select id="city_id" name="city_id">
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((int) old('city_id') === $city->id)>{{ $city->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="field" for="contractor_registration_number">State contractor registration # (if your state requires one)
                <input id="contractor_registration_number" name="contractor_registration_number" value="{{ old('contractor_registration_number') }}">
            </label>
            <fieldset class="stack-sm" style="border: none; padding: 0; margin: 0">
                <legend class="eyebrow" style="margin-bottom: .4rem">Services you offer</legend>
                <div class="cols">
                    @foreach($services as $service)
                        <label class="check" for="svc-{{ $service->id }}"><input id="svc-{{ $service->id }}" type="checkbox" name="services[]" value="{{ $service->id }}" @checked(in_array($service->id, old('services', [])))> {{ $service->name }}</label>
                    @endforeach
                </div>
            </fieldset>
            @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
            <button class="btn btn-primary" type="submit">Add business</button>
        </form>
    </div>
</x-layout>
