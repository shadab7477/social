<?php

namespace App\Services\Social\YouTube;

use Illuminate\Support\Facades\Cache;

class YouTubeQuotaTrackerService
{
    // Default project quota limit per day in units
    public const DAILY_LIMIT = 10000;
    public const VIDEO_UPLOAD_COST = 1600;

    /**
     * Check if workspace has enough quota remaining for a video upload.
     */
    public function canUploadVideo(int $workspaceId): bool
    {
        $currentUsage = $this->getTodayUsage($workspaceId);
        return ($currentUsage + self::VIDEO_UPLOAD_COST) <= self::DAILY_LIMIT;
    }

    /**
     * Record units consumed today.
     */
    public function recordUsage(int $workspaceId, int $units = self::VIDEO_UPLOAD_COST): int
    {
        $key = $this->getCacheKey($workspaceId);
        $newUsage = Cache::increment($key, $units);
        Cache::put($key, $newUsage, now()->endOfDay());

        return $newUsage;
    }

    /**
     * Get units consumed today.
     */
    public function getTodayUsage(int $workspaceId): int
    {
        return (int) Cache::get($this->getCacheKey($workspaceId), 0);
    }

    /**
     * Get remaining quota units for today.
     */
    public function getRemainingQuota(int $workspaceId): int
    {
        $remaining = self::DAILY_LIMIT - $this->getTodayUsage($workspaceId);
        return max(0, $remaining);
    }

    protected function getCacheKey(int $workspaceId): string
    {
        return "yt_quota:ws:{$workspaceId}:" . now()->format('Ymd');
    }
}
