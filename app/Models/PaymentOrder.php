<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrder extends Model
{
    public const TYPE_TOKEN = 'token';
    public const TYPE_SUBSCRIPTION = 'subscription';

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_REVIEW = 'awaiting_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'reference',
        'type',
        'package_key',
        'package_label',
        'token_amount',
        'subscription_months',
        'price',
        'unique_code',
        'total_amount',
        'proof_path',
        'whatsapp_number',
        'status',
        'admin_note',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'token_amount' => 'integer',
            'subscription_months' => 'integer',
            'price' => 'integer',
            'unique_code' => 'integer',
            'total_amount' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_AWAITING_REVIEW => 'Menunggu Verifikasi',
            self::STATUS_APPROVED => 'Berhasil',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'emerald',
            self::STATUS_REJECTED => 'rose',
            self::STATUS_AWAITING_REVIEW => 'gold',
            default => 'ink',
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED], true);
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'WSA-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }
}
