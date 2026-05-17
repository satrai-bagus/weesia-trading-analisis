<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Services\Billing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminUserController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        $users = User::orderByDesc('id')
            ->withCount(['signalUnlocks', 'signalPositions', 'tradeSignals'])
            ->withSum(['coinTransactions as total_topup' => fn($q) => $q->where('type', CoinTransaction::TYPE_TOPUP)], 'amount')
            ->withSum(['coinTransactions as total_subscription' => fn($q) => $q->where('type', CoinTransaction::TYPE_SUBSCRIPTION)], 'amount')
            ->withSum(['coinTransactions as total_spent' => fn($q) => $q->where('amount', '<', 0)], 'amount')
            ->get();

        $transactions = CoinTransaction::with(['user', 'createdBy', 'tradeSignal'])
            ->latest()
            ->limit(150)
            ->get();

        $stats = [
            'total_users' => $users->count(),
            'subscribers' => $users->filter(fn(User $u) => $u->hasActiveSubscription())->count(),
            'total_coins' => (int) $users->sum('coin_balance'),
            'total_topup' => (int) CoinTransaction::where('type', CoinTransaction::TYPE_TOPUP)->sum('amount'),
        ];

        $topUsers = $users
            ->filter(fn(User $u) => $u->role === 'user')
            ->map(function (User $u) {
                $u->lifetime_value = (int) $u->total_topup + abs((int) $u->total_subscription);
                return $u;
            })
            ->sortByDesc('lifetime_value')
            ->take(5)
            ->values();

        return view('dashboard.admin-users', [
            'users' => $users,
            'transactions' => $transactions,
            'stats' => $stats,
            'topUsers' => $topUsers,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'string', 'min:6', 'max:191'],
        ]);

        if ($user->id == Auth::id() && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Tidak bisa demote akun sendiri dari admin.']);
        }

        $updates = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($updates)->save();

        return back()->with('status', "User {$user->name} berhasil diupdate.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($user->id == Auth::id()) {
            return back()->withErrors(['delete' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $signalCount = $user->tradeSignals()->count();
        if ($signalCount > 0) {
            return back()->withErrors(['delete' => "User {$user->name} masih punya {$signalCount} signal. Hapus atau pindahkan signal dulu."]);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('status', "User {$name} berhasil dihapus.");
    }

    public function store(Request $request, Billing $billing): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:191'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'coin_balance' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'subscription_months' => ['nullable', 'integer', 'min:0', 'max:24'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $initialCoins = (int) ($validated['coin_balance'] ?? 0);
        if ($initialCoins > 0) {
            $billing->topUp($user, $initialCoins, 'Saldo awal saat user dibuat', Auth::user());
        }

        $months = (int) ($validated['subscription_months'] ?? 0);
        if ($months > 0) {
            $billing->extendSubscription($user, $months, Auth::user(), 'Subscription awal saat user dibuat');
        }

        return back()->with('status', "User {$user->name} berhasil dibuat.");
    }

    public function topUp(Request $request, User $user, Billing $billing): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:191'],
        ]);

        try {
            $billing->topUp($user, (int) $validated['amount'], $validated['note'] ?? null, Auth::user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        } catch (Throwable) {
            return back()->withErrors(['amount' => 'Gagal melakukan top-up. Coba lagi.']);
        }

        return back()->with('status', "Top-up {$validated['amount']} koin untuk {$user->name} berhasil.");
    }

    public function extendSubscription(Request $request, User $user, Billing $billing): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:24'],
            'note' => ['nullable', 'string', 'max:191'],
        ]);

        try {
            $billing->extendSubscription($user, (int) $validated['months'], Auth::user(), $validated['note'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['months' => $e->getMessage()]);
        } catch (Throwable) {
            return back()->withErrors(['months' => 'Gagal extend subscription. Coba lagi.']);
        }

        return back()->with('status', "Subscription {$user->name} diperpanjang {$validated['months']} bulan.");
    }
}
