<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\TradeSignal;
use App\Services\CryptoMarketData;
use App\Services\SignalAutoHit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(CryptoMarketData $marketData, SignalAutoHit $autoHit): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        return view('dashboard.admin', $this->adminSignalPayload($marketData, $autoHit));
    }

    public function adminSignals(CryptoMarketData $marketData, SignalAutoHit $autoHit): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        return view('dashboard.admin-signals', $this->adminSignalPayload($marketData, $autoHit));
    }

    private function adminSignalPayload(CryptoMarketData $marketData, SignalAutoHit $autoHit): array
    {
        $signals = TradeSignal::with('createdBy')->latest()->get();
        $priceMap = $marketData->pricesForTickers($signals->pluck('ticker'));
        $autoHit->evaluate($signals, $priceMap);
        $signals = TradeSignal::with('createdBy')
            ->withCount(['unlocks', 'userPositions'])
            ->latest()
            ->get();

        return [
            'signals' => $signals,
            'stats' => $this->stats(),
            'marketTickers' => $marketData->usdtTickers(),
            'priceMap' => $priceMap,
        ];
    }

    public function user(CryptoMarketData $marketData, SignalAutoHit $autoHit): View
    {
        $allSignals = TradeSignal::with('createdBy')->latest()->get();
        $priceMap = $marketData->pricesForTickers($allSignals->pluck('ticker'));
        $autoHit->evaluate($allSignals, $priceMap);

        $signals = TradeSignal::with('createdBy')
            ->where('status', TradeSignal::STATUS_ACTIVE)
            ->get()
            ->sortByDesc(function (TradeSignal $signal) use ($priceMap) {
                $live = $priceMap[$signal->ticker] ?? null;
                $roi = $signal->leveragedMoveTo((float) $signal->take_profit, $live ? (float) $live : null);

                return $roi ?? -INF;
            })
            ->values();

        $user = Auth::user();
        $unlockedIds = $user->signalUnlocks()
            ->pluck('trade_signal_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $hasSubscription = $user->hasActiveSubscription();
        $accessMap = $signals->mapWithKeys(fn ($signal) => [
            $signal->id => $hasSubscription || in_array($signal->id, $unlockedIds, true),
        ])->all();
        $signalSelections = $user->signalPositions()
            ->with('tradeSignal.createdBy')
            ->latest('selected_at')
            ->get();

        $archiveCount = TradeSignal::whereIn('status', [
            TradeSignal::STATUS_HIT_TP,
            TradeSignal::STATUS_HIT_TP2,
            TradeSignal::STATUS_HIT_SL,
        ])->count();

        return view('dashboard.user', [
            'signals' => $signals,
            'stats' => $this->stats(),
            'priceMap' => $priceMap,
            'accessMap' => $accessMap,
            'signalSelections' => $signalSelections,
            'selectionMap' => $signalSelections->keyBy('trade_signal_id'),
            'hasSubscription' => $hasSubscription,
            'coinBalance' => (int) $user->coin_balance,
            'subscriptionUntil' => $user->subscription_until,
            'liveSession' => $this->upcomingLiveSession(),
            'archiveCount' => $archiveCount,
        ]);
    }

    public function archive(CryptoMarketData $marketData, SignalAutoHit $autoHit): View
    {
        $allSignals = TradeSignal::with('createdBy')->latest()->get();
        $priceMap = $marketData->pricesForTickers($allSignals->pluck('ticker'));
        $autoHit->evaluate($allSignals, $priceMap);

        $signals = TradeSignal::with('createdBy')
            ->whereIn('status', [
                TradeSignal::STATUS_HIT_TP,
                TradeSignal::STATUS_HIT_TP2,
                TradeSignal::STATUS_HIT_SL,
            ])
            ->orderByDesc('auto_hit_at')
            ->orderByDesc('updated_at')
            ->get();

        $tickerFilters = $signals->pluck('ticker')->unique()->values();
        $tpCount = $signals->whereIn('status', [TradeSignal::STATUS_HIT_TP, TradeSignal::STATUS_HIT_TP2])->count();
        $slCount = $signals->where('status', TradeSignal::STATUS_HIT_SL)->count();

        return view('dashboard.archive', [
            'signals' => $signals,
            'priceMap' => $priceMap,
            'tickerFilters' => $tickerFilters,
            'tpCount' => $tpCount,
            'slCount' => $slCount,
        ]);
    }

    public function upcomingLiveSession(): ?LiveSession
    {
        return LiveSession::active()
            ->where('scheduled_at', '<=', now()->addDays(7))
            ->orderBy('scheduled_at')
            ->get()
            ->first(fn (LiveSession $session) => $session->endsAt()->gte(now()));
    }

    public function live(): View
    {
        $upcoming = LiveSession::active()
            ->with('createdBy')
            ->where('scheduled_at', '<=', now()->addDays(7))
            ->orderBy('scheduled_at')
            ->get()
            ->filter(fn (LiveSession $session) => $session->endsAt()->gte(now()))
            ->values();

        $past = LiveSession::active()
            ->with('createdBy')
            ->where('scheduled_at', '<', now())
            ->orderByDesc('scheduled_at')
            ->get()
            ->filter(fn (LiveSession $session) => $session->endsAt()->lt(now()))
            ->take(10)
            ->values();

        $featured = $upcoming->first();

        return view('dashboard.live', [
            'featured' => $featured,
            'upcoming' => $upcoming,
            'past' => $past,
        ]);
    }

    public function positions(CryptoMarketData $marketData, SignalAutoHit $autoHit): View
    {
        $user = Auth::user();

        $selectionsRaw = $user->signalPositions()
            ->with('tradeSignal.createdBy')
            ->latest('selected_at')
            ->get();

        $signals = $selectionsRaw->pluck('tradeSignal')->filter()->values();
        $priceMap = $marketData->pricesForTickers($signals->pluck('ticker'));
        $autoHit->evaluate($signals, $priceMap);

        $signalSelections = $user->signalPositions()
            ->with('tradeSignal.createdBy')
            ->latest('selected_at')
            ->get()
            ->filter(fn ($sel) => $sel->tradeSignal !== null)
            ->values();

        $hasSubscription = $user->hasActiveSubscription();
        $unlockedIds = $user->signalUnlocks()
            ->pluck('trade_signal_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $accessMap = $signalSelections->mapWithKeys(fn ($sel) => [
            (int) $sel->trade_signal_id => $sel->tradeSignal->status !== TradeSignal::STATUS_ACTIVE
                || $hasSubscription
                || in_array((int) $sel->trade_signal_id, $unlockedIds, true),
        ])->all();

        return view('dashboard.positions', [
            'signalSelections' => $signalSelections,
            'priceMap' => $priceMap,
            'accessMap' => $accessMap,
            'hasSubscription' => $hasSubscription,
            'coinBalance' => (int) $user->coin_balance,
            'subscriptionUntil' => $user->subscription_until,
        ]);
    }

    public function marketPrices(Request $request, CryptoMarketData $marketData, SignalAutoHit $autoHit): JsonResponse
    {
        $tickers = collect(explode(',', (string) $request->query('tickers', '')))
            ->map(fn (string $ticker) => trim($ticker))
            ->filter()
            ->unique()
            ->take(40)
            ->values();

        $prices = $marketData->pricesForTickers($tickers);
        $autoHit->evaluateAll($prices);

        return response()->json([
            'prices' => $prices,
            'updated_at' => now()->toIso8601String(),
            'alerts' => $this->alertsPayload(),
        ]);
    }

    public function alertsPayload(): array
    {
        $user = Auth::user();
        $since = $user?->last_hit_seen_at;

        $query = TradeSignal::whereNotNull('auto_hit_at')
            ->whereIn('status', [
                TradeSignal::STATUS_HIT_TP,
                TradeSignal::STATUS_HIT_TP2,
                TradeSignal::STATUS_HIT_SL,
            ]);

        if ($since) {
            $query->where('auto_hit_at', '>', $since);
        }

        $unread = $query->orderByDesc('auto_hit_at')->limit(10)->get();

        $recent = TradeSignal::whereNotNull('auto_hit_at')
            ->orderByDesc('auto_hit_at')
            ->limit(10)
            ->get();

        return [
            'unread_count' => $unread->count(),
            'items' => $recent->map(fn (TradeSignal $signal) => [
                'id' => $signal->id,
                'ticker' => $signal->ticker,
                'status' => $signal->status,
                'status_label' => $signal->statusLabel(),
                'tone' => $signal->statusTone(),
                'side' => $signal->sideLabel(),
                'leverage' => $signal->leverageValue(),
                'auto_hit_at' => optional($signal->auto_hit_at)->toIso8601String(),
                'auto_hit_human' => optional($signal->auto_hit_at)->diffForHumans(),
                'is_unread' => $since ? $signal->auto_hit_at && $signal->auto_hit_at->gt($since) : true,
            ])->all(),
        ];
    }

    private function stats(): array
    {
        $total = TradeSignal::count();
        $active = TradeSignal::where('status', TradeSignal::STATUS_ACTIVE)->count();
        $hitTp = TradeSignal::whereIn('status', [
            TradeSignal::STATUS_HIT_TP,
            TradeSignal::STATUS_HIT_TP2,
        ])->count();
        $hitSl = TradeSignal::where('status', TradeSignal::STATUS_HIT_SL)->count();
        $closed = $hitTp + $hitSl;

        return [
            'total' => $total,
            'active' => $active,
            'hitTp' => $hitTp,
            'hitSl' => $hitSl,
            'winrate' => $closed > 0 ? round(($hitTp / $closed) * 100) : 0,
        ];
    }
}
