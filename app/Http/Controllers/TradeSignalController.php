<?php

namespace App\Http\Controllers;

use App\Models\TradeSignal;
use App\Services\CryptoMarketData;
use App\Services\SignalChartImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TradeSignalController extends Controller
{
    public function store(Request $request, CryptoMarketData $marketData, SignalChartImage $chartImage): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'ticker' => ['required', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'position_side' => ['required', Rule::in([TradeSignal::SIDE_LONG, TradeSignal::SIDE_SHORT])],
            'signal_term' => ['required', Rule::in([TradeSignal::TERM_SHORT, TradeSignal::TERM_LONG])],
            'info_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'leverage' => ['required', 'integer', 'min:1', 'max:125'],
            'take_profit' => ['required', 'numeric', 'gt:0'],
            'has_take_profit_2' => ['nullable', 'boolean'],
            'take_profit_2' => ['nullable', 'required_if:has_take_profit_2,1', 'numeric', 'gt:0'],
            'stop_loss' => ['required', 'numeric', 'gt:0'],
        ]);

        $ticker = $marketData->normalizeTicker($validated['ticker']);
        $takeProfit2 = $request->boolean('has_take_profit_2')
            ? (float) $validated['take_profit_2']
            : null;
        $entryPrice = $marketData->priceForTicker($ticker)
            ?? $chartImage->fallbackEntry(
                (float) $validated['take_profit'],
                $takeProfit2,
                (float) $validated['stop_loss'],
                $validated['position_side'],
            );
        $path = $request->hasFile('image')
            ? $request->file('image')->store('trade-signals', 'public')
            : $chartImage->store(
                $ticker,
                $entryPrice,
                (float) $validated['take_profit'],
                $takeProfit2,
                (float) $validated['stop_loss'],
                $validated['position_side'],
                (int) $validated['leverage'],
            );

        TradeSignal::create([
            'ticker' => $ticker,
            'image_path' => $path,
            'entry_price' => $entryPrice,
            'take_profit' => $validated['take_profit'],
            'take_profit_2' => $takeProfit2,
            'stop_loss' => $validated['stop_loss'],
            'position_side' => $validated['position_side'],
            'signal_term' => $validated['signal_term'],
            'info_at' => $validated['info_at'] ?? null,
            'leverage' => $validated['leverage'],
            'created_by_id' => Auth::id(),
        ]);

        $message = $request->hasFile('image')
            ? 'Signal berhasil dipublish dengan chart upload.'
            : 'Signal berhasil dipublish dengan chart otomatis Weesia.';

        return back()->with('status', $message);
    }

    public function updateStatus(Request $request, TradeSignal $tradeSignal): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    TradeSignal::STATUS_ACTIVE,
                    TradeSignal::STATUS_HIT_TP,
                    TradeSignal::STATUS_HIT_TP2,
                    TradeSignal::STATUS_HIT_SL,
                ]),
            ],
        ]);

        $tradeSignal->forceFill([
            'status' => $validated['status'],
            'auto_hit_at' => null,
        ])->save();

        return back()->with('status', 'Status trading berhasil diperbarui.');
    }

    public function update(Request $request, TradeSignal $tradeSignal, CryptoMarketData $marketData): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'ticker' => ['required', 'string', 'max:30'],
            'position_side' => ['required', Rule::in([TradeSignal::SIDE_LONG, TradeSignal::SIDE_SHORT])],
            'signal_term' => ['required', Rule::in([TradeSignal::TERM_SHORT, TradeSignal::TERM_LONG])],
            'info_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'leverage' => ['required', 'integer', 'min:1', 'max:125'],
            'take_profit' => ['required', 'numeric', 'gt:0'],
            'take_profit_2' => ['nullable', 'numeric', 'gt:0'],
            'stop_loss' => ['required', 'numeric', 'gt:0'],
            'coin_cost' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => [
                'required',
                Rule::in([
                    TradeSignal::STATUS_ACTIVE,
                    TradeSignal::STATUS_HIT_TP,
                    TradeSignal::STATUS_HIT_TP2,
                    TradeSignal::STATUS_HIT_SL,
                ]),
            ],
        ]);

        $ticker = $marketData->normalizeTicker($validated['ticker']);
        $takeProfit2 = isset($validated['take_profit_2']) && $validated['take_profit_2'] !== null && $validated['take_profit_2'] !== ''
            ? (float) $validated['take_profit_2']
            : null;

        $tradeSignal->forceFill([
            'ticker' => $ticker,
            'position_side' => $validated['position_side'],
            'signal_term' => $validated['signal_term'],
            'info_at' => $request->has('info_at') ? ($validated['info_at'] ?? null) : $tradeSignal->info_at,
            'leverage' => (int) $validated['leverage'],
            'take_profit' => (float) $validated['take_profit'],
            'take_profit_2' => $takeProfit2,
            'stop_loss' => (float) $validated['stop_loss'],
            'coin_cost' => $validated['coin_cost'] !== null ? (int) $validated['coin_cost'] : $tradeSignal->coin_cost,
            'status' => $validated['status'],
            'auto_hit_at' => $validated['status'] === TradeSignal::STATUS_ACTIVE ? null : $tradeSignal->auto_hit_at,
        ])->save();

        return back()->with('status', 'Signal '.$ticker.' berhasil diupdate.');
    }

    public function destroy(TradeSignal $tradeSignal): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $ticker = $tradeSignal->ticker;
        $tradeSignal->delete();

        return back()->with('status', 'Signal '.$ticker.' berhasil dihapus.');
    }
}
