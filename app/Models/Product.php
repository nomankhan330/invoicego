<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'hs_code_id',
        'name',
        'sku',
        'description',
        'uom',
        'unit_price',
        'default_tax_rate',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'default_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function hsCode(): BelongsTo
    {
        return $this->belongsTo(HsCode::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
