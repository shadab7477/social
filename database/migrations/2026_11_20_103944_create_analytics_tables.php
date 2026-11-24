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
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('social_profile_id')->constrained('social_profiles')->onDelete('cascade');
            $table->foreignId('post_variant_id')->nullable()->constrained('post_variants')->onDelete('set null');
            $table->date('metric_date');
            $table->string('metric_name', 100);
            $table->decimal('metric_value', 14, 2)->default(0.00);
            $table->enum('period', ['day', 'week', 'month', 'lifetime'])->default('day');
            $table->json('raw_payload')->nullable();
            $table->timestamp('fetched_at')->useCurrent();

            $table->index(['workspace_id', 'social_profile_id', 'metric_name', 'metric_date'], 'idx_analytics_query');
            $table->index(['post_variant_id', 'metric_name'], 'idx_post_analytics');
        });

        Schema::create('analytics_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_profile_id')->constrained('social_profiles')->onDelete('cascade');
            $table->enum('status', ['success', 'failed', 'rate_limited'])->default('success');
            $table->unsignedInteger('records_ingested')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->useCurrent();

            $table->index(['social_profile_id', 'synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_sync_logs');
        Schema::dropIfExists('analytics_snapshots');
    }
};
