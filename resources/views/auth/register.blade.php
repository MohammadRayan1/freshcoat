<x-layout title="Create account">
    <div class="wrap">
        <form class="card-raised stack-sm narrow" style="margin: 0 auto" method="POST" action="{{ route('register') }}">
            @csrf
            <h1 style="font-size: 2rem">Create your account</h1>
            <label class="field" for="name">Full name
                <input id="name" name="name" autocomplete="name" value="{{ old('name') }}" required autofocus>
            </label>
            @error('name')<p class="error">{{ $message }}</p>@enderror
            <label class="field" for="email">Email
                <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
            </label>
            @error('email')<p class="error">{{ $message }}</p>@enderror
            <label class="field" for="phone">Mobile phone (optional)
                <input id="phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}">
            </label>
            <label class="check" for="sms_opt_in"><input id="sms_opt_in" type="checkbox" name="sms_opt_in" value="1" @checked(old('sms_opt_in'))> Text me about my quotes and bookings. Message and data rates may apply. Reply STOP to opt out. Not required to book.</label>
            <label class="field" for="password">Password
                <input id="password" name="password" type="password" autocomplete="new-password" required>
            </label>
            @error('password')<p class="error">{{ $message }}</p>@enderror
            <label class="field" for="password_confirmation">Confirm password
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </label>
            <button class="btn btn-primary" type="submit">Create account</button>
            <p class="muted small">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </form>
    </div>
</x-layout>
