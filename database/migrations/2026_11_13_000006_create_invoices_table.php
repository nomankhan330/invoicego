<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('invoice_number', 50)->index();
            $table->enum('invoice_type', ['standard', 'credit_note', 'debit_note'])->default('standard');
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox')->index();
            $table->date('invoice_date')->index();
            $table->enum('status', ['draft', 'pending', 'submitted', 'accepted', 'failed', 'needs_reconciliation', 'cancelled'])->default('draft')->index();
            $table->decimal('sales_value_excl_tax', 15, 2)->default(0.00);
            $table->decimal('sales_tax_amount', 15, 2)->default(0.00);
            $table->decimal('further_tax_amount', 15, 2)->default(0.00);
            $table->decimal('st_withheld_amount', 15, 2)->default(0.00);
            $table->decimal('extra_tax_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_invoice_value', 15, 2)->default(0.00);
            $table->string('fbr_invoice_number', 100)->nullable()->index();
            $table->text('fbr_qr_code')->nullable();
            $table->timestamp('fbr_submitted_at')->nullable();
            $table->json('fbr_validation_errors')->nullable();
            $table->json('fbr_raw_response')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
