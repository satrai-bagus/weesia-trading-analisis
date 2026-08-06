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

        // Analisa yang sudah selesai dibekukan: tanpa guard ini user bisa
        // menambah "kemenangan" lama dari arsip atau membalik posisi kalah jadi
        // pantauan supaya hilang dari statistik performa.
        if ($tradeSignal->isClosed()) {
            return back()->withErrors([
                'position' => "Analisa {$tradeSignal->ticker} sudah selesai - hasilnya tidak bisa ditambahkan atau diubah di meja kamu.",
            ]);
        }

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
            // Posisi wajib menyertakan harga entry pribadi: statistik performa
            // dihitung dari harga ini, bukan dari entry analisa. Batasnya
            // mengikuti kolom decimal(20,8): di luar itu MySQL strict melempar
            // out-of-range (500) atau membulatkan diam-diam ke 0.
            'entry_price' => [
                'required_if:type,'.UserSignalPosition::TYPE_POSITION,
                'nullable',
                'numeric',
                'between:0.00000001,999999999999',
            ],
        ], [
            'entry_price.required_if' => 'Isi harga entry kamu dulu - performa posisi dihitung dari harga ini.',
            'entry_price.between' => 'Harga entry harus di antara $0.00000001 dan $999.999.999.999.',
        ]);

        $values = [
            'type' => $validated['type'],
            'selected_at' => now(),
            // Memasang ulang signal menghidupkan lagi status "belum dibaca"-nya.
            'dismissed_at' => null,
        ];

        // Pantauan tidak menimpa entry yang sudah tercatat; posisi selalu
        // menyimpan entry terbaru yang diinput user.
        if ($validated['type'] === UserSignalPosition::TYPE_POSITION) {
            $values['entry_price'] = (float) $validated['entry_price'];
        }

        UserSignalPosition::updateOrCreate(
            [
                'user_id' => $user->id,
                'trade_signal_id' => $tradeSignal->id,
            ],
            $values,
        );

        $label = $validated['type'] === UserSignalPosition::TYPE_POSITION ? 'Posisi' : 'Pantauan';

        return back()->with('status', "Analisa {$tradeSignal->ticker} masuk ke {$label} kamu.");
    }

    public function destroy(TradeSignal $tradeSignal): RedirectResponse
    {
        // Posisi yang sudah selesai tidak dihapus permanen - hasilnya (termasuk
        // yang rugi) harus tetap masuk statistik performa. Menghapusnya cukup
        // berarti "tandai sudah dibaca".
        if ($tradeSignal->isClosed()) {
            Auth::user()
                ->signalPositions()
                ->where('trade_signal_id', $tradeSignal->id)
                ->whereNull('dismissed_at')
                ->update(['dismissed_at' => now()]);

            return back()->with('status', "Hasil {$tradeSignal->ticker} ditandai sudah dibaca. Riwayatnya tetap dihitung di performa kamu.");
        }

        Auth::user()
            ->signalPositions()
            ->where('trade_signal_id', $tradeSignal->id)
            ->delete();

        return back()->with('status', "Analisa {$tradeSignal->ticker} dihapus dari posisi/pantauan kamu.");
    }

    /**
     * Tandai satu hasil analisa sudah dibaca. Barisnya tidak dihapus (soft-dismiss)
     * supaya riwayatnya tetap dihitung di statistik performa posisi.
     */
    public function dismissSettled(TradeSignal $tradeSignal): RedirectResponse
    {
        if (! $tradeSignal->isClosed()) {
            return back()->withErrors([
                'position' => "Analisa {$tradeSignal->ticker} masih berjalan, belum ada hasil untuk ditandai.",
            ]);
        }

        Auth::user()
            ->signalPositions()
            ->where('trade_signal_id', $tradeSignal->id)
            ->whereNull('dismissed_at')
            ->update(['dismissed_at' => now()]);

        return back()->with('status', "Hasil {$tradeSignal->ticker} ditandai sudah dibaca. Riwayatnya tetap dihitung di performa kamu.");
    }

    public function clearSettled(): RedirectResponse
    {
        $count = Auth::user()
            ->signalPositions()
            ->whereNull('dismissed_at')
            ->whereHas('tradeSignal', fn ($query) => $query->whereIn('status', [
                TradeSignal::STATUS_HIT_TP,
                TradeSignal::STATUS_HIT_TP2,
                TradeSignal::STATUS_HIT_SL,
            ]))
            ->update(['dismissed_at' => now()]);

        if ($count === 0) {
            return back()->with('status', 'Tidak ada hasil analisa baru untuk ditandai.');
        }

        return back()->with('status', "{$count} hasil analisa ditandai sudah dibaca. Riwayatnya tetap dihitung di performa kamu.");
    }
}
