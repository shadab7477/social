<?php

namespace App\Services\Reports;

use App\Enums\PostStatus;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Models\AnalyticsSnapshot;
use App\Models\Client;
use App\Models\PostVariant;
use App\Models\Report;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportBuilderService
{
    /**
     * Gather aggregated analytics and content performance metrics for a reporting period.
     */
    public function gatherReportData(
        Workspace $workspace,
        ?Client $client,
        Carbon $dateFrom,
        Carbon $dateTo,
        array $brandingOptions = []
    ): array {
        $dateFromStr = $dateFrom->toDateString();
        $dateToStr = $dateTo->toDateString();

        // Target profiles
        $profilesQuery = SocialProfile::where('workspace_id', $workspace->id);
        if ($client) {
            $profilesQuery->where('client_id', $client->id);
        }
        $profileIds = $profilesQuery->pluck('id')->toArray();

        // 1. KPI Aggregation
        $snapshots = AnalyticsSnapshot::where('workspace_id', $workspace->id)
            ->whereIn('social_profile_id', $profileIds)
            ->whereBetween('metric_date', [$dateFromStr, $dateToStr])
            ->get();

        $impressions = (float) $snapshots->where('metric_name', 'impressions')->sum('metric_value');
        $reach = (float) $snapshots->where('metric_name', 'reach')->sum('metric_value');
        $engagement = (float) $snapshots->whereIn('metric_name', ['engagement', 'likes', 'comments', 'shares'])->sum('metric_value');
        $followers = (float) $snapshots->where('metric_name', 'followers_count')->max('metric_value') ?? 0;
        $engagementRate = $reach > 0 ? round(($engagement / $reach) * 100, 2) : 0;

        // 2. Platform Breakdown
        $networkBreakdown = [];
        $profiles = SocialProfile::whereIn('id', $profileIds)->get();
        foreach ($profiles as $profile) {
            $platKey = $profile->platform->value;
            if (!isset($networkBreakdown[$platKey])) {
                $networkBreakdown[$platKey] = [
                    'platform' => $profile->platform->label(),
                    'icon' => $profile->platform->icon(),
                    'impressions' => 0,
                    'reach' => 0,
                    'engagement' => 0,
                    'published_posts' => 0,
                ];
            }

            $profSnapshots = $snapshots->where('social_profile_id', $profile->id);
            $networkBreakdown[$platKey]['impressions'] += (float) $profSnapshots->where('metric_name', 'impressions')->sum('metric_value');
            $networkBreakdown[$platKey]['reach'] += (float) $profSnapshots->where('metric_name', 'reach')->sum('metric_value');
            $networkBreakdown[$platKey]['engagement'] += (float) $profSnapshots->whereIn('metric_name', ['engagement', 'likes', 'comments', 'shares'])->sum('metric_value');
        }

        // 3. Published Posts & Top Performing Content
        $postsQuery = PostVariant::query()
            ->where('status', PostStatus::PUBLISHED)
            ->whereIn('social_profile_id', $profileIds)
            ->whereBetween('published_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->with(['post.client', 'socialProfile', 'mediaAssets', 'analyticsSnapshots'])
            ->latest('published_at');

        $publishedVariants = $postsQuery->get();

        foreach ($publishedVariants as $pv) {
            $platKey = $pv->socialProfile->platform->value;
            if (isset($networkBreakdown[$platKey])) {
                $networkBreakdown[$platKey]['published_posts']++;
            }
        }

        $topPosts = $publishedVariants->map(function ($variant) {
            $impr = (int) $variant->analyticsSnapshots->where('metric_name', 'impressions')->first()?->metric_value;
            $rch = (int) $variant->analyticsSnapshots->where('metric_name', 'reach')->first()?->metric_value;
            $lks = (int) $variant->analyticsSnapshots->where('metric_name', 'likes')->first()?->metric_value;
            $cmts = (int) $variant->analyticsSnapshots->where('metric_name', 'comments')->first()?->metric_value;

            return [
                'id' => $variant->id,
                'caption' => $variant->getEffectiveCaption(),
                'platform' => $variant->socialProfile->platform->label(),
                'published_at' => $variant->published_at?->format('M d, Y h:i A') ?? 'N/A',
                'impressions' => $impr,
                'reach' => $rch,
                'reactions' => $lks + $cmts,
                'external_url' => $variant->external_post_url,
                'thumbnail' => $variant->mediaAssets->first()?->public_url,
            ];
        })->sortByDesc('impressions')->take(10)->values()->toArray();

        return [
            'agency_name' => $brandingOptions['agency_name'] ?? $workspace->name,
            'client_name' => $client ? $client->name : 'All Clients Combined',
            'period_from' => $dateFrom->format('M d, Y'),
            'period_to' => $dateTo->format('M d, Y'),
            'primary_color' => $brandingOptions['primary_color'] ?? '#4f46e5',
            'kpis' => [
                'impressions' => $impressions,
                'reach' => $reach,
                'engagement' => $engagement,
                'engagement_rate' => $engagementRate,
                'followers' => $followers,
                'total_posts_published' => $publishedVariants->count(),
            ],
            'network_breakdown' => array_values($networkBreakdown),
            'top_posts' => $topPosts,
        ];
    }

    /**
     * Generate CSV export format.
     */
    public function generateCsv(Report $report, array $data): array
    {
        $filename = 'reports/' . Str::uuid() . '.csv';

        $handle = fopen('php://temp', 'r+');

        // Agency & Client Header
        fputcsv($handle, ['SOCIAL MEDIA PERFORMANCE REPORT']);
        fputcsv($handle, ['Agency', $data['agency_name']]);
        fputcsv($handle, ['Client', $data['client_name']]);
        fputcsv($handle, ['Reporting Period', $data['period_from'] . ' - ' . $data['period_to']]);
        fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
        fputcsv($handle, []);

        // KPI Summary Section
        fputcsv($handle, ['EXECUTIVE KPI SUMMARY']);
        fputcsv($handle, ['Metric', 'Total Value']);
        fputcsv($handle, ['Total Impressions', $data['kpis']['impressions']]);
        fputcsv($handle, ['Unique Reach', $data['kpis']['reach']]);
        fputcsv($handle, ['Total Engagements', $data['kpis']['engagement']]);
        fputcsv($handle, ['Engagement Rate (%)', $data['kpis']['engagement_rate'] . '%']);
        fputcsv($handle, ['Audience Followers', $data['kpis']['followers']]);
        fputcsv($handle, ['Total Posts Published', $data['kpis']['total_posts_published']]);
        fputcsv($handle, []);

        // Network Breakdown Section
        fputcsv($handle, ['PERFORMANCE BY SOCIAL NETWORK']);
        fputcsv($handle, ['Social Network', 'Impressions', 'Reach', 'Engagements', 'Published Posts']);
        foreach ($data['network_breakdown'] as $net) {
            fputcsv($handle, [
                $net['platform'],
                $net['impressions'],
                $net['reach'],
                $net['engagement'],
                $net['published_posts'],
            ]);
        }
        fputcsv($handle, []);

        // Top Content Section
        fputcsv($handle, ['TOP PERFORMING CONTENT']);
        fputcsv($handle, ['Post ID', 'Platform', 'Published Date', 'Caption Snippet', 'Impressions', 'Reach', 'Reactions']);
        foreach ($data['top_posts'] as $post) {
            fputcsv($handle, [
                $post['id'],
                $post['platform'],
                $post['published_at'],
                Str::limit($post['caption'], 80),
                $post['impressions'],
                $post['reach'],
                $post['reactions'],
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('public')->put($filename, $content);
        $fileUrl = Storage::disk('public')->url($filename);

        return [
            'file_path' => $filename,
            'file_url' => $fileUrl,
        ];
    }

    /**
     * Generate HTML/PDF Executive Summary format.
     */
    public function generateHtmlPdf(Report $report, array $data): array
    {
        $filename = 'reports/' . Str::uuid() . '.html';

        $html = view('reports.templates.executive-summary', [
            'report' => $report,
            'data' => $data,
        ])->render();

        Storage::disk('public')->put($filename, $html);
        $fileUrl = Storage::disk('public')->url($filename);

        return [
            'file_path' => $filename,
            'file_url' => $fileUrl,
        ];
    }

    /**
     * Complete transactional report creation pipeline.
     */
    public function createReport(
        User $creator,
        string $title,
        ReportFormat $format,
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $clientId = null,
        array $branding = []
    ): Report {
        $workspace = $creator->activeWorkspace() ?? app('current_workspace');
        $client = $clientId ? Client::find($clientId) : null;

        $report = Report::create([
            'workspace_id' => $workspace->id,
            'client_id' => $clientId,
            'title' => $title,
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branding_options' => $branding,
            'status' => ReportStatus::GENERATING,
            'created_by' => $creator->id,
        ]);

        $data = $this->gatherReportData($workspace, $client, $dateFrom, $dateTo, $branding);

        $fileInfo = $format === ReportFormat::CSV
            ? $this->generateCsv($report, $data)
            : $this->generateHtmlPdf($report, $data);

        $report->update([
            'file_path' => $fileInfo['file_path'],
            'file_url' => $fileInfo['file_url'],
            'status' => ReportStatus::READY,
        ]);

        return $report;
    }
}

