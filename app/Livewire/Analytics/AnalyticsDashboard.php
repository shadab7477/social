<?php

namespace App\Livewire\Analytics;

use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Jobs\Analytics\SyncProfileAnalyticsJob;
use App\Models\AnalyticsSnapshot;
use App\Models\Client;
use App\Models\PostVariant;
use App\Models\SocialProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AnalyticsDashboard extends Component
{
    // Filter State
    public string $dateRange = '30d'; // 7d, 30d, 90d
    public ?int $selectedClientId = null;
    public ?int $selectedProfileId = null;

    // Flash message
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    public function updatingDateRange(): void
    {
        $this->dispatch('analytics-updated');
    }

    public function updatingSelectedClientId(): void
    {
        $this->selectedProfileId = null;
        $this->dispatch('analytics-updated');
    }

    public function updatingSelectedProfileId(): void
    {
        $this->dispatch('analytics-updated');
    }

    /**
     * Dispatch background worker jobs to refresh metrics from provider APIs.
     */
    public function syncNow(): void
    {
        $query = SocialProfile::where('publishing_enabled', true)->where('is_active', true);
        if ($this->selectedClientId) {
            $query->where('client_id', $this->selectedClientId);
        }
        if ($this->selectedProfileId) {
            $query->where('id', $this->selectedProfileId);
        }

        $profiles = $query->get();

        foreach ($profiles as $profile) {
            SyncProfileAnalyticsJob::dispatch($profile->id);
        }

        $this->feedbackMessage = "Analytics synchronization queued for {$profiles->count()} active profiles!";
        $this->feedbackType = 'info';
    }

    /**
     * Resolve date boundary Carbon instances.
     */
    protected function getDateBoundaries(): array
    {
        $endDate = now()->endOfDay();
        $startDate = match ($this->dateRange) {
            '7d' => now()->subDays(7)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            default => now()->subDays(30)->startOfDay(),
        };

        return [$startDate, $endDate];
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateBoundaries();

        // Base Snapshot Query with automatic TenantScope
        $snapshotQuery = AnalyticsSnapshot::query()
            ->whereBetween('metric_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($this->selectedClientId) {
            $snapshotQuery->whereHas('socialProfile', fn($q) => $q->where('client_id', $this->selectedClientId));
        }

        if ($this->selectedProfileId) {
            $snapshotQuery->where('social_profile_id', $this->selectedProfileId);
        }

        // 1. KPI Calculations
        $impressions = (float) (clone $snapshotQuery)->where('metric_name', 'impressions')->sum('metric_value');
        $reach = (float) (clone $snapshotQuery)->where('metric_name', 'reach')->sum('metric_value');
        $engagement = (float) (clone $snapshotQuery)->whereIn('metric_name', ['engagement', 'likes', 'comments', 'shares'])->sum('metric_value');
        $followers = (float) (clone $snapshotQuery)->where('metric_name', 'followers_count')->max('metric_value') ?? 0;

        $engagementRate = $reach > 0 ? round(($engagement / $reach) * 100, 2) : 0;

        // 2. Chart 1: Impressions and Reach Trend (Aggregated by Day)
        $dailyTrends = (clone $snapshotQuery)
            ->whereIn('metric_name', ['impressions', 'reach'])
            ->select('metric_date', 'metric_name', DB::raw('SUM(metric_value) as total'))
            ->groupBy('metric_date', 'metric_name')
            ->orderBy('metric_date')
            ->get();

        $chartLabels = [];
        $impressionsData = [];
        $reachData = [];

        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());
        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $chartLabels[] = $dt->format('M d');
            $impressionsData[] = (float) ($dailyTrends->where('metric_date', $dateStr)->where('metric_name', 'impressions')->first()?->total ?? 0);
            $reachData[] = (float) ($dailyTrends->where('metric_date', $dateStr)->where('metric_name', 'reach')->first()?->total ?? 0);
        }

        // 3. Chart 2: Platform Distribution
        $platformBreakdown = (clone $snapshotQuery)
            ->whereIn('metric_name', ['impressions', 'reach'])
            ->join('social_profiles', 'analytics_snapshots.social_profile_id', '=', 'social_profiles.id')
            ->select('social_profiles.platform', DB::raw('SUM(metric_value) as total'))
            ->groupBy('social_profiles.platform')
            ->get();

        $platformLabels = [];
        $platformTotals = [];
        foreach ($platformBreakdown as $pb) {
            $platformLabels[] = ucfirst($pb->platform);
            $platformTotals[] = (float) $pb->total;
        }

        // 4. Top Performing Posts Table
        $topVariantsQuery = PostVariant::query()
            ->where('status', PostStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [$startDate, $endDate])
            ->with(['post.client', 'socialProfile', 'mediaAssets', 'analyticsSnapshots'])
            ->whereHas('post');

        if ($this->selectedClientId) {
            $topVariantsQuery->whereHas('post', fn($q) => $q->where('client_id', $this->selectedClientId));
        }

        if ($this->selectedProfileId) {
            $topVariantsQuery->where('social_profile_id', $this->selectedProfileId);
        }

        $topVariants = $topVariantsQuery->latest('published_at')->take(10)->get();

        $clients = Client::orderBy('name')->get();
        $profilesQuery = SocialProfile::where('is_active', true);
        if ($this->selectedClientId) {
            $profilesQuery->where('client_id', $this->selectedClientId);
        }
        $profiles = $profilesQuery->get();

        return view('livewire.analytics.analytics-dashboard', [
            'kpis' => [
                'impressions' => $impressions,
                'reach' => $reach,
                'engagement' => $engagement,
                'engagementRate' => $engagementRate,
                'followers' => $followers,
            ],
            'chartData' => [
                'labels' => $chartLabels,
                'impressions' => $impressionsData,
                'reach' => $reachData,
            ],
            'platformData' => [
                'labels' => $platformLabels,
                'totals' => $platformTotals,
            ],
            'topVariants' => $topVariants,
            'clients' => $clients,
            'profiles' => $profiles,
        ]);
    }
}

