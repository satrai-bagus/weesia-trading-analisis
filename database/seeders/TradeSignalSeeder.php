<?php

namespace Database\Seeders;

use App\Models\TradeSignal;
use App\Models\User;
use App\Services\CryptoMarketData;
use App\Services\SignalChartImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TradeSignalSeeder extends Seeder
{
    public function run(): void
    {
        $marketData = app(CryptoMarketData::class);
        $chartImage = app(SignalChartImage::class);

        $admin = User::firstOrCreate([
            'email' => 'admin@weesia.local',
        ], [
            'name' => 'Weesia Admin',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        DB::table('signal_unlocks')->delete();
        DB::table('coin_transactions')->whereNotNull('trade_signal_id')->update(['trade_signal_id' => null]);
        TradeSignal::query()->delete();

        $variants = [
            ['ticker' => 'BTC/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.04,  'tp2_pct' => 0.08,  'sl_pct' => 0.018, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 5, 'leverage' => 75],
            ['ticker' => 'ETH/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.05,  'tp2_pct' => null,  'sl_pct' => 0.022, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 3, 'leverage' => 50],
            ['ticker' => 'SOL/USDT',   'side' => TradeSignal::SIDE_SHORT, 'tp1_pct' => 0.04,  'tp2_pct' => 0.075, 'sl_pct' => 0.02,  'status' => TradeSignal::STATUS_HIT_TP, 'coin_cost' => 4, 'leverage' => 75],
            ['ticker' => 'BNB/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.035, 'tp2_pct' => 0.07,  'sl_pct' => 0.018, 'status' => TradeSignal::STATUS_HIT_TP2,'coin_cost' => 2, 'leverage' => 25],
            ['ticker' => 'XRP/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.045, 'tp2_pct' => 0.09,  'sl_pct' => 0.022, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 2, 'leverage' => 50],
            ['ticker' => 'ADA/USDT',   'side' => TradeSignal::SIDE_SHORT, 'tp1_pct' => 0.05,  'tp2_pct' => null,  'sl_pct' => 0.025, 'status' => TradeSignal::STATUS_HIT_SL, 'coin_cost' => 1, 'leverage' => 25],
            ['ticker' => 'DOGE/USDT',  'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.06,  'tp2_pct' => 0.12,  'sl_pct' => 0.028, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 2, 'leverage' => 50],
            ['ticker' => 'AVAX/USDT',  'side' => TradeSignal::SIDE_SHORT, 'tp1_pct' => 0.045, 'tp2_pct' => 0.085, 'sl_pct' => 0.022, 'status' => TradeSignal::STATUS_HIT_TP, 'coin_cost' => 3, 'leverage' => 75],
            ['ticker' => 'LINK/USDT',  'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.05,  'tp2_pct' => 0.1,   'sl_pct' => 0.024, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 3, 'leverage' => 50],
            ['ticker' => 'DOT/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.04,  'tp2_pct' => null,  'sl_pct' => 0.02,  'status' => TradeSignal::STATUS_HIT_SL, 'coin_cost' => 1, 'leverage' => 25],
            ['ticker' => 'ARB/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.055, 'tp2_pct' => 0.11,  'sl_pct' => 0.026, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 4, 'leverage' => 75],
            ['ticker' => 'OP/USDT',    'side' => TradeSignal::SIDE_SHORT, 'tp1_pct' => 0.05,  'tp2_pct' => 0.09,  'sl_pct' => 0.024, 'status' => TradeSignal::STATUS_HIT_TP2,'coin_cost' => 2, 'leverage' => 50],
            ['ticker' => 'INJ/USDT',   'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.06,  'tp2_pct' => 0.13,  'sl_pct' => 0.03,  'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 3, 'leverage' => 75],
            ['ticker' => 'SUI/USDT',   'side' => TradeSignal::SIDE_SHORT, 'tp1_pct' => 0.05,  'tp2_pct' => null,  'sl_pct' => 0.025, 'status' => TradeSignal::STATUS_HIT_TP, 'coin_cost' => 2, 'leverage' => 25],
            ['ticker' => 'NEAR/USDT',  'side' => TradeSignal::SIDE_LONG,  'tp1_pct' => 0.045, 'tp2_pct' => 0.09,  'sl_pct' => 0.022, 'status' => TradeSignal::STATUS_ACTIVE,  'coin_cost' => 3, 'leverage' => 50],
        ];

        foreach ($variants as $index => $v) {
            $number = $index + 1;
            $ticker = $marketData->normalizeTicker($v['ticker']);
            $live = $marketData->priceForTicker($ticker);
            $entryPrice = $live ?: $this->fallbackEntry($ticker);

            $tp1 = round($v['side'] === TradeSignal::SIDE_SHORT ? $entryPrice * (1 - $v['tp1_pct']) : $entryPrice * (1 + $v['tp1_pct']), 8);
            $tp2 = $v['tp2_pct'] !== null
                ? round($v['side'] === TradeSignal::SIDE_SHORT ? $entryPrice * (1 - $v['tp2_pct']) : $entryPrice * (1 + $v['tp2_pct']), 8)
                : null;
            $sl  = round($v['side'] === TradeSignal::SIDE_SHORT ? $entryPrice * (1 + $v['sl_pct']) : $entryPrice * (1 - $v['sl_pct']), 8);

            $imagePath = "trade-signals/demo-signal-{$number}.svg";
            Storage::disk('public')->put(
                $imagePath,
                $chartImage->svg($ticker, $entryPrice, $tp1, $tp2, $sl, $v['side'], $v['leverage']),
            );

            $signal = new TradeSignal([
                'ticker' => $ticker,
                'image_path' => $imagePath,
                'entry_price' => $entryPrice,
                'take_profit' => $tp1,
                'take_profit_2' => $tp2,
                'stop_loss' => $sl,
                'position_side' => $v['side'],
                'leverage' => $v['leverage'],
                'coin_cost' => $v['coin_cost'],
                'status' => $v['status'],
                'created_by_id' => $admin->id,
            ]);

            $signal->created_at = now()->subHours(count($variants) - $index)->subMinutes(rand(0, 59));
            $signal->updated_at = $signal->created_at;
            $signal->save();
        }
    }

    private function fallbackEntry(string $ticker): float
    {
        return match ($ticker) {
            'BTC/USDT' => 78000,
            'ETH/USDT' => 3500,
            'SOL/USDT' => 165,
            'BNB/USDT' => 600,
            'XRP/USDT' => 0.55,
            'ADA/USDT' => 0.45,
            'DOGE/USDT' => 0.18,
            'AVAX/USDT' => 36,
            'LINK/USDT' => 18,
            'DOT/USDT' => 7.5,
            'ARB/USDT' => 1.1,
            'OP/USDT' => 2.4,
            'INJ/USDT' => 28,
            'SUI/USDT' => 2.1,
            'NEAR/USDT' => 5.5,
            default => 100,
        };
    }
}
