<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('ntn_cnic', 20)->index();
            $table->string('strn', 50)->nullable();
            $table->text('address');
            $table->string('province', 100);
            $table->string('sector', 150);
            $table->json('business_natures');
            $table->boolean('is_retailer_registered')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('sandbox_token')->nullable();
            $table->text('production_token')->nullable();
            $table->boolean('production_submissions_enabled')->default(false);
            $table->enum('current_environment', ['sandbox', 'production'])->default('sandbox');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
