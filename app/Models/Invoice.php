<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'business_id',
        'customer_id',
        'invoice_number',
        'invoice_type',
        'environment',
        'invoice_date',
        'status',
        'sales_value_excl_tax',
        'sales_tax_amount',
        'further_tax_amount',
        'st_withheld_amount',
        'extra_tax_amount',
        'discount_amount',
        'total_invoice_value',
        'fbr_invoice_number',
        'fbr_qr_code',
        'fbr_submitted_at',
        'fbr_validation_errors',
        'fbr_raw_response',
        'locked_at',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'sales_value_excl_tax' => 'decimal:2',
        'sales_tax_amount' => 'decimal:2',
        'further_tax_amount' => 'decimal:2',
        'st_withheld_amount' => 'decimal:2',
        'extra_tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_invoice_value' => 'decimal:2',
        'fbr_submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'fbr_validation_errors' => 'array',
        'fbr_raw_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fbrSubmissions(): HasMany
    {
        return $this->hasMany(FbrSubmission::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null || in_array($this->status, ['submitted', 'accepted']);
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
