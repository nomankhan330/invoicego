<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete()->index();
            $table->string('name');
            $table->string('ntn_cnic', 20)->index();
            $table->string('strn', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('province', 100)->nullable();
            $table->enum('registration_type', ['registered', 'unregistered'])->default('unregistered');
            $table->enum('fbr_verification_status', ['verified', 'unverified', 'failed'])->default('unverified');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

