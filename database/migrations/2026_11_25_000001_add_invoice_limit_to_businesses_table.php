<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('businesses', 'invoice_limit')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->unsignedInteger('invoice_limit')->nullable()->default(50)->after('production_submissions_enabled');
            });
        }

        // Backfill any existing business rows with the default 50-invoice limit
        DB::table('businesses')
            ->whereNull('invoice_limit')
            ->update(['invoice_limit' => 50]);

        // Configure global default invoice limit (50) and hide paid subscriptions for now
        if (Schema::hasTable('system_settings')) {
            SystemSetting::updateOrCreate(
                ['key' => 'default_invoice_limit'],
                ['value' => '50', 'group' => 'invoice', 'type' => 'integer']
            );

            SystemSetting::updateOrCreate(
                ['key' => 'show_pricing_plans'],
                ['value' => '0', 'group' => 'general', 'type' => 'boolean']
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('businesses', 'invoice_limit')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('invoice_limit');
            });
        }
    }
};
