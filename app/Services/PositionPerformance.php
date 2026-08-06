<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSignalPosition;
use Illuminate\Support\Collection;

class PositionPerformance
{
    /**
     * Rekap performa posisi user: hanya tipe "Posisi" (Pantauan tidak dihitung)
     * yang analisanya sudah selesai - termasuk yang sudah ditandai dibaca,
     * karena soft-dismiss mempertahankan barisnya.
     *
     * ROI dihitung dari harga entry yang DIINPUT USER saat memasang posisi ke
     * harga penutupan analisa, dikali leverage - dengan asumsi ukuran tiap
     * posisi sama. Posisi tanpa harga entry tidak dihitung.
     */
    public function forUser(User $user): array
    {
        $closed = $user->signalPositions()
            ->where('type', UserSignalPosition::TYPE_POSITION)
            ->with('tradeSignal')
            ->get()
            ->filter(fn ($sel) => $sel->tradeSignal?->isClosed());

        $entries = $closed
            ->map(fn ($sel) => [
                'signal' => $sel->tradeSignal,
                'entry' => $sel->entry_price ? (float) $sel->entry_price : null,
                'roi' => $sel->personalRoi(),
                'settled_at' => $sel->tradeSignal->settledAt(),
            ]);

        // Posisi tanpa harga entry pribadi (atau analisanya tidak punya harga
        // penutupan) tidak bisa dihitung; dilaporkan terpisah supaya angka
        // utama tetap jujur.
        $uncounted = $entries->filter(fn ($entry) => $entry['roi'] === null)->count();
        $counted = $entries
            ->filter(fn ($entry) => $entry['roi'] !== null)
            ->sortBy(fn ($entry) => $entry['settled_at'])
            ->values();

        $running = 0.0;
        $series = $counted->map(function (array $entry) use (&$running) {
            $running += $entry['roi'];
            $signal = $entry['signal'];

            return [
                'ts' => $entry['settled_at']->getTimestampMs(),
                'label' => $entry['settled_at']->translatedFormat('d M Y'),
                'ticker' => $signal->ticker,
                'status' => $signal->statusLabel(),
                'tone' => $signal->statusTone(),
                'side' => $signal->sideLabel(),
                'leverage' => $signal->leverageValue(),
                'entry' => $entry['entry'],
                'roi' => round($entry['roi'], 2),
                'cum' => round($running, 2),
            ];
        })->values();

        $rois = $counted->pluck('roi');
        $wins = $rois->filter(fn ($roi) => $roi >= 0);
        $losses = $rois->filter(fn ($roi) => $roi < 0);
        $grossProfit = (float) $wins->sum();
        $grossLoss = abs((float) $losses->sum());

        return [
            'counted' => $counted->count(),
            'uncounted' => $uncounted,
            'wins' => $wins->count(),
            'losses' => $losses->count(),
            'winrate' => $counted->isNotEmpty() ? round($wins->count() / $counted->count() * 100, 1) : null,
            'total_roi' => round((float) $rois->sum(), 2),
            'avg_roi' => $counted->isNotEmpty() ? round((float) $rois->avg(), 2) : null,
            'profit_factor' => $grossLoss > 0 ? round($grossProfit / $grossLoss, 2) : null,
            'best' => $this->extreme($counted, descending: true),
            'worst' => $this->extreme($counted, descending: false),
            'series' => $series->all(),
        ];
    }

    private function extreme(Collection $counted, bool $descending): ?array
    {
        $entry = $descending
            ? $counted->sortByDesc('roi')->first()
            : $counted->sortBy('roi')->first();

        return $entry === null ? null : [
            'ticker' => $entry['signal']->ticker,
            'roi' => round($entry['roi'], 2),
        ];
    }
}
