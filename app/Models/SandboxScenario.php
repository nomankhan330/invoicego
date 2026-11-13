<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SandboxScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_code',
        'title',
        'description',
        'applicable_sectors',
        'applicable_natures',
        'sample_payload',
    ];

    protected $casts = [
        'applicable_sectors' => 'array',
        'applicable_natures' => 'array',
        'sample_payload' => 'array',
    ];

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_sandbox_progress')
            ->withPivot('status', 'passed_at', 'last_fbr_response')
            ->withTimestamps();
    }
}

