<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'transaction_reference',
        'receipt_url',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function markAsVerified(int $adminId): void
    {
        $this->update([
            'status' => 'successful',
            'verified_by' => $adminId,
            'verified_at' => now(),
        ]);

        // Auto extend or activate subscription
        if ($this->subscription) {
            $this->subscription->renew(30);
        } elseif ($this->business) {
            $sub = $this->business->subscription ?? $this->business->subscriptions()->latest()->first();
            if ($sub) {
                $sub->renew(30);
            } else {
                $this->business->subscriptions()->create([
                    'plan_name' => 'Monthly Professional',
                    'starts_at' => now(),
                    'expires_at' => now()->addDays(30),
                    'status' => 'active',
                    'monthly_fee' => $this->amount > 0 ? $this->amount : 5000.00,
                ]);
            }
        }
    }
}
