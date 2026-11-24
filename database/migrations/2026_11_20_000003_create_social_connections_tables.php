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
        Schema::create('social_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('platform', 50);
            $table->string('account_id');
            $table->string('account_name');
            $table->string('account_email')->nullable();
            $table->string('avatar_url', 1024)->nullable();
            $table->text('access_token'); // Encrypted AES-256-GCM
            $table->text('refresh_token')->nullable(); // Encrypted AES-256-GCM
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->json('scopes');
            $table->enum('status', ['connected', 'expired', 'reauthorization_required', 'permission_missing', 'disconnected', 'error'])->default('connected');
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'platform', 'account_id'], 'uq_ws_platform_account');
            $table->index(['workspace_id', 'status']);
            $table->index('token_expires_at');
        });

        Schema::create('social_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('connection_id')->constrained('social_connections')->onDelete('cascade');
            $table->string('platform', 50);
            $table->string('profile_id'); // e.g. Page ID, IG ID, Channel ID
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('avatar_url', 1024)->nullable();
            $table->string('profile_url', 1024)->nullable();
            $table->text('access_token')->nullable(); // Encrypted page-specific token
            $table->json('capabilities');
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('publishing_enabled')->default(true);
            $table->timestamp('last_health_check_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workspace_id', 'platform', 'profile_id'], 'uq_ws_platform_profile');
            $table->index(['workspace_id', 'client_id']);
            $table->index(['is_active', 'publishing_enabled']);
        });

        Schema::create('oauth_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('state_token', 128)->unique();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('platform', 50);
            $table->string('code_verifier', 255)->nullable();
            $table->string('redirect_url', 1024);
            $table->json('metadata')->nullable();
            $table->boolean('is_consumed')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['state_token', 'is_consumed', 'expires_at'], 'idx_oauth_state');
        });

        Schema::create('social_token_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('social_connections')->onDelete('cascade');
            $table->enum('event_type', ['created', 'refreshed', 'refresh_failed', 'expired', 'revoked', 'permission_lost']);
            $table->json('details')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['connection_id', 'event_type', 'occurred_at'], 'idx_token_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_token_events');
        Schema::dropIfExists('oauth_transactions');
        Schema::dropIfExists('social_profiles');
        Schema::dropIfExists('social_connections');
    }
};

