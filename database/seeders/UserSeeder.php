<?php

namespace Database\Seeders;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', '!=', 'admin@weesia.local')->delete();

        $now = Carbon::now();

        $admin = User::updateOrCreate(
            ['email' => 'admin@weesia.local'],
            [
                'name' => 'Weesia Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'coin_balance' => 0,
                'subscription_until' => null,
            ],
        );

        $traders = [
            ['name' => 'Budi Santoso',   'email' => 'budi.santoso@weesia.id',   'sub_days' => 30,   'coin_balance' => 0],
            ['name' => 'Andi Pratama',   'email' => 'andi.pratama@weesia.id',   'sub_days' => -5,   'coin_balance' => 50],
            ['name' => 'Sari Wulandari', 'email' => 'sari.wulandari@weesia.id', 'sub_days' => null, 'coin_balance' => 100],
            ['name' => 'Rina Marlina',   'email' => 'rina.marlina@weesia.id',   'sub_days' => 60,   'coin_balance' => 25],
            ['name' => 'Hendra Wijaya',  'email' => 'hendra.wijaya@weesia.id',  'sub_days' => null, 'coin_balance' => 0],
            ['name' => 'Lukman Hakim',   'email' => 'lukman.hakim@weesia.id',   'sub_days' => 14,   'coin_balance' => 10],
            ['name' => 'Nadia Permata',  'email' => 'nadia.permata@weesia.id',  'sub_days' => null, 'coin_balance' => 75],
            ['name' => 'Toni Setiawan',  'email' => 'toni.setiawan@weesia.id',  'sub_days' => -30,  'coin_balance' => 0],
            ['name' => 'Maya Sari',      'email' => 'maya.sari@weesia.id',      'sub_days' => 90,   'coin_balance' => 200],
        ];

        foreach ($traders as $index => $row) {
            $createdAt = $now->copy()->subDays(count($traders) - $index)->subHours(rand(0, 23));

            $user = new User([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make('password123'),
                'role' => 'user',
                'coin_balance' => $row['coin_balance'],
                'subscription_until' => $row['sub_days'] !== null ? $now->copy()->addDays($row['sub_days']) : null,
            ]);
            $user->created_at = $createdAt;
            $user->updated_at = $createdAt;
            $user->save();

            if ($row['coin_balance'] > 0) {
                $topup = new CoinTransaction([
                    'user_id' => $user->id,
                    'type' => CoinTransaction::TYPE_TOPUP,
                    'amount' => $row['coin_balance'],
                    'balance_after' => $row['coin_balance'],
                    'description' => 'Top-up awal saat user dibuat',
                    'created_by_id' => $admin->id,
                ]);
                $topup->created_at = $createdAt;
                $topup->updated_at = $createdAt;
                $topup->save();
            }

            if ($row['sub_days'] !== null) {
                $subscriptionUntil = $now->copy()->addDays($row['sub_days']);
                $tx = new CoinTransaction([
                    'user_id' => $user->id,
                    'type' => CoinTransaction::TYPE_SUBSCRIPTION,
                    'amount' => 0,
                    'balance_after' => $row['coin_balance'],
                    'description' => "Subscription sampai {$subscriptionUntil->format('d M Y')}",
                    'created_by_id' => $admin->id,
                ]);
                $tx->created_at = $createdAt->copy()->addMinutes(5);
                $tx->updated_at = $tx->created_at;
                $tx->save();
            }
        }
    }
}
