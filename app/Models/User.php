<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar_url',
        'role',
        'last_hit_seen_at',
        'coin_balance',
        'subscription_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_hit_seen_at' => 'datetime',
            'subscription_until' => 'datetime',
            'coin_balance' => 'integer',
            'password' => 'hashed',
        ];
    }

    public function tradeSignals()
    {
        return $this->hasMany(TradeSignal::class, 'created_by_id');
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function signalUnlocks(): HasMany
    {
        return $this->hasMany(SignalUnlock::class);
    }

    public function signalPositions(): HasMany
    {
        return $this->hasMany(UserSignalPosition::class);
    }

    public function selectedTradeSignals(): BelongsToMany
    {
        return $this->belongsToMany(TradeSignal::class, 'user_signal_positions')
            ->withPivot(['type', 'selected_at'])
            ->withTimestamps();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_until && $this->subscription_until->isFuture();
    }

    public function subscriptionStatusLabel(): string
    {
        if (! $this->subscription_until) {
            return 'Belum berlangganan';
        }

        return $this->hasActiveSubscription()
            ? 'Aktif sampai '.$this->subscription_until->format('d M Y')
            : 'Kadaluarsa '.$this->subscription_until->format('d M Y');
    }

    public function canAccessSignal(TradeSignal $signal): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->hasActiveSubscription()) {
            return true;
        }

        if ($signal->status !== TradeSignal::STATUS_ACTIVE) {
            return true;
        }

        return $this->signalUnlocks()
            ->where('trade_signal_id', $signal->id)
            ->exists();
    }
}
