<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    private const SIGNUP_BONUS_TOKENS = 5;

    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google dibatalkan.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menghubungkan dengan Google. Coba lagi.',
            ]);
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google tidak punya email yang bisa dipakai.',
            ]);
        }

        $user = DB::transaction(function () use ($googleUser, $email) {
            $existing = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $email)
                ->first();

            if ($existing) {
                $updates = [
                    'google_id' => $googleUser->getId(),
                ];

                if ($googleUser->getAvatar() && $existing->avatar_url !== $googleUser->getAvatar()) {
                    $updates['avatar_url'] = $googleUser->getAvatar();
                }

                if (! $existing->name && $googleUser->getName()) {
                    $updates['name'] = $googleUser->getName();
                }

                $existing->forceFill($updates)->save();

                return $existing;
            }

            $created = User::create([
                'name' => $googleUser->getName() ?: explode('@', $email)[0],
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'role' => 'user',
                'coin_balance' => self::SIGNUP_BONUS_TOKENS,
                'email_verified_at' => now(),
            ]);

            if (self::SIGNUP_BONUS_TOKENS > 0) {
                CoinTransaction::create([
                    'user_id' => $created->id,
                    'type' => CoinTransaction::TYPE_TOPUP,
                    'amount' => self::SIGNUP_BONUS_TOKENS,
                    'balance_after' => self::SIGNUP_BONUS_TOKENS,
                    'description' => 'Bonus signup via Google',
                ]);
            }

            return $created;
        });

        Auth::login($user, true);
        $request->session()->regenerate();

        $route = $user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard';

        return redirect()->intended(route($route));
    }
}
