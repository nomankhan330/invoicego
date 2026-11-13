<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_time', 20)->nullable()->after('invoice_date');
            $table->string('payment_mode', 50)->default('Cash')->after('invoice_time');
            $table->string('reference_invoice_no', 100)->nullable()->after('payment_mode');
            $table->string('buyer_name', 255)->nullable()->after('customer_id');
            $table->string('buyer_ntn_cnic', 20)->nullable()->after('buyer_name');
            $table->string('buyer_province', 100)->nullable()->after('buyer_ntn_cnic');
            $table->text('buyer_address')->nullable()->after('buyer_province');
            $table->enum('buyer_type', ['registered', 'unregistered'])->default('unregistered')->after('buyer_address');
            $table->decimal('fed_amount', 15, 2)->default(0.00)->after('extra_tax_amount');
            $table->decimal('net_receivable_amount', 15, 2)->default(0.00)->after('total_invoice_value');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('sale_type', 100)->default('Goods (Standard)')->after('uom');
            $table->decimal('retail_price', 15, 2)->nullable()->after('unit_price');
            $table->decimal('fixed_notified_value', 15, 2)->nullable()->after('retail_price');
            $table->decimal('extra_tax_amount', 15, 2)->default(0.00)->after('further_tax_amount');
            $table->boolean('send_extra_tax_as_empty')->default(false)->after('extra_tax_amount');
            $table->decimal('fed_payable_amount', 15, 2)->default(0.00)->after('send_extra_tax_as_empty');
            $table->string('sro_schedule_no', 100)->nullable()->after('fed_payable_amount');
            $table->string('sro_item_serial', 100)->nullable()->after('sro_schedule_no');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_time',
                'payment_mode',
                'reference_invoice_no',
                'buyer_name',
                'buyer_ntn_cnic',
                'buyer_province',
                'buyer_address',
                'buyer_type',
                'fed_amount',
                'net_receivable_amount',
            ]);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'sale_type',
                'retail_price',
                'fixed_notified_value',
                'extra_tax_amount',
                'send_extra_tax_as_empty',
                'fed_payable_amount',
                'sro_schedule_no',
                'sro_item_serial',
            ]);
        });
    }
};
