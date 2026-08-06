<?php

namespace Tests\Feature;

use App\Models\SignalUnlock;
use App\Models\TradeSignal;
use App\Models\User;
use App\Models\UserSignalPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserSignalPositionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa fake, harga live asli membuat SignalAutoHit menutup signal dummy
        // (TP-nya cuma 110) sehingga posisinya pindah ke daftar hasil selesai.
        Http::fake();
    }

    public function test_user_can_save_accessible_signal_as_position(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();

        $this->actingAs($user)
            ->from('/user')
            ->post(route('signals.positions.store', $signal), [
                'type' => UserSignalPosition::TYPE_POSITION,
                'entry_price' => 101.5,
            ])
            ->assertRedirect('/user')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_POSITION,
            'entry_price' => 101.5,
        ]);
    }

    public function test_saving_position_without_entry_price_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();

        // Posisi wajib menyertakan harga entry - statistik dihitung darinya.
        $this->actingAs($user)
            ->from('/user')
            ->post(route('signals.positions.store', $signal), [
                'type' => UserSignalPosition::TYPE_POSITION,
            ])
            ->assertRedirect('/user')
            ->assertSessionHasErrors('entry_price');

        $this->assertDatabaseMissing('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
        ]);

        // Pantauan tidak butuh harga entry.
        $this->actingAs($user)
            ->from('/user')
            ->post(route('signals.positions.store', $signal), [
                'type' => UserSignalPosition::TYPE_WATCHLIST,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_entry_price_outside_column_range_is_rejected_not_500(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();

        // Melebihi decimal(20,8): tanpa rule between, MySQL strict melempar
        // out-of-range dan user melihat error 500.
        foreach (['1e300', '99999999999999', '1e-10'] as $bad) {
            $this->actingAs($user)
                ->from('/user')
                ->post(route('signals.positions.store', $signal), [
                    'type' => UserSignalPosition::TYPE_POSITION,
                    'entry_price' => $bad,
                ])
                ->assertRedirect('/user')
                ->assertSessionHasErrors('entry_price');
        }

        $this->assertDatabaseMissing('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
        ]);
    }

    public function test_user_cannot_save_locked_active_signal(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'coin_balance' => 0,
            'subscription_until' => null,
        ]);
        $signal = $this->signal();

        $this->actingAs($user)
            ->from('/user')
            ->post(route('signals.positions.store', $signal), [
                'type' => UserSignalPosition::TYPE_WATCHLIST,
            ])
            ->assertRedirect('/user')
            ->assertSessionHasErrors('position');

        $this->assertDatabaseMissing('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
        ]);
    }

    public function test_saving_same_signal_updates_existing_choice(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();

        $this->actingAs($user)->post(route('signals.positions.store', $signal), [
            'type' => UserSignalPosition::TYPE_POSITION,
            'entry_price' => 100,
        ]);
        $this->actingAs($user)->post(route('signals.positions.store', $signal), [
            'type' => UserSignalPosition::TYPE_WATCHLIST,
        ]);

        $this->assertSame(1, UserSignalPosition::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_WATCHLIST,
        ]);
    }

    public function test_unlocked_signal_stays_accessible_on_positions_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'coin_balance' => 4,
            'subscription_until' => null,
        ]);
        $signal = $this->signal([
            'ticker' => 'ETH/USDT',
            'coin_cost' => 1,
        ]);

        SignalUnlock::create([
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'coin_cost' => 1,
            'unlocked_at' => now(),
        ]);
        UserSignalPosition::create([
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_POSITION,
            'selected_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('Terbuka')
            ->assertDontSee('Buka Lagi');
    }

    public function test_user_can_remove_saved_signal_choice(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
        $signal = $this->signal();
        UserSignalPosition::create([
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_POSITION,
            'selected_at' => now(),
        ]);

        $this->actingAs($user)
            ->from('/user')
            ->delete(route('signals.positions.destroy', $signal))
            ->assertRedirect('/user')
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
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
