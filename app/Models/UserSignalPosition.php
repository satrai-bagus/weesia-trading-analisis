<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSignalPosition extends Model
{
    public const TYPE_POSITION = 'position';

    public const TYPE_WATCHLIST = 'watchlist';

    protected $fillable = [
        'user_id',
        'trade_signal_id',
        'type',
        'selected_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_at' => 'datetime',
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

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_POSITION ? 'Posisi' : 'Pantauan';
    }

    public function typeTone(): string
    {
        return $this->type === self::TYPE_POSITION ? 'emerald' : 'gold';
    }
}
