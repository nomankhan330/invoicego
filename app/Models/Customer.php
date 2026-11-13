<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'name',
        'ntn_cnic',
        'strn',
        'address',
        'province',
        'category',
        'registration_type',
        'fbr_verification_status',
        'contact_person',
        'phone',
        'email',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isRegistered(): bool
    {
        return $this->registration_type === 'registered';
    }
}

