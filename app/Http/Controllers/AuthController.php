<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const SIGNUP_BONUS_TOKENS = 5;

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }

        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $route = Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard';

        return redirect()->intended(route($route));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:191', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'coin_balance' => self::SIGNUP_BONUS_TOKENS,
        ]);

        if (self::SIGNUP_BONUS_TOKENS > 0) {
            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => CoinTransaction::TYPE_TOPUP,
                'amount' => self::SIGNUP_BONUS_TOKENS,
                'balance_after' => self::SIGNUP_BONUS_TOKENS,
                'description' => 'Bonus email signup',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('user.dashboard')
            ->with('registration_success', true);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
