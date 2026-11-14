<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'ntn_cnic',
        'strn',
        'address',
        'province',
        'sector',
        'business_natures',
        'is_retailer_registered',
        'status',
        'rejection_reason',
        'sandbox_token',
        'production_token',
        'production_submissions_enabled',
        'current_environment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'business_natures' => 'array',
        'is_retailer_registered' => 'boolean',
        'production_submissions_enabled' => 'boolean',
        'sandbox_token' => 'encrypted',
        'production_token' => 'encrypted',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (empty($business->uuid)) {
                $business->uuid = (string) Str::uuid();
            }
        });
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withPivot('role', 'is_default')
            ->withTimestamps();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function hsCodes(): HasMany
    {
        return $this->hasMany(HsCode::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function fbrSubmissions(): HasMany
    {
        return $this->hasMany(FbrSubmission::class);
    }

    public function sandboxScenarios(): BelongsToMany
    {
        return $this->belongsToMany(SandboxScenario::class, 'business_sandbox_progress')
            ->withPivot('status', 'passed_at', 'last_fbr_response')
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}

