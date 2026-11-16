<x-layout title="Verify your business">
    <div class="wrap">
        <form class="card-raised stack-sm narrow" style="margin: 0 auto" method="POST" action="{{ route('claim.verify', $provider) }}">
            @csrf
            <h1 style="font-size: 1.9rem">Claim {{ $provider->business_name }}</h1>
            <p class="muted">Enter the 6-digit code we sent to {{ $verification->maskedDestination() }}. It expires in 15 minutes.</p>
            <label class="field" for="code">Verification code
                <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus>
            </label>
            @error('code')<p class="error" role="alert">{{ $message }}</p>@enderror
            <button class="btn btn-primary" type="submit">Verify and claim</button>
        </form>
        <form method="POST" action="{{ route('claim.send', $provider) }}" class="narrow" style="margin: 1rem auto 0">@csrf<button class="btn-link" type="submit">Send a new code</button></form>
    </div>
</x-layout>
