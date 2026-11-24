<?php

use App\Models\SandboxScenario;
use Database\Seeders\SandboxScenarioSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sandbox_scenarios') && SandboxScenario::count() > 0) {
            (new SandboxScenarioSeeder())->run();
        }
    }

    public function down(): void
    {
        // Data migration: no destructive rollback needed.
    }
};
