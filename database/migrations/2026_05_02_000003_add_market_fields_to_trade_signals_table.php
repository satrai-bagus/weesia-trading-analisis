<?php

use App\Models\TradeSignal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->decimal('entry_price', 20, 8)->nullable()->after('image_path');
            $table->string('position_side')->default(TradeSignal::SIDE_LONG)->after('stop_loss');
            $table->unsignedSmallInteger('leverage')->default(75)->after('position_side');
        });
    }

    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropColumn(['entry_price', 'position_side', 'leverage']);
        });
    }
};
