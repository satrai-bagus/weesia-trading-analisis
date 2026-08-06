<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_signal_positions', function (Blueprint $table) {
            // Soft-dismiss: "Tandai sudah dibaca" mengisi kolom ini alih-alih
            // menghapus baris, supaya riwayat hasil tetap bisa dihitung di
            // statistik performa posisi.
            $table->timestamp('dismissed_at')->nullable()->after('selected_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_signal_positions', function (Blueprint $table) {
            $table->dropColumn('dismissed_at');
        });
    }
};
