<?php

namespace Tests\Feature;

use App\Models\TradeSignal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa fake, harga BTC live asli membuat SignalAutoHit menutup signal
        // dummy (TP-nya cuma 110) sehingga daftar analisa aktif jadi kosong.
        Http::fake();
    }

    public function test_subscriber_sees_dashboard_with_unlocked_signal_cards(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Meja Trader')
            ->assertSee($signal->ticker)
            ->assertSee('Detail & P/L', false)
            ->assertSee('signal-detail-'.$signal->id, false)
            ->assertSee('data-signal-carousel', false)
            ->assertSee('data-live-signal-chart', false)
            ->assertSee('data-user-entry', false)
            ->assertSee('data-signal-tp1="110"', false)
            ->assertSee('data-signal-sl="95"', false)
            ->assertSee(route('signals.positions.store', $signal), false);
    }

    public function test_user_without_balance_sees_locked_card_with_topup(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'coin_balance' => 0,
            'subscription_until' => null,
        ]);
        $signal = $this->signal(['coin_cost' => 5]);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Analisa Terkunci')
            ->assertSee('Top Up')
            ->assertDontSee('signal-detail-'.$signal->id, false)
            // Nilai TP/SL tidak boleh bocor ke HTML untuk analisa terkunci.
            ->assertDontSee('data-signal-tp1', false)
            ->assertDontSee('data-chart-tp1', false)
            ->assertDontSee('data-unlock-trigger', false);
    }

    public function test_user_with_enough_balance_sees_unlock_button(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'coin_balance' => 10,
            'subscription_until' => null,
        ]);
        $signal = $this->signal(['coin_cost' => 5]);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Buka Analisa')
            ->assertSee('data-unlock-trigger', false)
            ->assertSee(route('signals.unlock', $signal), false)
            ->assertDontSee('data-signal-tp1', false);
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
