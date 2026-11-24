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
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('metric_key', 100);
            $table->char('billing_cycle_month', 7); // e.g. 2026-11
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['workspace_id', 'metric_key', 'billing_cycle_month'], 'uq_usage_cycle');
            $table->index(['workspace_id', 'billing_cycle_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
