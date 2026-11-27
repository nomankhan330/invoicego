<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_code',
        'name',
        'email',
        'username',
        'phone',
        'cnic',
        'password',
        'is_super_admin',
        'admin_role',
        'status',
        'last_login_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'email_notifications_enabled' => 'boolean',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if (empty($user->user_code)) {
                $user->forceFill([
                    'user_code' => 'USR-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user')
            ->withPivot('role', 'is_default')
            ->withTimestamps();
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('is_super_admin', false)
            ->where(function (Builder $q) {
                $q->whereNull('admin_role')->orWhere('admin_role', 'user');
            });
    }

    public function scopeAdminStaff(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_super_admin', true)
              ->orWhereIn('admin_role', ['super_admin', 'admin', 'support_staff']);
        });
    }

    public function effectiveAdminRole(): string
    {
        if (!empty($this->admin_role) && $this->admin_role !== 'user') {
            return $this->admin_role;
        }

        return $this->is_super_admin ? 'super_admin' : 'user';
    }

    public function isSuperAdmin(): bool
    {
        return $this->effectiveAdminRole() === 'super_admin';
    }

    public function isAdminManager(): bool
    {
        return in_array($this->effectiveAdminRole(), ['super_admin', 'admin'], true);
    }

    public function isSupportStaff(): bool
    {
        return $this->effectiveAdminRole() === 'support_staff';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->effectiveAdminRole(), ['super_admin', 'admin', 'support_staff'], true);
    }
}
