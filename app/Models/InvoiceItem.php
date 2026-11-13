<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'invoice_id',
        'business_id',
        'product_id',
        'hs_code',
        'product_description',
        'quantity',
        'uom',
        'unit_price',
        'value_excl_tax',
        'tax_rate',
        'sales_tax_amount',
        'further_tax_rate',
        'further_tax_amount',
        'st_withheld_rate',
        'st_withheld_amount',
        'discount_amount',
        'total_value_incl_tax',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'value_excl_tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'sales_tax_amount' => 'decimal:2',
        'further_tax_rate' => 'decimal:2',
        'further_tax_amount' => 'decimal:2',
        'st_withheld_rate' => 'decimal:2',
        'st_withheld_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_value_incl_tax' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

