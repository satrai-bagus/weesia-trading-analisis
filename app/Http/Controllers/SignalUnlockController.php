<?php

namespace App\Http\Controllers;

use App\Models\TradeSignal;
use App\Services\Billing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SignalUnlockController extends Controller
{
    public function unlock(TradeSignal $tradeSignal, Billing $billing): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->hasActiveSubscription()) {
            return back()->with('status', 'Signal sudah bisa kamu akses.');
        }

        if ($tradeSignal->status !== TradeSignal::STATUS_ACTIVE) {
            return back()->with('status', 'Arsip signal yang sudah selesai bisa kamu akses gratis.');
        }

        try {
            $billing->unlockSignal($user, $tradeSignal);
        } catch (RuntimeException $e) {
            return back()->withErrors(['unlock' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Signal unlock failed.', [
                'user_id' => $user->id,
                'trade_signal_id' => $tradeSignal->id,
                'trade_signal_ticker' => $tradeSignal->ticker,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['unlock' => 'Gagal buka signal. Coba lagi.']);
        }

        return back()->with('status', "Signal {$tradeSignal->ticker} berhasil dibuka.");
    }
}
