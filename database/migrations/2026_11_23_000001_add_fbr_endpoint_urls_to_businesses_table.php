<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('sandbox_url')->nullable()->default('https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb');
            $table->string('sandbox_validation_url')->nullable()->default('https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb');
            $table->string('production_url')->nullable()->default('https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata');
            $table->string('production_validation_url')->nullable()->default('https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata');
            $table->timestamp('sandbox_verified_at')->nullable();
            $table->timestamp('production_verified_at')->nullable();
            $table->string('sandbox_connection_status')->nullable()->default('not_tested');
            $table->string('production_connection_status')->nullable()->default('not_tested');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'sandbox_url',
                'sandbox_validation_url',
                'production_url',
                'production_validation_url',
                'sandbox_verified_at',
                'production_verified_at',
                'sandbox_connection_status',
                'production_connection_status',
            ]);
        });
    }
};
