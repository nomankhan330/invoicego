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
     * Resolve the visitor's real public IP across Vercel, Cloudflare, and reverse proxies.
     */
    public static function resolveClientIp(): string
    {
        try {
            $request = request();
            if (!$request) {
                return '127.0.0.1';
            }

            $headerCandidates = [
                $request->header('X-Vercel-Forwarded-For'),
                $request->header('CF-Connecting-IP'),
                $request->header('X-Real-IP'),
                $request->header('X-Forwarded-For'),
                $request->server('HTTP_X_VERCEL_FORWARDED_FOR'),
                $request->server('HTTP_CF_CONNECTING_IP'),
                $request->server('HTTP_X_REAL_IP'),
                $request->server('HTTP_X_FORWARDED_FOR'),
                $request->ip(),
            ];

            $firstValidIp = null;

            foreach ($headerCandidates as $raw) {
                if (!is_string($raw) || trim($raw) === '') {
                    continue;
                }

                foreach (explode(',', $raw) as $part) {
                    $candidate = trim($part);
                    if ($candidate === '') {
                        continue;
                    }

                    // Strip optional port on IPv4 (e.g. 203.0.113.10:54321)
                    if (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $candidate, $m)) {
                        $candidate = $m[1];
                    }

                    if (!filter_var($candidate, FILTER_VALIDATE_IP)) {
                        continue;
                    }

                    if ($firstValidIp === null) {
                        $firstValidIp = $candidate;
                    }

                    if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $candidate;
                    }
                }
            }

            return $firstValidIp ?? '127.0.0.1';
        } catch (\Throwable) {
            return '127.0.0.1';
        }
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

            $ip = static::resolveClientIp();

            $mergedProperties = is_array($properties) ? $properties : [];
            if (!array_key_exists('public_ip', $mergedProperties)) {
                $mergedProperties['public_ip'] = $ip;
            }
            try {
                $ua = request()?->userAgent();
                if ($ua && !array_key_exists('user_agent', $mergedProperties)) {
                    $mergedProperties['user_agent'] = mb_substr($ua, 0, 180);
                }
            } catch (\Throwable) {
                // Ignore if running in CLI without request
            }

            return static::create([
                'business_id' => $resolvedBusinessId,
                'user_id' => $resolvedUserId,
                'action' => $action,
                'description' => $description,
                'properties' => $mergedProperties,
                'ip_address' => $ip,
            ]);
        } catch (\Throwable $e) {
            // Fail silently to avoid interrupting core user actions
            report($e);
            return null;
        }
    }
}
