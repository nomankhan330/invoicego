<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fbr_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('scenario_code', 20)->nullable()->index();
            $table->enum('environment', ['sandbox', 'production'])->index();
            $table->string('endpoint', 255);
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->integer('http_status')->nullable();
            $table->enum('outcome', ['success', 'rejected', 'network_error', 'reconciled'])->index();
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_submissions');
    }
};
