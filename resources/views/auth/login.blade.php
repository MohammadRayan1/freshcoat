<x-layout title="Sign in">
    <div class="wrap">
        <form class="card-raised stack-sm narrow" style="margin: 0 auto" method="POST" action="{{ route('login') }}">
            @csrf
            <h1 style="font-size: 2rem">Sign in</h1>
            <label class="field" for="email">Email
                <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label class="field" for="password">Password
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </label>
            <label class="check" for="remember"><input id="remember" type="checkbox" name="remember" value="1"> Keep me signed in</label>
            @error('email')<p class="error" role="alert">{{ $message }}</p>@enderror
            <button class="btn btn-primary" type="submit">Sign in</button>
            <p class="muted small">New to FreshCoat? <a href="{{ route('register') }}">Create an account</a></p>
        </form>
    </div>
</x-layout>
