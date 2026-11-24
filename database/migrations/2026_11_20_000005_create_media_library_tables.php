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
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();

            $table->unique(['workspace_id', 'parent_id', 'name'], 'uq_ws_folder_parent_name');
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['workspace_id', 'name'], 'uq_ws_tag_name');
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('uploader_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->onDelete('set null');
            $table->string('file_name');
            $table->string('storage_path', 1024);
            $table->string('disk', 50)->default('public');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size'); // bytes
            $table->enum('media_type', ['image', 'video', 'gif', 'document']);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('duration_seconds', 8, 2)->nullable();
            $table->string('aspect_ratio', 20)->nullable();
            $table->string('public_url', 1024);
            $table->string('thumbnail_url', 1024)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'folder_id']);
            $table->index(['workspace_id', 'media_type']);
        });

        Schema::create('media_asset_tags', function (Blueprint $table) {
            $table->foreignId('media_asset_id')->constrained('media_assets')->onDelete('cascade');
            $table->foreignId('media_tag_id')->constrained('media_tags')->onDelete('cascade');

            $table->primary(['media_asset_id', 'media_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_asset_tags');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_folders');
    }
};

