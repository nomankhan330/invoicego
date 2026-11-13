<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hs_code', 20)->nullable()->after('hs_code_id');
            $table->string('sale_type', 100)->default('Goods (Standard)')->after('uom');
            $table->string('sro_schedule_no', 100)->nullable()->after('default_tax_rate');
            $table->string('sro_item_serial', 100)->nullable()->after('sro_schedule_no');
            $table->decimal('retail_price', 15, 2)->nullable()->after('sro_item_serial');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('ntn_cnic', 20)->nullable()->change();
            $table->string('category', 100)->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'hs_code',
                'sale_type',
                'sro_schedule_no',
                'sro_item_serial',
                'retail_price',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

