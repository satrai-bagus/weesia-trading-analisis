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
            $table->string('signal_term')
                ->default(TradeSignal::TERM_SHORT)
                ->after('position_side');
        });
    }

    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropColumn('signal_term');
        });
    }
};
