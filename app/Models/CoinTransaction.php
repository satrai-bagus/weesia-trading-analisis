<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    public const TYPE_TOPUP = 'topup';
    public const TYPE_SPEND = 'spend';
    public const TYPE_REFUND = 'refund';
    public const TYPE_SUBSCRIPTION = 'subscription';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'trade_signal_id',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_TOPUP => 'Top-up',
            self::TYPE_SPEND => 'Buka Analisa',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_SUBSCRIPTION => 'Subscription',
            default => ucfirst($this->type),
        };
    }
}
