<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LiveSession extends Model
{
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_LIVE = 'live';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';

    public const PROVIDER_ZOOM = 'zoom';
    public const PROVIDER_GMEET = 'gmeet';
    public const PROVIDER_OTHER = 'other';

    protected $fillable = [
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'provider',
        'meeting_link',
        'cover_image_path',
        'cancelled_at',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function scheduledAtLocal(): Carbon
    {
        return $this->scheduled_at->copy()->setTimezone('Asia/Jakarta');
    }

    public function status(): string
    {
        if ($this->cancelled_at) {
            return self::STATUS_CANCELLED;
        }

        $now = now();

        if ($now->lt($this->scheduled_at)) {
            return self::STATUS_UPCOMING;
        }

        if ($now->lte($this->endsAt())) {
            return self::STATUS_LIVE;
        }

        return self::STATUS_ENDED;
    }

    public function statusLabel(): string
    {
        return match ($this->status()) {
            self::STATUS_LIVE => 'Live Sekarang',
            self::STATUS_UPCOMING => 'Akan Datang',
            self::STATUS_ENDED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        };
    }

    public function statusTone(): string
    {
        return match ($this->status()) {
            self::STATUS_LIVE => 'rose',
            self::STATUS_UPCOMING => 'gold',
            self::STATUS_ENDED => 'ink',
            self::STATUS_CANCELLED => 'ink',
        };
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            self::PROVIDER_ZOOM => 'Zoom',
            self::PROVIDER_GMEET => 'Google Meet',
            default => 'Online',
        };
    }

    public function isJoinable(): bool
    {
        if ($this->cancelled_at) {
            return false;
        }

        // Joinable from 15 minutes before until end
        return now()->gte($this->scheduled_at->copy()->subMinutes(15))
            && now()->lte($this->endsAt());
    }

    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }
}
