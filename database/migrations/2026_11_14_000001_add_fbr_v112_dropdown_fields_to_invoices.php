<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('supplier_province', 100)->nullable()->after('reference_invoice_no');
            $table->string('destination_of_supply', 100)->nullable()->after('supplier_province');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->boolean('cancel_further_tax')->default(false)->after('further_tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['supplier_province', 'destination_of_supply']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('cancel_further_tax');
        });
    }
};

