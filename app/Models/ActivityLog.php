<?php

namespace App\Models;

use App\Services\Contracts\TenantServiceInterface;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'user_id',
        'action',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a system or user activity log safely
     */
    public static function record(string $action, string $description, ?array $properties = null, ?int $businessId = null, ?int $userId = null): ?self
    {
        try {
            $user = auth()->user();
            $resolvedUserId = $userId ?? $user?->id;

            $resolvedBusinessId = $businessId;
            if (!$resolvedBusinessId) {
                try {
                    $tenantService = app(TenantServiceInterface::class);
                    $resolvedBusinessId = $tenantService->getActiveBusinessId();
                } catch (\Throwable) {
                    $resolvedBusinessId = null;
                }
            }

            $ip = null;
            try {
                $ip = request()->ip();
            } catch (\Throwable) {
                $ip = '127.0.0.1';
            }

            return static::create([
                'business_id' => $resolvedBusinessId,
                'user_id' => $resolvedUserId,
                'action' => $action,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => $ip,
            ]);
        } catch (\Throwable $e) {
            // Fail silently to avoid interrupting core user actions
            report($e);
            return null;
        }
    }
}
