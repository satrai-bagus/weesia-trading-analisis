<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->timestamp('auto_hit_at')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_hit_seen_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropColumn('auto_hit_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_hit_seen_at');
        });
    }
};
