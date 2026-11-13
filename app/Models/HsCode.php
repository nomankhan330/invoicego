<?php

namespace App\Models;

use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HsCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'pct_code',
        'description',
        'default_tax_rate',
        'is_active',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('tenant_or_global', function (Builder $builder) {
            $tenantService = app(TenantServiceInterface::class);
            $activeBusinessId = $tenantService->getActiveBusinessId();

            if ($activeBusinessId) {
                $builder->where(function ($query) use ($activeBusinessId) {
                    $query->whereNull('business_id')
                          ->orWhere('business_id', $activeBusinessId);
                });
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
