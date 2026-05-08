<?php

use App\Models\UserSignalPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_signal_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_signal_id')->constrained('trade_signals')->cascadeOnDelete();
            $table->string('type')->default(UserSignalPosition::TYPE_WATCHLIST)->index();
            $table->timestamp('selected_at');
            $table->timestamps();

            $table->unique(['user_id', 'trade_signal_id']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_signal_positions');
    }
};
