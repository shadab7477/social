<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publishing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_variant_id')->unique()->constrained('post_variants')->onDelete('cascade');
            $table->string('idempotency_key', 128)->unique();
            $table->enum('status', ['pending', 'queued', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'locked_at']);
        });

        Schema::create('publish_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publishing_record_id')->constrained('publishing_records')->onDelete('cascade');
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->json('request_payload')->nullable();
            $table->integer('response_status_code')->nullable();
            $table->json('response_body')->nullable();
            $table->string('provider_error_code', 100)->nullable();
            $table->text('provider_error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['publishing_record_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_attempts');
        Schema::dropIfExists('publishing_records');
    }
};

