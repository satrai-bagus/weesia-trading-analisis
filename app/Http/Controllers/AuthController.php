<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const SIGNUP_BONUS_TOKENS = 5;

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }

        $this->rememberRedirectTarget($request);

        return view('auth.login', [
            'checkoutIntent' => $this->hasCheckoutIntent($request),
        ]);
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }

        $this->rememberRedirectTarget($request);

        return view('auth.register', [
            'checkoutIntent' => $this->hasCheckoutIntent($request),
        ]);
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
            ->intended(route('user.dashboard'))
            ->with('registration_success', true);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Store a safe internal "?redirect=" path as the intended URL so that
     * login / register / Google all land the user back where they wanted
     * (e.g. /buy) instead of the generic dashboard.
     */
    private function rememberRedirectTarget(Request $request): void
    {
        $target = $request->query('redirect');

        if (is_string($target)
            && Str::startsWith($target, '/')
            && ! Str::startsWith($target, ['//', '/\\'])) {
            $request->session()->put('url.intended', $target);
        }
    }

    /**
     * Whether the visitor arrived here on the way to checkout, so the auth
     * pages can show a "lanjut ke checkout" message instead of a bare wall.
     */
    private function hasCheckoutIntent(Request $request): bool
    {
        $intended = (string) ($request->query('redirect') ?: $request->session()->get('url.intended', ''));

        return Str::contains($intended, '/buy');
    }
}
