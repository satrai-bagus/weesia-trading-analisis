<?php

use App\Models\TradeSignal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_signals', function (Blueprint $table) {
            $table->id();
            $table->string('ticker');
            $table->string('image_path');
            $table->decimal('take_profit', 20, 8);
            $table->decimal('take_profit_2', 20, 8)->nullable();
            $table->decimal('stop_loss', 20, 8);
            $table->string('status')->default(TradeSignal::STATUS_ACTIVE)->index();
            $table->foreignId('created_by_id')->constrained('users');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_signals');
    }
};
