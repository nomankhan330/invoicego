<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code', 20)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 80)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'cnic')) {
                $table->string('cnic', 20)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'admin_role')) {
                $table->string('admin_role', 30)->default('user')->after('is_super_admin');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
        });

        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'business_type')) {
                $table->string('business_type', 40)->nullable()->default('Company')->after('name');
            }
            if (!Schema::hasColumn('businesses', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('businesses', 'fbr_pos_id')) {
                $table->string('fbr_pos_id', 60)->nullable()->after('current_environment');
            }
            if (!Schema::hasColumn('businesses', 'fbr_registration_status')) {
                $table->string('fbr_registration_status', 40)->nullable()->default('Registered')->after('fbr_pos_id');
            }
            if (!Schema::hasColumn('businesses', 'fbr_integration_status')) {
                $table->string('fbr_integration_status', 40)->nullable()->default('Not Connected')->after('fbr_registration_status');
            }
        });

        // Backfill user_code and admin_role for existing users safely
        $users = DB::table('users')->orderBy('id')->get(['id', 'user_code', 'is_super_admin', 'admin_role']);
        foreach ($users as $u) {
            $updates = [];
            if (empty($u->user_code)) {
                $updates['user_code'] = 'USR-' . str_pad((string) $u->id, 4, '0', STR_PAD_LEFT);
            }
            if ($u->is_super_admin && ($u->admin_role === 'user' || empty($u->admin_role))) {
                $updates['admin_role'] = 'super_admin';
            }
            if (!empty($updates)) {
                DB::table('users')->where('id', $u->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['user_code', 'username', 'cnic', 'admin_role', 'last_login_at'], fn ($c) => Schema::hasColumn('users', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('businesses', function (Blueprint $table) {
            $cols = array_filter(['business_type', 'city', 'fbr_pos_id', 'fbr_registration_status', 'fbr_integration_status'], fn ($c) => Schema::hasColumn('businesses', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
