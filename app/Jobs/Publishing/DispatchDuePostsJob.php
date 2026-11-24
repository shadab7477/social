<?php

namespace App\Jobs\Publishing;

use App\Enums\PostStatus;
use App\Models\PostVariant;
use App\Models\PublishingRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DispatchDuePostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job to claim and dispatch due post variants.
     */
    public function handle(): void
    {
        $dueVariants = PostVariant::whereIn('status', [PostStatus::SCHEDULED, PostStatus::QUEUED])
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->with(['post', 'socialProfile.connection'])
            ->get();

        foreach ($dueVariants as $variant) {
            // Check parent post eligibility
            if ($variant->post->status === PostStatus::CANCELLED || $variant->post->status === PostStatus::DRAFT) {
                continue;
            }

            // Atomic Lock per variant to prevent duplicate workers claiming the same post
            $lockKey = 'publish_lock:variant:' . $variant->id;
            $lock = Cache::lock($lockKey, 60);

            if ($lock->get()) {
                $idempotencyKey = (string) Str::uuid();

                $publishingRecord = PublishingRecord::updateOrCreate(
                    ['post_variant_id' => $variant->id],
                    [
                        'idempotency_key' => $idempotencyKey,
                        'status' => 'queued',
                        'locked_at' => now(),
                        'locked_by' => gethostname() ?: 'worker',
                    ]
                );

                $variant->update(['status' => PostStatus::QUEUED]);

                // Dispatch to dedicated Redis publishing queue
                PublishPostVariantJob::dispatch($variant->id, $idempotencyKey)
                    ->onQueue('publishing');
            }
        }
    }
}

