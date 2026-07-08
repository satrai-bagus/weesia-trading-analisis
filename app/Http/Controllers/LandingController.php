<?php

namespace App\Http\Controllers;

use App\Models\TradeSignal;
use App\Services\CryptoMarketData;
use App\Services\SignalAutoHit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LandingController extends Controller
{
    private const TICKERS = [
        'BTC/USDT',
        'ETH/USDT',
        'SOL/USDT',
        'BNB/USDT',
        'XRP/USDT',
        'ADA/USDT',
        'DOGE/USDT',
        'AVAX/USDT',
        'LINK/USDT',
        'SEI/USDT',
    ];

    public function index(CryptoMarketData $marketData, SignalAutoHit $autoHit): View
    {
        $analysis = $this->featuredAnalysisPayload($marketData, $autoHit);

        return view('landing', [
            'landingTickers' => $this->fallbackTickers(),
            'featuredAnalysis' => $analysis,
            'recordStats' => $this->recordStats(),
        ]);
    }

    private function recordStats(): ?array
    {
        if (! Schema::hasTable('trade_signals')) {
            return null;
        }

        $total = TradeSignal::count();
        $hitTp = TradeSignal::whereIn('status', [
            TradeSignal::STATUS_HIT_TP,
            TradeSignal::STATUS_HIT_TP2,
        ])->count();
        $hitSl = TradeSignal::where('status', TradeSignal::STATUS_HIT_SL)->count();

        return [
            'total' => $total,
            'active' => TradeSignal::where('status', TradeSignal::STATUS_ACTIVE)->count(),
            'hitTp' => $hitTp,
            'hitSl' => $hitSl,
            'closed' => $hitTp + $hitSl,
        ];
    }

    public function marketTickers(Request $request, CryptoMarketData $marketData): JsonResponse
    {
        $tickers = collect(explode(',', (string) $request->query('tickers', '')))
            ->map(fn (string $ticker) => trim($ticker))
            ->filter()
            ->take(20)
            ->values();

        if ($tickers->isEmpty()) {
            $tickers = collect(self::TICKERS);
        }

        $summaries = $marketData->summariesForTickers($tickers);

        return response()->json([
            'tickers' => collect($tickers)
                ->map(fn (string $ticker) => $marketData->normalizeTicker($ticker))
                ->unique()
                ->map(function (string $ticker) use ($summaries) {
                    $summary = $summaries[$ticker] ?? null;

                    if (! $summary) {
                        return null;
                    }

                    return [
                        'symbol' => $ticker,
                        'price' => $this->formatPrice($summary['price']),
                        'change' => $this->formatChange($summary['change_24h'] ?? null),
                        'up' => ($summary['change_24h'] ?? 0) >= 0,
                    ];
                })
                ->filter()
                ->values()
                ->all(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function featuredSignal(CryptoMarketData $marketData, SignalAutoHit $autoHit): JsonResponse
    {
        return response()->json([
            'analysis' => $this->featuredAnalysisPayload($marketData, $autoHit),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function featuredAnalysisPayload(CryptoMarketData $marketData, SignalAutoHit $autoHit): ?array
    {
        $signal = $this->featuredSignalForToday();

        if (! $signal) {
            return null;
        }

        $priceMap = $marketData->pricesForTickers([$signal->ticker]);
        $autoHit->evaluate(collect([$signal]), $priceMap);
        $signal = TradeSignal::find($signal->id) ?: $signal;
        $live = $priceMap[$signal->ticker] ?? null;
        $entry = $signal->entry_price ?: $live ?: (($signal->take_profit + $signal->stop_loss) / 2);
        $isLive = $signal->status === TradeSignal::STATUS_ACTIVE;

        return [
            'id' => $signal->id,
            'symbol' => $signal->ticker,
            'title' => $this->analysisTitle($signal),
            'status' => $signal->status,
            'status_label' => $signal->statusLabel(),
            'status_meta' => $isLive ? 'Setup Aktif' : 'Arsip Riset',
            'is_live' => $isLive,
            'live_label' => $isLive ? 'Analisa Live' : 'Dari Arsip Riset',
            'side' => $signal->sideLabel(),
            'timeframe' => '4H',
            'entry' => $this->formatPrice((float) $entry),
            'live' => $live ? $this->formatPrice((float) $live) : null,
            'tp1' => $this->formatPrice((float) $signal->take_profit),
            'tp2' => $signal->take_profit_2 ? $this->formatPrice((float) $signal->take_profit_2) : null,
            'sl' => $this->formatPrice((float) $signal->stop_loss),
            'rr' => $this->riskRewardFor($signal, (float) $signal->entry_price ?: (float) $entry),
        ];
    }

    private function riskRewardFor(TradeSignal $signal, float $entry): ?string
    {
        $reward = abs((float) $signal->take_profit - $entry);
        $risk = abs($entry - (float) $signal->stop_loss);

        if ($entry <= 0 || $risk <= 0 || $reward <= 0) {
            return null;
        }

        $ratio = $reward / $risk;

        // A ratio that would render as "1 : 0" is worse than showing nothing.
        if ($ratio < 0.05) {
            return null;
        }

        return '1 : '.rtrim(rtrim(number_format($ratio, 1, '.', ','), '0'), '.');
    }

    private function featuredSignalForToday(): ?TradeSignal
    {
        if (! Schema::hasTable('trade_signals')) {
            return null;
        }

        $cacheKey = 'landing.featured-signal.'.now()->toDateString();
        $cachedId = Cache::get($cacheKey);

        if ($cachedId) {
            $cached = TradeSignal::find($cachedId);

            if ($cached && $cached->status !== TradeSignal::STATUS_HIT_SL) {
                return $cached;
            }
        }

        $signal = TradeSignal::query()
            ->whereIn('status', [TradeSignal::STATUS_HIT_TP2, TradeSignal::STATUS_HIT_TP])
            ->whereDate('auto_hit_at', now()->toDateString())
            ->orderByDesc('auto_hit_at')
            ->first()
            ?: TradeSignal::query()
                ->where('status', TradeSignal::STATUS_ACTIVE)
                ->whereDate('created_at', now()->toDateString())
                ->latest()
                ->first()
            ?: TradeSignal::query()
                ->where('status', TradeSignal::STATUS_ACTIVE)
                ->latest()
                ->first()
            ?: TradeSignal::query()
                ->whereIn('status', [TradeSignal::STATUS_HIT_TP2, TradeSignal::STATUS_HIT_TP])
                ->orderByDesc('auto_hit_at')
                ->latest()
                ->first()
            ?: TradeSignal::query()->latest()->first();

        if ($signal) {
            Cache::put($cacheKey, $signal->id, now()->endOfDay()->addMinute());
        }

        return $signal;
    }

    private function analysisTitle(TradeSignal $signal): string
    {
        if (in_array($signal->status, [TradeSignal::STATUS_HIT_TP, TradeSignal::STATUS_HIT_TP2], true)) {
            return 'TP Captured - 0.618';
        }

        return $signal->position_side === TradeSignal::SIDE_SHORT
            ? 'Bearish Retracement - 0.618'
            : 'Bullish Retracement - 0.618';
    }

    private function fallbackTickers(): array
    {
        return collect(self::TICKERS)
            ->map(fn (string $ticker) => [
                'symbol' => $ticker,
                'price' => '--',
                'change' => 'LIVE',
                'up' => true,
            ])
            ->all();
    }

    private function formatPrice(float $price): string
    {
        $decimals = match (true) {
            $price >= 1000 => 2,
            $price >= 1 => 4,
            default => 8,
        };

        return rtrim(rtrim(number_format($price, $decimals, '.', ','), '0'), '.');
    }

    private function formatChange(?float $change): string
    {
        if ($change === null) {
            return 'LIVE';
        }

        $prefix = $change > 0 ? '+' : '';

        return $prefix.rtrim(rtrim(number_format($change, 2, '.', ','), '0'), '.').'%';
    }

}
