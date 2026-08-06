<?php

namespace App\Services;

use App\Models\TradeSignal;
use App\Models\UserSignalPosition;
use Illuminate\Support\Collection;

class SignalAutoHit
{
    public function evaluate(iterable $signals, array $priceMap): Collection
    {
        $changed = collect();

        foreach ($signals as $signal) {
            if (! $signal instanceof TradeSignal) {
                continue;
            }

            $live = $priceMap[$signal->ticker] ?? null;
            if (! $live) {
                continue;
            }

            $newStatus = $this->detectStatus($signal, (float) $live);
            if ($newStatus === null || $newStatus === $signal->status) {
                continue;
            }

            $signal->forceFill([
                'status' => $newStatus,
                'auto_hit_at' => now(),
            ])->save();

            // Hasil yang berubah (mis. TP1 lanjut ke TP2 atau jatuh ke SL) harus
            // muncul lagi sebagai notifikasi meski hasil lamanya sudah dibaca.
            UserSignalPosition::where('trade_signal_id', $signal->id)
                ->whereNotNull('dismissed_at')
                ->update(['dismissed_at' => null]);

            $changed->push($signal);
        }

        return $changed;
    }

    public function evaluateAll(array $priceMap): Collection
    {
        $signals = TradeSignal::whereNotIn('status', [
            TradeSignal::STATUS_HIT_TP2,
            TradeSignal::STATUS_HIT_SL,
        ])->get();

        return $this->evaluate($signals, $priceMap);
    }

    private function detectStatus(TradeSignal $signal, float $live): ?string
    {
        if (in_array($signal->status, [TradeSignal::STATUS_HIT_TP2, TradeSignal::STATUS_HIT_SL], true)) {
            return null;
        }

        $isLong = $signal->position_side !== TradeSignal::SIDE_SHORT;
        $sl = (float) $signal->stop_loss;
        $tp1 = (float) $signal->take_profit;
        $tp2 = $signal->take_profit_2 ? (float) $signal->take_profit_2 : null;

        if ($sl > 0 && ($isLong ? $live <= $sl : $live >= $sl)) {
            return TradeSignal::STATUS_HIT_SL;
        }

        if ($tp2 && ($isLong ? $live >= $tp2 : $live <= $tp2)) {
            return TradeSignal::STATUS_HIT_TP2;
        }

        if ($signal->status === TradeSignal::STATUS_ACTIVE
            && $tp1 > 0
            && ($isLong ? $live >= $tp1 : $live <= $tp1)) {
            return TradeSignal::STATUS_HIT_TP;
        }

        return null;
    }
}
