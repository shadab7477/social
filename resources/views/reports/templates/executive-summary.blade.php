<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }} — {{ $data['client_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-accent: {{ $data['primary_color'] ?? '#4f46e5' }};
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            line-height: 1.5;
            padding: 30px 20px;
        }
        .report-sheet {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            padding: 40px;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 30px;
        }
        .agency-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-accent);
        }
        .report-meta {
            text-align: right;
            font-size: 0.85rem;
            color: #64748b;
        }
        .report-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .client-subhead {
            font-size: 1rem;
            color: #475569;
            margin-bottom: 25px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 35px;
        }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            border-top: 4px solid var(--primary-accent);
        }
        .kpi-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }
        .kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-sub {
            font-size: 0.75rem;
            color: #10b981;
            font-weight: 600;
            margin-top: 4px;
        }
        .section-heading {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
            font-size: 0.875rem;
        }
        th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid #cbd5e1;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .text-right {
            text-align: right;
        }
        .post-card {
            display: flex;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #ffffff;
        }
        .post-thumb {
            width: 70px;
            height: 70px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #e0e7ff;
            color: #4338ca;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary-accent);
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .report-sheet {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="bi bi-printer"></i> Print / Save as PDF
    </button>

    <div class="report-sheet">
        <!-- Header -->
        <div class="header-bar">
            <div>
                <div class="agency-brand">{{ $data['agency_name'] }}</div>
                <div style="font-size: 0.8rem; color: #64748b;">Social Media Performance & Client Reporting</div>
            </div>
            <div class="report-meta">
                <div><strong>Period:</strong> {{ $data['period_from'] }} – {{ $data['period_to'] }}</div>
                <div>Generated on {{ now()->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Title -->
        <h1 class="report-title">{{ $report->title }}</h1>
        <div class="client-subhead">
            Prepared exclusively for: <strong>{{ $data['client_name'] }}</strong>
        </div>

        <!-- KPI Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total Impressions</div>
                <div class="kpi-value">{{ number_format($data['kpis']['impressions']) }}</div>
                <div class="kpi-sub">Views & Placements</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Unique Reach</div>
                <div class="kpi-value">{{ number_format($data['kpis']['reach']) }}</div>
                <div class="kpi-sub">Unique Audience</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Engagements</div>
                <div class="kpi-value">{{ number_format($data['kpis']['engagement']) }}</div>
                <div class="kpi-sub">{{ $data['kpis']['engagement_rate'] }}% Engagement Rate</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Audience Size</div>
                <div class="kpi-value">{{ number_format($data['kpis']['followers']) }}</div>
                <div class="kpi-sub">Total Followers</div>
            </div>
        </div>

        <!-- Network Breakdown -->
        <div class="section-heading">
            <i class="bi bi-diagram-3-fill" style="color: var(--primary-accent);"></i> Channel Performance Breakdown
        </div>
        <table>
            <thead>
                <tr>
                    <th>Social Network</th>
                    <th class="text-right">Impressions</th>
                    <th class="text-right">Reach</th>
                    <th class="text-right">Engagements</th>
                    <th class="text-right">Posts Published</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['network_breakdown'] as $network)
                    <tr>
                        <td><strong>{{ $network['platform'] }}</strong></td>
                        <td class="text-right">{{ number_format($network['impressions']) }}</td>
                        <td class="text-right">{{ number_format($network['reach']) }}</td>
                        <td class="text-right">{{ number_format($network['engagement']) }}</td>
                        <td class="text-right">{{ number_format($network['published_posts']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">No network activity recorded for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Top Content Section -->
        <div class="section-heading">
            <i class="bi bi-trophy-fill" style="color: #f59e0b;"></i> Top Performing Content Highlights
        </div>
        <div>
            @forelse($data['top_posts'] as $post)
                <div class="post-card">
                    @if($post['thumbnail'])
                        <img src="{{ $post['thumbnail'] }}" alt="Post Media" class="post-thumb">
                    @else
                        <div class="post-thumb" style="display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <i class="bi bi-file-text fs-4"></i>
                        </div>
                    @endif
                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span class="badge">{{ $post['platform'] }}</span>
                            <span style="font-size: 0.75rem; color: #64748b;">{{ $post['published_at'] }}</span>
                        </div>
                        <div style="font-size: 0.875rem; color: #1e293b; margin-bottom: 8px;">
                            {{ $post['caption'] ?: 'No caption' }}
                        </div>
                        <div style="display: flex; gap: 16px; font-size: 0.8rem; color: #475569;">
                            <span><strong>{{ number_format($post['impressions']) }}</strong> Impressions</span>
                            <span><strong>{{ number_format($post['reach']) }}</strong> Reach</span>
                            <span><strong>{{ number_format($post['reactions']) }}</strong> Reactions</span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: #94a3b8; padding: 20px;">
                    No published posts in this period.
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 16px; text-align: center; font-size: 0.75rem; color: #94a3b8;">
            Report compiled by <strong>{{ $data['agency_name'] }}</strong> using SocialPilotX Enterprise Platform.
        </div>
    </div>
</body>
</html>

