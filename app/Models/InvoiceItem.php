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
        'sale_type',
        'unit_price',
        'retail_price',
        'fixed_notified_value',
        'value_excl_tax',
        'tax_rate',
        'sales_tax_amount',
        'further_tax_rate',
        'further_tax_amount',
        'extra_tax_amount',
        'send_extra_tax_as_empty',
        'fed_payable_amount',
        'st_withheld_rate',
        'st_withheld_amount',
        'sro_schedule_no',
        'sro_item_serial',
        'discount_amount',
        'total_value_incl_tax',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'fixed_notified_value' => 'decimal:2',
        'value_excl_tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'sales_tax_amount' => 'decimal:2',
        'further_tax_rate' => 'decimal:2',
        'further_tax_amount' => 'decimal:2',
        'extra_tax_amount' => 'decimal:2',
        'send_extra_tax_as_empty' => 'boolean',
        'fed_payable_amount' => 'decimal:2',
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

