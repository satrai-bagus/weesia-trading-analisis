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
        'entry_price',
        'selected_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_price' => 'float',
            'selected_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    /**
     * ROI pribadi (sudah dikali leverage) dari harga entry yang diinput user ke
     * harga penutupan analisa. Null selama analisa berjalan atau entry belum diisi.
     */
    public function personalRoi(): ?float
    {
        $signal = $this->tradeSignal;

        if (! $signal || ! $this->entry_price || $this->entry_price <= 0) {
            return null;
        }

        $close = $signal->closePrice();

        if (! $close) {
            return null;
        }

        return $signal->leveragedMoveTo($close, (float) $this->entry_price);
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
