<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('trade_signals', 'info_at')) {
            return;
        }

        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dateTime('info_at')->nullable()->after('signal_term');
        });

        if (Schema::hasColumn('trade_signals', 'info_time')) {
            DB::table('trade_signals')
                ->whereNotNull('info_time')
                ->orderBy('id')
                ->select(['id', 'created_at', 'info_time'])
                ->chunk(100, function ($signals) {
                    foreach ($signals as $signal) {
                        $date = substr((string) ($signal->created_at ?: now()->toDateString()), 0, 10);
                        $time = substr((string) $signal->info_time, 0, 8);

                        DB::table('trade_signals')
                            ->where('id', $signal->id)
                            ->update(['info_at' => trim($date.' '.$time)]);
                    }
                });

            Schema::table('trade_signals', function (Blueprint $table) {
                $table->dropColumn('info_time');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('trade_signals', 'info_at')) {
            return;
        }

        if (! Schema::hasColumn('trade_signals', 'info_time')) {
            Schema::table('trade_signals', function (Blueprint $table) {
                $table->time('info_time')->nullable()->after('signal_term');
            });
        }

        DB::table('trade_signals')
            ->whereNotNull('info_at')
            ->orderBy('id')
            ->select(['id', 'info_at'])
            ->chunk(100, function ($signals) {
                foreach ($signals as $signal) {
                    DB::table('trade_signals')
                        ->where('id', $signal->id)
                        ->update(['info_time' => substr((string) $signal->info_at, 11, 8)]);
                }
            });

        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropColumn('info_at');
        });
    }
};
