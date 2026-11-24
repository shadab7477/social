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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('title');
            $table->enum('format', ['pdf', 'csv'])->default('pdf');
            $table->date('date_from');
            $table->date('date_to');
            $table->string('file_path')->nullable();
            $table->string('file_url', 1024)->nullable();
            $table->enum('status', ['generating', 'ready', 'failed'])->default('generating');
            $table->json('branding_options')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['workspace_id', 'client_id', 'created_at']);
        });

        Schema::create('report_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('name');
            $table->enum('frequency', ['weekly', 'monthly'])->default('monthly');
            $table->json('recipients');
            $table->json('branding_options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'is_active', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_configs');
        Schema::dropIfExists('reports');
    }
};
