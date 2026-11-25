<div class="analytics-dashboard-wrapper">
    <!-- Top Action & Filter Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i> Analytics & Insights
            </h4>
            <p class="text-muted small mb-0">Multi-channel performance metrics, engagement rates, audience growth, and top content.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Date Range Selector -->
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                <button type="button" wire:click="$set('dateRange', '7d')" class="btn {{ $dateRange === '7d' ? 'btn-primary' : 'btn-outline-secondary' }}">7 Days</button>
                <button type="button" wire:click="$set('dateRange', '30d')" class="btn {{ $dateRange === '30d' ? 'btn-primary' : 'btn-outline-secondary' }}">30 Days</button>
                <button type="button" wire:click="$set('dateRange', '90d')" class="btn {{ $dateRange === '90d' ? 'btn-primary' : 'btn-outline-secondary' }}">90 Days</button>
            </div>

            <!-- Sync Button -->
            <button wire:click="syncNow" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                <span wire:loading.remove wire:target="syncNow"><i class="bi bi-arrow-repeat me-1"></i> Sync Now</span>
                <span wire:loading wire:target="syncNow"><span class="spinner-border spinner-border-sm me-1"></span> Syncing...</span>
            </button>
        </div>
    </div>

    <!-- Flash message -->
    @if($feedbackMessage)
        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mb-4 py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Filter Bar Card -->
    <div class="card-frosted p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-semibold text-nowrap"><i class="bi bi-building me-1"></i> Client:</span>
                    <select wire:model.live="selectedClientId" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-semibold text-nowrap"><i class="bi bi-person-badge me-1"></i> Profile:</span>
                    <select wire:model.live="selectedProfileId" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                        <option value="">All Connected Profiles</option>
                        @foreach($profiles as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->platform->label() }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 text-end">
                @if($selectedClientId || $selectedProfileId)
                    <button wire:click="$set('selectedClientId', null); $set('selectedProfileId', null);" class="btn btn-sm btn-link text-muted p-0">
                        Reset Filters
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid (Pastel Palette) -->
    <div class="row g-3 mb-4">
        <!-- Impressions: Pastel Mint -->
        <div class="col-md-3">
            <div class="card-pastel-mint p-4 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold opacity-75 text-uppercase">Total Impressions</span>
                    <span class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">↗</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-dark">{{ number_format($kpis['impressions']) }}</h2>
                    <small class="opacity-75">Views across newsfeeds & reels</small>
                </div>
            </div>
        </div>

        <!-- Reach: Pastel Yellow -->
        <div class="col-md-3">
            <div class="card-pastel-yellow p-4 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold opacity-75 text-uppercase">Unique Reach</span>
                    <span class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">↗</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-dark">{{ number_format($kpis['reach']) }}</h2>
                    <small class="opacity-75">Unique accounts exposed</small>
                </div>
            </div>
        </div>

        <!-- Engagements & Rate: Pastel Pink -->
        <div class="col-md-3">
            <div class="card-pastel-pink p-4 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold opacity-75 text-uppercase">Engagements</span>
                    <span class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">↗</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-dark">{{ number_format($kpis['engagement']) }}</h2>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-white text-dark rounded-pill shadow-xs">{{ $kpis['engagementRate'] }}%</span>
                        <small class="opacity-75">Engagement Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Followers / Community: Pastel Lavender -->
        <div class="col-md-3">
            <div class="card-pastel-lavender p-4 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold opacity-75 text-uppercase">Audience Base</span>
                    <span class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">↗</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-dark">{{ number_format($kpis['followers']) }}</h2>
                    <small class="opacity-75">Tracked community followers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Daily Trends Line Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-activity text-primary me-2"></i> Performance Trends
                    </h6>
                    <span class="text-muted small">Daily Impressions vs. Unique Reach</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div 
                        x-data="analyticsTrendChart(@js($chartData))" 
                        x-init="initChart()"
                        wire:ignore
                        style="height: 280px; position: relative;"
                    >
                        <canvas id="analyticsTrendCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Distribution Doughnut Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart text-primary me-2"></i> Network Distribution
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-center">
                    <div 
                        x-data="platformDoughnutChart(@js($platformData))" 
                        x-init="initDoughnut()"
                        wire:ignore
                        style="height: 260px; width: 100%; position: relative;"
                    >
                        <canvas id="platformDoughnutCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Posts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-trophy text-warning me-2"></i> Top Performing Content
            </h6>
            <a href="{{ route('posts.index') }}" class="btn btn-sm btn-link text-decoration-none">View All Posts &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 320px;">Content</th>
                        <th>Network</th>
                        <th>Published Date</th>
                        <th class="text-end">Impressions</th>
                        <th class="text-end">Reach</th>
                        <th class="text-end">Reactions</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topVariants as $variant)
                        @php
                            $vPost = $variant->post;
                            $firstMedia = $variant->mediaAssets->first();
                            $vPlat = $variant->socialProfile?->platform;
                            $impr = (int) $variant->analyticsSnapshots->where('metric_name', 'impressions')->first()?->metric_value;
                            $rch = (int) $variant->analyticsSnapshots->where('metric_name', 'reach')->first()?->metric_value;
                            $lks = (int) $variant->analyticsSnapshots->where('metric_name', 'likes')->first()?->metric_value;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 44px; height: 44px;">
                                        @if($firstMedia)
                                            <img src="{{ $firstMedia->public_url }}" alt="Media" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <i class="bi bi-card-text text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="text-truncate" style="max-width: 240px;">
                                        <div class="fw-semibold text-dark text-truncate small mb-0">
                                            {{ Str::limit($variant->getEffectiveCaption(), 45) }}
                                        </div>
                                        @if($vPost->client)
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-building"></i> {{ $vPost->client->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light border text-dark p-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $vPlat?->icon() }} {{ $vPlat?->cssClass() }}"></i>
                                    {{ $variant->socialProfile?->name }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $variant->published_at?->format('M d, Y') ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end fw-semibold text-dark">{{ number_format($impr) }}</td>
                            <td class="text-end fw-semibold text-dark">{{ number_format($rch) }}</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($lks) }}</td>
                            <td class="text-end">
                                @if($variant->external_post_url)
                                    <a href="{{ $variant->external_post_url }}" target="_blank" class="btn btn-xs btn-outline-primary" title="View on Social Platform">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted small">
                                No published posts found in the selected date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 1. Line/Bar Trend Chart
    Alpine.data('analyticsTrendChart', (initialData) => ({
        chart: null,

        initChart() {
            const ctx = document.getElementById('analyticsTrendCanvas');
            if (!ctx || !window.Chart) return;

            this.chart = new window.Chart(ctx, {
                type: 'line',
                data: {
                    labels: initialData.labels,
                    datasets: [
                        {
                            label: 'Impressions',
                            data: initialData.impressions,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                        },
                        {
                            label: 'Unique Reach',
                            data: initialData.reach,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.05)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            window.addEventListener('analytics-updated', () => {
                location.reload(); // Simple smooth reload on range change
            });
        }
    }));

    // 2. Platform Share Doughnut Chart
    Alpine.data('platformDoughnutChart', (initialData) => ({
        chart: null,

        initDoughnut() {
            const ctx = document.getElementById('platformDoughnutCanvas');
            if (!ctx || !window.Chart) return;

            const labels = initialData.labels.length > 0 ? initialData.labels : ['No Data'];
            const totals = initialData.totals.length > 0 ? initialData.totals : [1];
            const colors = ['#1877F2', '#E1306C', '#0A66C2', '#FF0000', '#0F1419', '#BD081C'];

            this.chart = new window.Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: totals,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }));
});
</script>
@endpush

