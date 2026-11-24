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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50); // meta, x, stripe, razorpay
            $table->string('event_id');
            $table->string('event_name');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed', 'duplicate'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            $table->unique(['provider', 'event_id'], 'uq_provider_event');
            $table->index(['status', 'received_at'], 'idx_webhook_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};

