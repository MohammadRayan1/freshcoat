<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\QuoteRequestController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'That email and password don\'t match an account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->afterAuth($request);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'sms_opt_in' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            ...$data,
            'role' => UserRole::Customer,
            'sms_opt_in' => $request->boolean('sms_opt_in'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->afterAuth($request);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Finish a quote request that was started before signing in, otherwise go where the user was headed.
     */
    private function afterAuth(Request $request): RedirectResponse
    {
        if ($pending = $request->session()->pull(QuoteRequestController::PENDING_SESSION_KEY)) {
            return app(QuoteRequestController::class)->createFor($request->user(), $pending);
        }

        $default = $request->user()->provider ? route('pro.dashboard') : route('quote-requests.index');

        return redirect()->intended($default);
    }
}
