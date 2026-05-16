<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradeSignal extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_HIT_TP = 'hit_tp';

    public const STATUS_HIT_TP2 = 'hit_tp2';

    public const STATUS_HIT_SL = 'hit_sl';

    public const SIDE_LONG = 'long';

    public const SIDE_SHORT = 'short';

    public const TERM_SHORT = 'short_term';

    public const TERM_LONG = 'long_term';

    protected $fillable = [
        'ticker',
        'image_path',
        'entry_price',
        'take_profit',
        'take_profit_2',
        'stop_loss',
        'position_side',
        'signal_term',
        'leverage',
        'coin_cost',
        'status',
        'auto_hit_at',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'take_profit' => 'float',
            'take_profit_2' => 'float',
            'stop_loss' => 'float',
            'entry_price' => 'float',
            'leverage' => 'integer',
            'coin_cost' => 'integer',
            'auto_hit_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(SignalUnlock::class);
    }

    public function userPositions(): HasMany
    {
        return $this->hasMany(UserSignalPosition::class);
    }

    public function coinCostValue(): int
    {
        return $this->coin_cost ?: 1;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_HIT_TP => 'Kena TP1',
            self::STATUS_HIT_TP2 => 'Kena TP2',
            self::STATUS_HIT_SL => 'Kena SL',
            default => 'Aktif',
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_HIT_TP, self::STATUS_HIT_TP2 => 'emerald',
            self::STATUS_HIT_SL => 'rose',
            default => 'gold',
        };
    }

    public function sideLabel(): string
    {
        return $this->position_side === self::SIDE_SHORT ? 'Short' : 'Long';
    }

    public function termLabel(): string
    {
        return $this->signal_term === self::TERM_LONG ? 'Long Term' : 'Short Term';
    }

    public function leverageValue(): int
    {
        return $this->leverage ?: 75;
    }

    public function leveragedMoveTo(float $target, ?float $referencePrice = null): ?float
    {
        $entry = $referencePrice ?: $this->entry_price;

        if (! $entry || $entry <= 0 || $target <= 0) {
            return null;
        }

        $move = $this->position_side === self::SIDE_SHORT
            ? (($entry - $target) / $entry) * 100
            : (($target - $entry) / $entry) * 100;

        return $move * $this->leverageValue();
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_HIT_TP,
            self::STATUS_HIT_TP2,
            self::STATUS_HIT_SL,
        ], true);
    }

    public function closePrice(): ?float
    {
        return match ($this->status) {
            self::STATUS_HIT_TP => $this->take_profit ? (float) $this->take_profit : null,
            self::STATUS_HIT_TP2 => $this->take_profit_2 ? (float) $this->take_profit_2 : null,
            self::STATUS_HIT_SL => $this->stop_loss ? (float) $this->stop_loss : null,
            default => null,
        };
    }
}
