<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_code', 20)->unique();
            $table->string('title');
            $table->text('description');
            $table->json('applicable_sectors')->nullable();
            $table->json('applicable_natures')->nullable();
            $table->json('sample_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('business_sandbox_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('sandbox_scenario_id')->constrained('sandbox_scenarios')->cascadeOnDelete();
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending')->index();
            $table->timestamp('passed_at')->nullable();
            $table->text('last_fbr_response')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'sandbox_scenario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_sandbox_progress');
        Schema::dropIfExists('sandbox_scenarios');
    }
};
