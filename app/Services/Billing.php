<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\SignalUnlock;
use App\Models\TradeSignal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Billing
{
    public function topUp(User $user, int $amount, ?string $note = null, ?User $admin = null): CoinTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Jumlah top-up harus lebih dari 0.');
        }

        return DB::transaction(function () use ($user, $amount, $note, $admin) {
            $fresh = User::lockForUpdate()->findOrFail($user->id);
            $newBalance = $fresh->coin_balance + $amount;
            $fresh->forceFill(['coin_balance' => $newBalance])->save();

            return CoinTransaction::create([
                'user_id' => $fresh->id,
                'type' => CoinTransaction::TYPE_TOPUP,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $note,
                'created_by_id' => $admin?->id,
            ]);
        });
    }

    public function extendSubscription(User $user, int $months, ?User $admin = null, ?string $note = null): CoinTransaction
    {
        if ($months <= 0) {
            throw new RuntimeException('Durasi subscription harus minimal 1 bulan.');
        }

        return DB::transaction(function () use ($user, $months, $admin, $note) {
            $fresh = User::lockForUpdate()->findOrFail($user->id);
            $base = ($fresh->subscription_until && $fresh->subscription_until->isFuture())
                ? $fresh->subscription_until
                : Carbon::now();
            $newUntil = $base->copy()->addMonths($months);
            $fresh->forceFill(['subscription_until' => $newUntil])->save();

            return CoinTransaction::create([
                'user_id' => $fresh->id,
                'type' => CoinTransaction::TYPE_SUBSCRIPTION,
                'amount' => 0,
                'balance_after' => $fresh->coin_balance,
                'description' => $note ?: "Perpanjang subscription {$months} bulan (sampai {$newUntil->format('d M Y')})",
                'created_by_id' => $admin?->id,
            ]);
        });
    }

    public function unlockSignal(User $user, TradeSignal $signal): SignalUnlock
    {
        return DB::transaction(function () use ($user, $signal) {
            $existing = SignalUnlock::where('user_id', $user->id)
                ->where('trade_signal_id', $signal->id)
                ->first();
            if ($existing) {
                return $existing;
            }

            $cost = $signal->coinCostValue();
            $fresh = User::lockForUpdate()->findOrFail($user->id);
            if ($fresh->coin_balance < $cost) {
                throw new RuntimeException('Saldo koin tidak cukup.');
            }

            $newBalance = $fresh->coin_balance - $cost;
            $fresh->forceFill(['coin_balance' => $newBalance])->save();

            $unlock = SignalUnlock::create([
                'user_id' => $fresh->id,
                'trade_signal_id' => $signal->id,
                'coin_cost' => $cost,
                'unlocked_at' => now(),
            ]);

            CoinTransaction::create([
                'user_id' => $fresh->id,
                'type' => CoinTransaction::TYPE_SPEND,
                'amount' => -$cost,
                'balance_after' => $newBalance,
                'description' => "Buka analisa {$signal->ticker} #{$signal->id}",
                'trade_signal_id' => $signal->id,
            ]);

            return $unlock;
        });
    }
}
