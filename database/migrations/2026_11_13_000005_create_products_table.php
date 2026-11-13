<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('hs_code_id')->nullable()->constrained('hs_codes')->nullOnDelete();
            $table->string('name');
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('uom', 30)->default('NOS');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('default_tax_rate', 5, 2)->default(18.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
