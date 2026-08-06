<?php

namespace Database\Seeders;

use App\Models\TradeSignal;
use App\Models\User;
use App\Models\UserSignalPosition;
use App\Services\SignalChartImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh untuk kartu "Performa Posisi" di halaman Posisi Saya.
 *
 * Membuat riwayat ~100 hari posisi selesai milik user demo (budi.santoso@weesia.id):
 * fase awal campur, drawdown di tengah, lalu recovery - supaya kurva akumulasi,
 * winrate, dan profit factor terlihat hidup. Dua hasil terakhir sengaja belum
 * ditandai dibaca agar banner notifikasi ikut tampil, plus dua posisi aktif
 * untuk daftar berjalan.
 *
 * Jalankan: php artisan db:seed --class=PerformanceDemoSeeder
 * (Idempotent: signal demo lama - image_path trade-signals/perf-demo-* - dibersihkan dulu.)
 */
class PerformanceDemoSeeder extends Seeder
{
    private const IMAGE_PREFIX = 'trade-signals/perf-demo-';

    public function run(): void
    {
        $chartImage = app(SignalChartImage::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@weesia.local'],
            ['name' => 'Weesia Admin', 'password' => Hash::make('password123'), 'role' => 'admin'],
        );

        // updateOrCreate, bukan firstOrCreate: kredensial demo yang dijanjikan
        // (password123) harus berlaku juga saat akunnya sudah ada duluan dengan
        // password lama yang berbeda.
        $trader = User::updateOrCreate(
            ['email' => 'budi.santoso@weesia.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ],
        );
        if (! $trader->hasActiveSubscription()) {
            $trader->forceFill(['subscription_until' => now()->addDays(30)])->save();
        }

        // Bersihkan sisa demo sebelumnya supaya seeder bisa diulang tanpa duplikat.
        $oldIds = TradeSignal::where('image_path', 'like', self::IMAGE_PREFIX.'%')->pluck('id');
        if ($oldIds->isNotEmpty()) {
            DB::table('coin_transactions')->whereIn('trade_signal_id', $oldIds)->update(['trade_signal_id' => null]);
            TradeSignal::whereKey($oldIds)->delete(); // positions & unlocks ikut lewat cascade
        }

        // [hari lalu, ticker, side, leverage, hasil, move spot %] - ROI = move x leverage.
        // Hasil menang memakai HIT_TP2 (status terminal): HIT_TP masih bisa diubah
        // SignalAutoHit oleh harga live, yang akan merusak riwayat demo ini.
        $closed = [
            [98, 'BTC/USDT',  TradeSignal::SIDE_LONG,  50, TradeSignal::STATUS_HIT_TP2, 0.040],
            [92, 'ETH/USDT',  TradeSignal::SIDE_LONG,  25, TradeSignal::STATUS_HIT_SL,  0.022],
            [86, 'SOL/USDT',  TradeSignal::SIDE_SHORT, 50, TradeSignal::STATUS_HIT_TP2, 0.038],
            [80, 'ADA/USDT',  TradeSignal::SIDE_LONG,  25, TradeSignal::STATUS_HIT_SL,  0.024],
            [74, 'AVAX/USDT', TradeSignal::SIDE_SHORT, 75, TradeSignal::STATUS_HIT_SL,  0.018],
            [67, 'BNB/USDT',  TradeSignal::SIDE_LONG,  25, TradeSignal::STATUS_HIT_TP2, 0.070],
            [60, 'XRP/USDT',  TradeSignal::SIDE_LONG,  50, TradeSignal::STATUS_HIT_SL,  0.022],
            [55, 'DOGE/USDT', TradeSignal::SIDE_LONG,  50, TradeSignal::STATUS_HIT_SL,  0.026],
            [49, 'LINK/USDT', TradeSignal::SIDE_LONG,  50, TradeSignal::STATUS_HIT_TP2, 0.046],
            [42, 'OP/USDT',   TradeSignal::SIDE_SHORT, 25, TradeSignal::STATUS_HIT_TP2, 0.084],
            [35, 'INJ/USDT',  TradeSignal::SIDE_LONG,  75, TradeSignal::STATUS_HIT_SL,  0.020],
            [28, 'SUI/USDT',  TradeSignal::SIDE_SHORT, 25, TradeSignal::STATUS_HIT_TP2, 0.048],
            [21, 'NEAR/USDT', TradeSignal::SIDE_LONG,  50, TradeSignal::STATUS_HIT_TP2, 0.044],
            [14, 'ARB/USDT',  TradeSignal::SIDE_LONG,  75, TradeSignal::STATUS_HIT_TP2, 0.036],
            [8,  'DOT/USDT',  TradeSignal::SIDE_LONG,  25, TradeSignal::STATUS_HIT_SL,  0.020],
            [3,  'TIA/USDT',  TradeSignal::SIDE_SHORT, 50, TradeSignal::STATUS_HIT_TP2, 0.042],
            [1,  'MATIC/USDT', TradeSignal::SIDE_LONG, 75, TradeSignal::STATUS_HIT_SL,  0.016],
        ];

        $number = 0;
        foreach ($closed as [$daysAgo, $ticker, $side, $leverage, $status, $movePct]) {
            $signal = $this->makeSignal($chartImage, $admin, ++$number, $ticker, $side, $leverage, $status, $movePct, $daysAgo);

            // Dua hasil terbaru dibiarkan belum dibaca supaya banner notifikasi tampil.
            $dismissed = $daysAgo > 5 ? $signal->auto_hit_at->copy()->addHours(6) : null;
            // Entry pribadi sedikit meleset dari entry analisa (deterministik
            // per-nomor, -0.4%..+0.4%) - statistik dihitung dari harga ini.
            $personalEntry = round($signal->entry_price * (1 + ((($number % 5) - 2) * 0.002)), 8);
            $this->attachPosition($trader, $signal, UserSignalPosition::TYPE_POSITION, $dismissed, $personalEntry);
        }

        // Pantauan yang ikut selesai: muncul di banner, tapi TIDAK dihitung di performa.
        $watch = $this->makeSignal($chartImage, $admin, ++$number, 'ATOM/USDT', TradeSignal::SIDE_LONG, 50, TradeSignal::STATUS_HIT_TP2, 0.040, 12);
        $this->attachPosition($trader, $watch, UserSignalPosition::TYPE_WATCHLIST, $watch->auto_hit_at->copy()->addHours(3));

        // Dua posisi yang masih berjalan untuk daftar utama. Entry memakai harga
        // live (kalau API bisa dihubungi) supaya levelnya mengapit harga sekarang
        // dan tidak langsung ditutup SignalAutoHit di request berikutnya.
        $marketData = app(\App\Services\CryptoMarketData::class);
        foreach ([
            ['BTC/USDT', TradeSignal::SIDE_LONG, 75, 0.040],
            ['ETH/USDT', TradeSignal::SIDE_SHORT, 50, 0.050],
        ] as [$ticker, $side, $leverage, $movePct]) {
            $liveEntry = $marketData->priceForTicker($ticker);
            $signal = $this->makeSignal($chartImage, $admin, ++$number, $ticker, $side, $leverage, TradeSignal::STATUS_ACTIVE, $movePct, 0, $liveEntry);
            $this->attachPosition($trader, $signal, UserSignalPosition::TYPE_POSITION, null, (float) $signal->entry_price);
        }

        $this->command?->info("Demo performa terpasang untuk {$trader->email} (password123): ".count($closed).' posisi selesai, 1 pantauan selesai, 2 posisi berjalan.');
    }

    private function makeSignal(
        SignalChartImage $chartImage,
        User $admin,
        int $number,
        string $ticker,
        string $side,
        int $leverage,
        string $status,
        float $movePct,
        int $daysAgo,
        ?float $entryOverride = null,
    ): TradeSignal {
        $entry = $entryOverride ?: $this->entryFor($ticker);
        $isShort = $side === TradeSignal::SIDE_SHORT;
        $direction = $isShort ? -1 : 1;

        // Level disusun supaya harga penutupan menghasilkan ROI yang persis
        // movePct x leverage (lihat TradeSignal::resultRoi()).
        [$tp1, $tp2, $sl] = match ($status) {
            TradeSignal::STATUS_HIT_TP2 => [
                round($entry * (1 + $direction * $movePct / 2), 8),
                round($entry * (1 + $direction * $movePct), 8),
                round($entry * (1 - $direction * $movePct / 2), 8),
            ],
            TradeSignal::STATUS_HIT_SL => [
                round($entry * (1 + $direction * $movePct * 2), 8),
                null,
                round($entry * (1 - $direction * $movePct), 8),
            ],
            default => [ // hit TP1 & aktif
                round($entry * (1 + $direction * $movePct), 8),
                round($entry * (1 + $direction * $movePct * 2), 8),
                round($entry * (1 - $direction * $movePct / 2), 8),
            ],
        };

        $imagePath = self::IMAGE_PREFIX."{$number}.svg";
        Storage::disk('public')->put(
            $imagePath,
            $chartImage->svg($ticker, $entry, $tp1, $tp2, $sl, $side, $leverage),
        );

        $isClosed = $status !== TradeSignal::STATUS_ACTIVE;
        $hitAt = $isClosed ? now()->subDays($daysAgo)->setTime(9 + ($number % 9), ($number * 17) % 60) : null;
        $createdAt = $isClosed ? $hitAt->copy()->subDays(3) : now()->subHours(6 + $number);

        $signal = new TradeSignal([
            'ticker' => $ticker,
            'image_path' => $imagePath,
            'entry_price' => $entry,
            'take_profit' => $tp1,
            'take_profit_2' => $tp2,
            'stop_loss' => $sl,
            'position_side' => $side,
            'leverage' => $leverage,
            'coin_cost' => 1 + ($number % 4),
            'status' => $status,
            'auto_hit_at' => $hitAt,
            'created_by_id' => $admin->id,
        ]);
        $signal->created_at = $createdAt;
        $signal->updated_at = $hitAt ?? $createdAt;
        $signal->save();

        return $signal;
    }

    private function attachPosition(User $trader, TradeSignal $signal, string $type, $dismissedAt, ?float $entryPrice = null): void
    {
        UserSignalPosition::updateOrCreate(
            ['user_id' => $trader->id, 'trade_signal_id' => $signal->id],
            [
                'type' => $type,
                'entry_price' => $entryPrice,
                'selected_at' => $signal->created_at->copy()->addHours(2),
                'dismissed_at' => $dismissedAt,
            ],
        );
    }

    private function entryFor(string $ticker): float
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
            'OP/USDT' => 2.4,
            'INJ/USDT' => 28,
            'SUI/USDT' => 2.1,
            'NEAR/USDT' => 5.5,
            'ARB/USDT' => 1.1,
            'TIA/USDT' => 6.2,
            'MATIC/USDT' => 0.62,
            'ATOM/USDT' => 9.5,
            default => 100,
        };
    }
}
