<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('hs_code', 20)->index();
            $table->text('product_description');
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->string('uom', 30)->default('NOS');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('value_excl_tax', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('sales_tax_amount', 15, 2)->default(0.00);
            $table->decimal('further_tax_rate', 5, 2)->default(0.00);
            $table->decimal('further_tax_amount', 15, 2)->default(0.00);
            $table->decimal('st_withheld_rate', 5, 2)->default(0.00);
            $table->decimal('st_withheld_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_value_incl_tax', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
