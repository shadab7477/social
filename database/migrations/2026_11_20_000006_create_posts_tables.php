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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('restrict');
            $table->string('title')->nullable();
            $table->text('base_caption')->nullable();
            $table->string('destination_link', 2048)->nullable();
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'scheduled',
                'queued',
                'publishing',
                'published',
                'partially_published',
                'failed',
                'cancelled'
            ])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->boolean('requires_approval')->default(false);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
            $table->index(['scheduled_at', 'status']);
        });

        Schema::create('post_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('social_profile_id')->constrained('social_profiles')->onDelete('cascade');
            $table->text('custom_caption')->nullable();
            $table->json('platform_settings')->nullable();
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'scheduled',
                'queued',
                'publishing',
                'published',
                'failed',
                'cancelled'
            ])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('external_post_id')->nullable();
            $table->string('external_post_url', 1024)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->unique(['post_id', 'social_profile_id'], 'uq_post_variant_profile');
            $table->index(['scheduled_at', 'status']);
        });

        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_variant_id')->constrained('post_variants')->onDelete('cascade');
            $table->foreignId('media_asset_id')->constrained('media_assets')->onDelete('restrict');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('custom_alt_text', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_variant_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
        Schema::dropIfExists('post_variants');
        Schema::dropIfExists('posts');
    }
};

