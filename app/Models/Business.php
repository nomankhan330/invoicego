<?php

namespace App\Models;

use Carbon\Carbon;
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
        'business_type',
        'ntn_cnic',
        'strn',
        'address',
        'city',
        'province',
        'sector',
        'business_natures',
        'is_retailer_registered',
        'status',
        'rejection_reason',
        'sandbox_token',
        'production_token',
        'fbr_sandbox_token',
        'fbr_production_token',
        'sandbox_url',
        'sandbox_validation_url',
        'production_url',
        'production_validation_url',
        'sandbox_verified_at',
        'production_verified_at',
        'sandbox_connection_status',
        'production_connection_status',
        'production_submissions_enabled',
        'invoice_limit',
        'current_environment',
        'fbr_pos_id',
        'fbr_registration_status',
        'fbr_integration_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'business_natures' => 'array',
        'is_retailer_registered' => 'boolean',
        'production_submissions_enabled' => 'boolean',
        'invoice_limit' => 'integer',
        'sandbox_token' => 'encrypted',
        'production_token' => 'encrypted',
        'sandbox_verified_at' => 'datetime',
        'production_verified_at' => 'datetime',
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

    /**
     * Effective monthly invoice creation cap for this business.
     * 0 = unlimited; positive integer = max non-cancelled invoices allowed per monthly cycle.
     */
    public function getEffectiveInvoiceLimit(): int
    {
        if ($this->invoice_limit !== null) {
            return max(0, (int) $this->invoice_limit);
        }

        return max(0, (int) SystemSetting::get('default_invoice_limit', 50));
    }

    /**
     * Calculate this business's active rolling 1-month quota cycle anchored to its approval/registration date.
     * Example: Approved on 27 Nov 2026 -> Cycle 1 is [27 Nov 2026, 27 Dec 2026); on 27 Dec 2026 it auto-renews to [27 Dec 2026, 27 Jan 2027).
     */
    public function getCurrentQuotaCycle(?Carbon $now = null): array
    {
        $tz = 'Asia/Karachi';
        $current = ($now ? $now->copy() : Carbon::now($tz))->timezone($tz);
        $anchorSource = $this->approved_at ?? $this->created_at ?? $current;
        $anchor = $anchorSource->copy()->timezone($tz)->startOfDay();

        if ($current->lt($anchor)) {
            $cycleStart = $anchor->copy();
            $cycleEnd = $anchor->copy()->addMonthsNoOverflow(1);
        } else {
            $monthsElapsed = (($current->year - $anchor->year) * 12) + ($current->month - $anchor->month);
            if ($monthsElapsed < 0) {
                $monthsElapsed = 0;
            }

            $cycleStart = $anchor->copy()->addMonthsNoOverflow($monthsElapsed);
            if ($cycleStart->gt($current)) {
                $monthsElapsed = max(0, $monthsElapsed - 1);
                $cycleStart = $anchor->copy()->addMonthsNoOverflow($monthsElapsed);
            }

            $cycleEnd = $anchor->copy()->addMonthsNoOverflow($monthsElapsed + 1);
        }

        return [
            'start' => $cycleStart,
            'end' => $cycleEnd,
            'start_utc' => $cycleStart->copy()->utc(),
            'end_utc' => $cycleEnd->copy()->utc(),
            'start_label' => $cycleStart->format('d M Y'),
            'end_label' => $cycleEnd->format('d M Y'),
            'renews_on' => $cycleEnd->format('d M Y'),
            'days_left' => max(0, (int) $current->copy()->startOfDay()->diffInDays($cycleEnd, false)),
        ];
    }

    /**
     * Count of non-cancelled invoices created within this business's current monthly cycle.
     */
    public function getInvoiceUsageCount(?Carbon $now = null): int
    {
        $cycle = $this->getCurrentQuotaCycle($now);

        return $this->invoices()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $cycle['start_utc'])
            ->where('created_at', '<', $cycle['end_utc'])
            ->count();
    }

    public function hasReachedInvoiceLimit(?Carbon $now = null): bool
    {
        $limit = $this->getEffectiveInvoiceLimit();

        return $limit > 0 && $this->getInvoiceUsageCount($now) >= $limit;
    }

    public function getRemainingInvoicesCount(?Carbon $now = null): ?int
    {
        $limit = $this->getEffectiveInvoiceLimit();

        if ($limit === 0) {
            return null;
        }

        return max(0, $limit - $this->getInvoiceUsageCount($now));
    }

    public function getInvoiceQuotaSummary(?Carbon $now = null): array
    {
        $cycle = $this->getCurrentQuotaCycle($now);
        $limit = $this->getEffectiveInvoiceLimit();
        $used = $this->getInvoiceUsageCount($now);
        $unlimited = $limit === 0;
        $remaining = $unlimited ? null : max(0, $limit - $used);
        $reached = !$unlimited && $used >= $limit;
        $percent = $unlimited ? 0 : ($limit > 0 ? min(100, (int) round(($used / $limit) * 100)) : 100);

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'unlimited' => $unlimited,
            'reached' => $reached,
            'percent' => $percent,
            'cycle_start' => $cycle['start_label'],
            'cycle_end' => $cycle['end_label'],
            'renews_on' => $cycle['renews_on'],
            'days_left' => $cycle['days_left'],
            'period_label' => "{$cycle['start_label']} – {$cycle['end_label']}",
        ];
    }

    public function getFbrSandboxTokenAttribute()
    {
        return $this->sandbox_token;
    }

    public function setFbrSandboxTokenAttribute($value)
    {
        $this->sandbox_token = $value;
    }

    public function getFbrProductionTokenAttribute()
    {
        return $this->production_token;
    }

    public function setFbrProductionTokenAttribute($value)
    {
        $this->production_token = $value;
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

