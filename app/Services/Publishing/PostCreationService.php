<?php

namespace App\Services\Publishing;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\PostVariant;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostCreationService
{
    /**
     * Create a master campaign post and generate tailored variants for all target profiles.
     */
    public function createPost(Workspace $workspace, User $author, array $data): Post
    {
        $profileIds = $data['profile_ids'] ?? [];
        if (empty($profileIds)) {
            throw ValidationException::withMessages([
                'profile_ids' => 'Please select at least one social profile to publish to.',
            ]);
        }

        return DB::transaction(function () use ($workspace, $author, $data, $profileIds) {
            $status = $data['status'] ?? PostStatus::DRAFT;
            if (is_string($status)) {
                $status = PostStatus::from($status);
            }

            $scheduledAt = !empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
            if ($status === PostStatus::SCHEDULED && !$scheduledAt) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'Please provide a valid scheduled date and time.',
                ]);
            }

            // 1. Create Master Parent Post
            $post = Post::create([
                'workspace_id' => $workspace->id,
                'client_id' => $data['client_id'] ?? null,
                'author_id' => $author->id,
                'title' => $data['title'] ?? null,
                'base_caption' => $data['base_caption'] ?? '',
                'destination_link' => $data['destination_link'] ?? null,
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'timezone' => $data['timezone'] ?? $workspace->timezone ?? 'UTC',
                'requires_approval' => $data['requires_approval'] ?? false,
            ]);

            // 2. Generate Profile Variants
            $variantCaptions = $data['variant_captions'] ?? [];
            $variantSettings = $data['variant_settings'] ?? [];
            $mediaAssetIds = $data['media_asset_ids'] ?? [];

            foreach ($profileIds as $profileId) {
                $customCaption = $variantCaptions[$profileId] ?? null;
                // If custom caption matches base caption, keep null to inherit
                if ($customCaption === $post->base_caption) {
                    $customCaption = null;
                }

                $variant = PostVariant::create([
                    'post_id' => $post->id,
                    'social_profile_id' => $profileId,
                    'custom_caption' => $customCaption,
                    'platform_settings' => $variantSettings[$profileId] ?? null,
                    'status' => $status,
                    'scheduled_at' => $scheduledAt,
                ]);

                // 3. Attach Media Assets with Sort Order
                $variantMedia = $data['variant_media'][$profileId] ?? $mediaAssetIds;
                foreach ($variantMedia as $order => $assetId) {
                    PostMedia::create([
                        'post_variant_id' => $variant->id,
                        'media_asset_id' => $assetId,
                        'sort_order' => $order,
                    ]);
                }
            }

            return $post->load(['variants.socialProfile', 'variants.mediaAssets']);
        });
    }

    /**
     * Duplicate an existing post into a fresh draft.
     */
    public function duplicatePost(Post $post, User $author): Post
    {
        return DB::transaction(function () use ($post, $author) {
            $newPost = Post::create([
                'workspace_id' => $post->workspace_id,
                'client_id' => $post->client_id,
                'author_id' => $author->id,
                'title' => $post->title ? "Copy of " . $post->title : null,
                'base_caption' => $post->base_caption,
                'destination_link' => $post->destination_link,
                'status' => PostStatus::DRAFT,
                'timezone' => $post->timezone,
            ]);

            foreach ($post->variants as $variant) {
                $newVariant = PostVariant::create([
                    'post_id' => $newPost->id,
                    'social_profile_id' => $variant->social_profile_id,
                    'custom_caption' => $variant->custom_caption,
                    'platform_settings' => $variant->platform_settings,
                    'status' => PostStatus::DRAFT,
                ]);

                foreach ($variant->postMedia as $media) {
                    PostMedia::create([
                        'post_variant_id' => $newVariant->id,
                        'media_asset_id' => $media->media_asset_id,
                        'sort_order' => $media->sort_order,
                    ]);
                }
            }

            return $newPost->load(['variants.socialProfile', 'variants.mediaAssets']);
        });
    }
}

