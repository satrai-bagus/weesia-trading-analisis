<?php

namespace Tests\Feature;

use App\Models\TradeSignal;
use App\Models\User;
use App\Models\UserSignalPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PositionPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa fake, harga live asli membuat SignalAutoHit mengubah status
        // signal dummy dan merusak angka yang di-assert.
        Http::fake();
    }

    public function test_performance_card_shows_winrate_and_accumulated_roi(): void
    {
        $user = $this->subscriber();
        // Dua menang (+500% masing-masing), satu kalah (-250%):
        // winrate 66.7%, akumulasi +750%. Semua sudah dibaca (dismissed) -
        // riwayat tetap dihitung berkat soft-dismiss.
        $this->position($user, $this->signal([
            'ticker' => 'ETH/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDays(9),
        ]), dismissed: true);
        $this->position($user, $this->signal([
            'ticker' => 'LINK/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDays(5),
        ]), dismissed: true);
        $this->position($user, $this->signal([
            'ticker' => 'BTC/USDT',
            'status' => TradeSignal::STATUS_HIT_SL,
            'auto_hit_at' => now()->subDays(2),
        ]), dismissed: true);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('data-position-performance', false)
            ->assertSee('untung +750% dari 3 posisi selesai')
            ->assertSee('66.7%')
            ->assertSee('2 menang')
            ->assertSee('Kurva Akumulasi ROI')
            ->assertSee('data-perf-data', false);
    }

    public function test_watchlist_results_are_not_counted(): void
    {
        $user = $this->subscriber();
        $this->position($user, $this->signal([
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDay(),
        ]), type: UserSignalPosition::TYPE_WATCHLIST);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            // Pantauan selesai tetap muncul di banner notifikasi...
            ->assertSee('data-settled-item', false)
            // ...dengan label hasil analisa, bukan "posisi kamu" (entry lamanya
            // masih tersimpan tapi barisnya sudah bukan posisi).
            ->assertDontSee('Hasil posisi kamu')
            // ...dan tidak menghasilkan kartu performa maupun kartu ajakan.
            ->assertDontSee('data-position-performance', false)
            ->assertDontSee('Performa belum bisa dihitung.');
    }

    public function test_no_performance_card_without_closed_positions(): void
    {
        $user = $this->subscriber();
        $this->position($user, $this->signal(['ticker' => 'SOL/USDT']));

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertDontSee('data-position-performance', false);
    }

    public function test_stats_use_the_users_own_entry_price(): void
    {
        $user = $this->subscriber();
        // Analisa: entry 100 -> TP 110 (long 50x) = +500%. Tapi user masuk di
        // 105, jadi ROI pribadinya (110-105)/105 x 50 = +238.1% - angka inilah
        // yang harus dipakai statistik, bukan +500%.
        $this->position($user, $this->signal([
            'ticker' => 'ETH/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDay(),
        ]), entry: 105.0);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('untung +238.1% dari 1 posisi selesai')
            ->assertSee('Hasil posisi kamu - profit')
            ->assertSee('Entry kamu $105')
            ->assertDontSee('untung +500%');
    }

    public function test_position_without_entry_price_is_not_counted(): void
    {
        $user = $this->subscriber();
        $this->position($user, $this->signal([
            'ticker' => 'ETH/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDay(),
        ]), entry: null);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            // Banner tetap memberi tahu hasil analisanya, dengan catatan jujur.
            ->assertSee('entry pribadi tidak diisi, tidak dihitung di performa')
            // Tidak ada kartu statistik; muncul ajakan mengisi entry.
            ->assertDontSee('data-position-performance', false)
            ->assertSee('Performa belum bisa dihitung.');
    }

    public function test_null_roi_result_is_neutral_and_reported_uncounted(): void
    {
        $user = $this->subscriber();
        // hit_tp2 tanpa nilai TP2 -> closePrice() null -> ROI tidak bisa dihitung.
        $this->position($user, $this->signal([
            'ticker' => 'APT/USDT',
            'take_profit_2' => null,
            'status' => TradeSignal::STATUS_HIT_TP2,
            'auto_hit_at' => now()->subDay(),
        ]));
        $this->position($user, $this->signal([
            'ticker' => 'ETH/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subDays(2),
        ]));

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            // ROI tak diketahui tidak boleh dilabeli "rugi" (temuan review).
            ->assertDontSee('Hasil analisa - rugi')
            ->assertSee('Harga penutupan tidak tersedia, ROI tidak bisa dihitung')
            ->assertSee('1 posisi selesai tidak ikut dihitung');
    }

    private function subscriber(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
    }

    private function position(
        User $user,
        TradeSignal $signal,
        string $type = UserSignalPosition::TYPE_POSITION,
        bool $dismissed = false,
        ?float $entry = 100.0,
    ): UserSignalPosition {
        return UserSignalPosition::create([
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => $type,
            // Sengaja juga terisi untuk watchlist: meniru posisi yang di-flip ke
            // pantauan (entry lamanya tetap tersimpan) - filter type di service
            // yang harus mengecualikannya, bukan ketiadaan entry.
            'entry_price' => $entry,
            'selected_at' => now()->subDays(10),
            'dismissed_at' => $dismissed ? now()->subHours(3) : null,
        ]);
    }

    private function signal(array $overrides = []): TradeSignal
    {
        return TradeSignal::create(array_merge([
            'ticker' => 'BTC/USDT',
            'image_path' => 'trade-signals/test.png',
            'entry_price' => 100,
            'take_profit' => 110,
            'take_profit_2' => 120,
            'stop_loss' => 95,
            'position_side' => TradeSignal::SIDE_LONG,
            'leverage' => 50,
            'coin_cost' => 5,
            'status' => TradeSignal::STATUS_ACTIVE,
            'created_by_id' => User::factory()->create(['role' => 'admin'])->id,
        ], $overrides));
    }
}
