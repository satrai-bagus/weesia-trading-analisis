<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalUnlock extends Model
{
    protected $fillable = [
        'user_id',
        'trade_signal_id',
        'coin_cost',
        'unlocked_at',
    ];

    protected function casts(): array
    {
        return [
            'coin_cost' => 'integer',
            'unlocked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradeSignal(): BelongsTo
    {
        return $this->belongsTo(TradeSignal::class);
    }
}
