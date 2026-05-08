<?php

namespace Tests\Feature;

use App\Models\TradeSignal;
use App\Models\User;
use App\Models\UserSignalPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSignalPositionTest extends TestCase
{
    use RefreshDatabase;

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
            ])
            ->assertRedirect('/user')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_POSITION,
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
