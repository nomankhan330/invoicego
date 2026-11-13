<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TenantService implements TenantServiceInterface
{
    protected ?Business $activeBusiness = null;

    public function getActiveBusiness(): ?Business
    {
        if ($this->activeBusiness !== null) {
            return $this->activeBusiness->fresh() ?? $this->activeBusiness;
        }

        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $businessId = Session::get('current_business_id');

        if ($businessId) {
            $business = $user->businesses()->where('businesses.id', $businessId)->first();
            if ($business) {
                $this->activeBusiness = $business;
                return $this->activeBusiness;
            }
        }

        // Fallback: pick user's first business
        $business = $user->businesses()->first();
        if ($business) {
            $this->setActiveBusiness($business);
            return $business;
        }

        return null;
    }

    public function getActiveBusinessId(): ?int
    {
        $business = $this->getActiveBusiness();
        return $business?->id;
    }

    public function setActiveBusiness(Business $business): void
    {
        $this->activeBusiness = $business;
        Session::put('current_business_id', $business->id);
    }

    public function setActiveBusinessById(int $businessId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $business = $user->businesses()->where('businesses.id', $businessId)->first();
        if ($business) {
            $this->setActiveBusiness($business);
            return true;
        }

        return false;
    }

    public function getUserBusinesses(User $user)
    {
        return $user->businesses()->orderBy('businesses.name')->get();
    }

    public function canAccessBusiness(User $user, int $businessId): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->businesses()->where('businesses.id', $businessId)->exists();
    }

    public function registerBusiness(array $userData, array $businessData): Business
    {
        return DB::transaction(function () use ($userData, $businessData) {
            // Find existing user or create a new user
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'password' => Hash::make($userData['password']),
                    'status' => 'active',
                    'is_super_admin' => false,
                ]);
            }

            // Create Business
            $business = Business::create([
                'uuid' => (string) Str::uuid(),
                'name' => $businessData['name'],
                'ntn_cnic' => $businessData['ntn_cnic'],
                'strn' => $businessData['strn'] ?? null,
                'address' => $businessData['address'],
                'province' => $businessData['province'],
                'sector' => $businessData['sector'],
                'business_natures' => $businessData['business_natures'] ?? [],
                'is_retailer_registered' => $businessData['is_retailer_registered'] ?? false,
                'status' => 'pending', // Pending approval by Super Admin
                'current_environment' => 'sandbox',
                'production_submissions_enabled' => false,
            ]);

            // Link User to Business as Owner
            $user->businesses()->attach($business->id, [
                'role' => 'owner',
                'is_default' => true,
            ]);

            // Set as active business in session
            Session::put('current_business_id', $business->id);

            return $business;
        });
    }
}
