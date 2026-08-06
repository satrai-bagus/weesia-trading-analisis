<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_signal_positions', function (Blueprint $table) {
            // Harga entry pribadi user, diisi wajib saat memasang tipe "posisi".
            // Statistik performa dihitung dari nilai ini, bukan entry analisa.
            $table->decimal('entry_price', 20, 8)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('user_signal_positions', function (Blueprint $table) {
            $table->dropColumn('entry_price');
        });
    }
};
