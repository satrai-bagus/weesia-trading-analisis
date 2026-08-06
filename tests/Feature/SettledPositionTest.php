<?php

namespace Tests\Feature;

use App\Models\TradeSignal;
use App\Models\User;
use App\Models\UserSignalPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SettledPositionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa fake, harga live asli membuat SignalAutoHit menutup signal dummy
        // sehingga posisi "berjalan" ikut pindah ke daftar selesai.
        Http::fake();
    }

    public function test_settled_position_moves_to_banner_with_result_roi(): void
    {
        $user = $this->subscriber();
        // Long 100 -> TP1 110 = +10% spot, dikali leverage 50 jadi +500%.
        $hit = $this->signal([
            'ticker' => 'ETH/USDT',
            'status' => TradeSignal::STATUS_HIT_TP,
            'auto_hit_at' => now()->subHour(),
        ]);
        $this->position($user, $hit);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('1 posisi kamu sudah menyentuh TP atau SL.')
            ->assertSee('+500%')
            ->assertSee('data-settled-item', false)
            ->assertSee(route('positions.settled.dismiss', $hit), false)
            // Posisi selesai tidak ikut jadi kartu di daftar utama. Ditandai lewat
            // data-signal-row karena data-position-item juga muncul di script filter.
            ->assertDontSee('data-signal-row', false);
    }

    public function test_stop_loss_result_is_shown_as_a_loss(): void
    {
        $user = $this->subscriber();
        // Long 100 -> SL 95 = -5% spot, dikali leverage 50 jadi -250%.
        $hit = $this->signal([
            'status' => TradeSignal::STATUS_HIT_SL,
            'auto_hit_at' => now()->subHour(),
        ]);
        $this->position($user, $hit);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('-250%')
            ->assertSee('Hasil posisi kamu - rugi');
    }

    public function test_running_position_stays_in_the_main_list(): void
    {
        $user = $this->subscriber();
        $running = $this->signal(['ticker' => 'SOL/USDT']);
        $this->position($user, $running);

        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertSee('data-signal-row', false)
            ->assertDontSee('data-settled-item', false)
            ->assertDontSee('sudah menyentuh TP atau SL');
    }

    public function test_marking_a_result_as_read_hides_it_but_keeps_history(): void
    {
        $user = $this->subscriber();
        $hit = $this->signal(['ticker' => 'ETH/USDT', 'status' => TradeSignal::STATUS_HIT_TP]);
        $running = $this->signal(['ticker' => 'SOL/USDT']);
        $this->position($user, $hit);
        $this->position($user, $running);

        $this->actingAs($user)
            ->from(route('user.positions'))
            ->delete(route('positions.settled.dismiss', $hit))
            ->assertRedirect(route('user.positions'))
            ->assertSessionHas('status');

        // Soft-dismiss: barisnya tetap ada, hanya ditandai dibaca.
        $this->assertNotNull($user->signalPositions()->where('trade_signal_id', $hit->id)->value('dismissed_at'));
        $this->assertNull($user->signalPositions()->where('trade_signal_id', $running->id)->value('dismissed_at'));

        // Banner-nya hilang, tapi hasilnya tetap dihitung di kartu performa.
        $this->actingAs($user)
            ->get(route('user.positions'))
            ->assertOk()
            ->assertDontSee('data-settled-item', false)
            ->assertSee('data-position-performance', false)
            ->assertSee('+500%');
    }

    public function test_running_position_cannot_be_marked_as_read(): void
    {
        $user = $this->subscriber();
        $running = $this->signal();
        $this->position($user, $running);

        $this->actingAs($user)
            ->from(route('user.positions'))
            ->delete(route('positions.settled.dismiss', $running))
            ->assertSessionHasErrors('position');

        $this->assertDatabaseHas('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $running->id,
        ]);
    }

    public function test_clear_all_marks_every_settled_position_read_only(): void
    {
        $user = $this->subscriber();
        $tp = $this->signal(['ticker' => 'ETH/USDT', 'status' => TradeSignal::STATUS_HIT_TP]);
        $sl = $this->signal(['ticker' => 'APT/USDT', 'status' => TradeSignal::STATUS_HIT_SL]);
        $running = $this->signal(['ticker' => 'SOL/USDT']);
        $this->position($user, $tp);
        $this->position($user, $sl);
        $this->position($user, $running);

        $this->actingAs($user)
            ->from(route('user.positions'))
            ->delete(route('positions.settled.clear'))
            ->assertRedirect(route('user.positions'))
            ->assertSessionHas('status');

        // Tidak ada baris yang dihapus; hanya yang selesai yang ditandai dibaca.
        $this->assertSame(3, UserSignalPosition::where('user_id', $user->id)->count());
        $this->assertSame(2, $user->signalPositions()->whereNotNull('dismissed_at')->count());
        $this->assertNull($user->signalPositions()->where('trade_signal_id', $running->id)->value('dismissed_at'));
    }

    public function test_a_user_cannot_clear_another_users_positions(): void
    {
        $owner = $this->subscriber();
        $intruder = $this->subscriber();
        $hit = $this->signal(['status' => TradeSignal::STATUS_HIT_TP]);
        $this->position($owner, $hit);

        $this->actingAs($intruder)
            ->from(route('user.positions'))
            ->delete(route('positions.settled.clear'));

        $this->assertNull($owner->signalPositions()->where('trade_signal_id', $hit->id)->value('dismissed_at'));
    }

    public function test_closed_signal_cannot_be_added_or_retyped_on_the_desk(): void
    {
        $user = $this->subscriber();
        $closed = $this->signal(['status' => TradeSignal::STATUS_HIT_TP2, 'auto_hit_at' => now()->subDay()]);

        // Menambah "kemenangan" lama dari arsip -> ditolak.
        $this->actingAs($user)
            ->from(route('user.positions'))
            ->post(route('signals.positions.store', $closed), ['type' => UserSignalPosition::TYPE_POSITION])
            ->assertSessionHasErrors('position');
        $this->assertDatabaseMissing('user_signal_positions', [
            'user_id' => $user->id,
            'trade_signal_id' => $closed->id,
        ]);

        // Membalik posisi kalah jadi pantauan supaya hilang dari statistik -> ditolak.
        $lost = $this->signal(['ticker' => 'APT/USDT', 'status' => TradeSignal::STATUS_HIT_SL, 'auto_hit_at' => now()->subDay()]);
        $this->position($user, $lost);

        $this->actingAs($user)
            ->from(route('user.positions'))
            ->post(route('signals.positions.store', $lost), ['type' => UserSignalPosition::TYPE_WATCHLIST])
            ->assertSessionHasErrors('position');
        $this->assertSame(
            UserSignalPosition::TYPE_POSITION,
            $user->signalPositions()->where('trade_signal_id', $lost->id)->value('type'),
        );
    }

    public function test_deleting_a_settled_position_soft_dismisses_instead(): void
    {
        $user = $this->subscriber();
        $lost = $this->signal(['status' => TradeSignal::STATUS_HIT_SL, 'auto_hit_at' => now()->subDay()]);
        $this->position($user, $lost);

        $this->actingAs($user)
            ->from(route('user.positions'))
            ->delete(route('signals.positions.destroy', $lost))
            ->assertSessionHas('status');

        // Kerugian tidak bisa dihapus dari riwayat performa.
        $this->assertNotNull($user->signalPositions()->where('trade_signal_id', $lost->id)->value('dismissed_at'));
    }

    public function test_changed_outcome_resurfaces_a_dismissed_result(): void
    {
        $user = $this->subscriber();
        // Kena TP1 (belum terminal), lalu user menandainya sudah dibaca.
        $signal = $this->signal(['status' => TradeSignal::STATUS_HIT_TP, 'auto_hit_at' => now()->subDay()]);
        $selection = $this->position($user, $signal);
        $selection->update(['dismissed_at' => now()->subHours(5)]);

        // Harga jatuh ke SL: hasil berubah, notifikasi harus muncul lagi.
        app(\App\Services\SignalAutoHit::class)->evaluate([$signal->fresh()], ['BTC/USDT' => 94.0]);

        $this->assertSame(TradeSignal::STATUS_HIT_SL, $signal->fresh()->status);
        $this->assertNull($user->signalPositions()->where('trade_signal_id', $signal->id)->value('dismissed_at'));
    }

    public function test_manual_close_by_admin_stamps_settled_timestamp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $signal = $this->signal();

        $this->actingAs($admin)
            ->from(route('admin.signals'))
            ->patch(route('signals.status', $signal), ['status' => TradeSignal::STATUS_HIT_SL])
            ->assertSessionHas('status');

        // Tanpa stempel ini settledAt() jatuh ke updated_at yang berubah tiap edit.
        $this->assertNotNull($signal->fresh()->auto_hit_at);
    }

    private function subscriber(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'subscription_until' => now()->addDay(),
        ]);
    }

    private function position(User $user, TradeSignal $signal): UserSignalPosition
    {
        return UserSignalPosition::create([
            'user_id' => $user->id,
            'trade_signal_id' => $signal->id,
            'type' => UserSignalPosition::TYPE_POSITION,
            // Sama dengan entry analisa di helper signal(), jadi ROI pribadi =
            // ROI analisa dan angka assert lama tetap berlaku.
            'entry_price' => 100,
            'selected_at' => now(),
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
