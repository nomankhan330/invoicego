<?php

namespace App\Scopes;

use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if in console without explicit business or if explicitly disabled
        $tenantService = app(TenantServiceInterface::class);
        $activeBusinessId = $tenantService->getActiveBusinessId();

        if ($activeBusinessId) {
            $builder->where($model->getTable() . '.business_id', $activeBusinessId);
        }
    }
}
