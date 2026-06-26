<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->time('info_time')->nullable()->after('signal_term');
        });
    }

    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropColumn('info_time');
        });
    }
};
