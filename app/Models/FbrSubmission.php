<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbrSubmission extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'invoice_id',
        'scenario_code',
        'environment',
        'endpoint',
        'request_payload',
        'response_payload',
        'http_status',
        'outcome',
        'error_message',
        'ip_address',
        'executed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'executed_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

