<?php

namespace App\Traits;

use App\Models\Business;
use App\Scopes\TenantScope;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->business_id)) {
                $tenantService = app(TenantServiceInterface::class);
                $model->business_id = $tenantService->getActiveBusinessId();
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

