<?php

namespace App\Services\Contracts;

use App\Models\Business;
use App\Models\User;

interface TenantServiceInterface
{
    public function getActiveBusiness(): ?Business;
    public function getActiveBusinessId(): ?int;
    public function setActiveBusiness(Business $business): void;
    public function setActiveBusinessById(int $businessId): bool;
    public function getUserBusinesses(User $user);
    public function canAccessBusiness(User $user, int $businessId): bool;
    public function registerBusiness(array $userData, array $businessData): Business;
}

