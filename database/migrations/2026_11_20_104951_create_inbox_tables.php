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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('social_profile_id')->constrained('social_profiles')->onDelete('cascade');
            $table->string('external_conversation_id');
            $table->enum('type', ['comment_thread', 'direct_message', 'mention'])->default('comment_thread');
            $table->string('sender_name');
            $table->string('sender_avatar', 1024)->nullable();
            $table->string('sender_profile_id');
            $table->text('snippet')->nullable();
            $table->enum('status', ['open', 'resolved', 'spam', 'archived'])->default('open');
            $table->boolean('is_unread')->default(true);
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();

            $table->unique(['social_profile_id', 'external_conversation_id'], 'uq_profile_conv');
            $table->index(['workspace_id', 'status', 'is_unread']);
            $table->index(['social_profile_id', 'last_activity_at']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->string('external_message_id');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->string('sender_name');
            $table->string('sender_id');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->unique(['conversation_id', 'external_message_id'], 'uq_conv_msg');
            $table->index(['conversation_id', 'sent_at']);
        });

        Schema::create('conversation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('assigned_to_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['conversation_id', 'assigned_to_user_id'], 'idx_conv_assign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_assignments');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
