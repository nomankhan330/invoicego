<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan_name',
        'starts_at',
        'expires_at',
        'status',
        'monthly_fee',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'monthly_fee' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        $now = Carbon::now();
        if ($this->expires_at && $this->expires_at->isFuture()) {
            return (int) $now->diffInDays($this->expires_at, false);
        }

        return 0;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function renew(int $days = 30): void
    {
        $base = ($this->expires_at && $this->expires_at->isFuture()) ? $this->expires_at : Carbon::now();
        $this->update([
            'expires_at' => $base->addDays($days),
            'status' => 'active',
        ]);
    }
}
