<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'qris_path',
        'qris_holder',
        'bank_accounts',
        'ewallet_accounts',
        'whatsapp_admin',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'bank_accounts' => 'array',
            'ewallet_accounts' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'bank_accounts' => [],
            'ewallet_accounts' => [],
        ]);
    }
}
