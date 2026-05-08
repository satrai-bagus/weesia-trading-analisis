<?php

namespace App\Http\Controllers;

use App\Models\TradeSignal;
use App\Models\UserSignalPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserSignalPositionController extends Controller
{
    public function store(Request $request, TradeSignal $tradeSignal): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessSignal($tradeSignal)) {
            return back()->withErrors([
                'position' => 'Buka analisa terlebih dahulu sebelum memasukkannya ke posisi atau pantauan.',
            ]);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in([
                UserSignalPosition::TYPE_POSITION,
                UserSignalPosition::TYPE_WATCHLIST,
            ])],
        ]);

        UserSignalPosition::updateOrCreate(
            [
                'user_id' => $user->id,
                'trade_signal_id' => $tradeSignal->id,
            ],
            [
                'type' => $validated['type'],
                'selected_at' => now(),
            ],
        );

        $label = $validated['type'] === UserSignalPosition::TYPE_POSITION ? 'Posisi' : 'Pantauan';

        return back()->with('status', "Analisa {$tradeSignal->ticker} masuk ke {$label} kamu.");
    }

    public function destroy(TradeSignal $tradeSignal): RedirectResponse
    {
        Auth::user()
            ->signalPositions()
            ->where('trade_signal_id', $tradeSignal->id)
            ->delete();

        return back()->with('status', "Analisa {$tradeSignal->ticker} dihapus dari posisi/pantauan kamu.");
    }
}
