<?php

namespace App\Jobs\Publishing;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PublishAttempt;
use App\Models\PublishingRecord;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\Registry\SocialProviderManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishPostVariantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(
        public int $variantId,
        public string $idempotencyKey
    ) {}

    /**
     * Execute worker publishing pipeline.
     */
    public function handle(SocialProviderManager $providerManager): void
    {
        $variant = PostVariant::with(['post', 'socialProfile.connection', 'mediaAssets'])->find($this->variantId);
        if (!$variant) {
            return;
        }

        $publishingRecord = PublishingRecord::where('post_variant_id', $variant->id)->first();
        if ($publishingRecord) {
            $publishingRecord->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
        }

        $variant->update(['status' => PostStatus::PUBLISHING]);

        $profile = $variant->socialProfile;
        if (!$profile || !$profile->is_active || !$profile->publishing_enabled) {
            $this->failVariant($variant, $publishingRecord, "Target profile is inactive or publishing is disabled.");
            return;
        }

        $connection = $profile->connection;
        if (!$connection || !$connection->isPublishable()) {
            $this->failVariant($variant, $publishingRecord, "Connection for {$profile->name} is expired or requires reauthorization.");
            return;
        }

        // Prepare Payload
        $mediaUrls = $variant->mediaAssets->pluck('public_url')->toArray();
        $payload = new PublishingPayloadDTO(
            caption: $variant->getEffectiveCaption(),
            mediaUrls: $mediaUrls,
            linkUrl: $variant->post->destination_link,
            platformSettings: $variant->platform_settings ?? [],
            idempotencyKey: $this->idempotencyKey
        );

        $startTime = microtime(true);

        try {
            $adapter = $providerManager->getPublishingProvider($profile->platform);
            $result = $adapter->publish($profile, $payload);
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            // Log attempt
            if ($publishingRecord) {
                PublishAttempt::create([
                    'publishing_record_id' => $publishingRecord->id,
                    'attempt_number' => $variant->retry_count + 1,
                    'request_payload' => [
                        'caption' => $payload->caption,
                        'media_count' => count($payload->mediaUrls),
                        'link' => $payload->linkUrl,
                    ],
                    'response_status_code' => $result->success ? 200 : 400,
                    'response_body' => $result->rawResponse,
                    'provider_error_code' => $result->errorCode,
                    'provider_error_message' => $result->errorMessage,
                    'duration_ms' => $durationMs,
                ]);
            }

            if ($result->success) {
                $variant->update([
                    'status' => PostStatus::PUBLISHED,
                    'external_post_id' => $result->providerPostId,
                    'external_post_url' => $result->postUrl,
                    'published_at' => now(),
                    'error_message' => null,
                ]);

                if ($publishingRecord) {
                    $publishingRecord->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }

                $this->resolveParentPostStatus($variant->post);
            } else {
                // If retriable and under max retries (only release if running on an async queue)
                if ($result->retriable && $variant->retry_count < 2 && $this->job !== null) {
                    $variant->increment('retry_count');
                    $backoff = 60 * pow(2, $variant->retry_count);
                    $this->release($backoff);
                    return;
                }

                $this->failVariant($variant, $publishingRecord, $result->errorMessage ?? 'Provider publishing rejected');
            }

        } catch (Exception $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($publishingRecord) {
                PublishAttempt::create([
                    'publishing_record_id' => $publishingRecord->id,
                    'attempt_number' => $variant->retry_count + 1,
                    'provider_error_message' => $e->getMessage(),
                    'duration_ms' => $durationMs,
                ]);
            }

            if ($variant->retry_count < 2 && $this->job !== null) {
                $variant->increment('retry_count');
                $this->release(60 * pow(2, $variant->retry_count));
                return;
            }

            $this->failVariant($variant, $publishingRecord, "Publishing Exception: " . $e->getMessage());
        }
    }

    /**
     * Mark variant and publishing record as failed, and resolve parent post status.
     */
    protected function failVariant(PostVariant $variant, ?PublishingRecord $record, string $errorMessage): void
    {
        $variant->update([
            'status' => PostStatus::FAILED,
            'error_message' => $errorMessage,
        ]);

        if ($record) {
            $record->update(['status' => 'failed']);
        }

        $this->resolveParentPostStatus($variant->post);
    }

    /**
     * Calculate and update master parent Post status based on all its variants.
     */
    protected function resolveParentPostStatus(Post $post): void
    {
        $variants = $post->variants()->get();
        $total = $variants->count();
        $publishedCount = $variants->where('status', PostStatus::PUBLISHED)->count();
        $failedCount = $variants->where('status', PostStatus::FAILED)->count();

        if ($publishedCount === $total) {
            $post->update(['status' => PostStatus::PUBLISHED]);
        } elseif ($failedCount === $total) {
            $post->update(['status' => PostStatus::FAILED]);
        } elseif ($publishedCount > 0 && ($publishedCount + $failedCount === $total)) {
            // Some published, some failed -> PARTIALLY PUBLISHED!
            $post->update(['status' => PostStatus::PARTIALLY_PUBLISHED]);
        }
    }
}

