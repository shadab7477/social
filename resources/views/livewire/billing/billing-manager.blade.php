<div class="billing-wrapper">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-credit-card-2-front me-2 text-primary"></i> Billing &amp; Plans
            </h4>
            <p class="text-muted small mb-0">Manage your subscription tier, workspace quotas, and monthly resource usage.</p>
        </div>

        <div class="btn-group bg-white p-1 rounded-pill border shadow-xs" role="group">
            <button type="button" wire:click="toggleCycle('monthly')" class="btn btn-sm rounded-pill px-3 {{ $billingCycle === 'monthly' ? 'btn-dark' : 'btn-light border-0 bg-transparent' }}">Monthly</button>
            <button type="button" wire:click="toggleCycle('annual')" class="btn btn-sm rounded-pill px-3 {{ $billingCycle === 'annual' ? 'btn-dark' : 'btn-light border-0 bg-transparent' }}">Annual <span class="badge bg-success ms-1">-18%</span></button>
        </div>
    </div>

    @if ($feedbackMessage)
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Usage KPIs & Quota Chart Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Current Plan Usage ({{ $activePlan['name'] }})</h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Active</span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Connected Social Profiles</span>
                            <span>{{ $usage['profiles'] }} / {{ $activePlan['profiles'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ min(100, round(($usage['profiles'] / max(1, $activePlan['profiles'])) * 100)) }}%;"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Team Members</span>
                            <span>{{ $usage['members'] }} / {{ $activePlan['members'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ min(100, round(($usage['members'] / max(1, $activePlan['members'])) * 100)) }}%;"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Monthly AI Assistant Requests</span>
                            <span>{{ $usage['ai_requests'] }} / {{ $activePlan['ai_limit'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ min(100, round(($usage['ai_requests'] / max(1, $activePlan['ai_limit'])) * 100)) }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Media Storage Used</span>
                            <span>{{ $usage['storage_mb'] }} MB / {{ $activePlan['storage_mb'] }} MB</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ min(100, round(($usage['storage_mb'] / max(1, $activePlan['storage_mb'])) * 100)) }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">Resource Distribution Chart</h6>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center" wire:ignore>
                    <canvas id="billingUsageChart" style="max-height: 220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Plan Cards -->
    <div class="row g-4">
        @foreach ($plans as $planKey => $plan)
            @php
                $isCurrent = $selectedPlan === $planKey;
                $price = $billingCycle === 'annual' ? $plan['annual'] : $plan['monthly'];
            @endphp
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 {{ $isCurrent ? 'border border-2 border-dark' : '' }}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $plan['name'] }}</h5>
                            <span class="badge {{ $isCurrent ? 'bg-dark text-white' : 'bg-light text-muted border' }} rounded-pill">{{ $isCurrent ? 'Current Plan' : $plan['badge'] }}</span>
                        </div>
                        <div class="my-3">
                            <span class="fs-2 fw-bold text-dark">${{ $price }}</span>
                            <span class="text-muted small">/ month</span>
                        </div>
                        <ul class="list-unstyled small text-muted flex-grow-1 mb-4 d-flex flex-column gap-2">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Up to {{ $plan['profiles'] }} Social Profiles</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $plan['members'] }} Team Seats</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ number_format($plan['ai_limit']) }} AI Captions / mo</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ round($plan['storage_mb'] / 1024, 1) }} GB Media Vault</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>White-Label PDF &amp; CSV Reports</li>
                        </ul>
                        <button type="button" wire:click="selectPlan('{{ $planKey }}')"
                            class="btn rounded-pill py-2 fw-semibold {{ $isCurrent ? 'btn-light border text-dark' : 'btn-dark' }}"
                            @if($isCurrent) disabled @endif>
                            {{ $isCurrent ? 'Active Plan' : 'Switch to ' . $plan['name'] }}
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:navigated', renderBillingChart);
document.addEventListener('DOMContentLoaded', renderBillingChart);

function renderBillingChart() {
    const ctx = document.getElementById('billingUsageChart');
    if (!ctx || !window.Chart) return;
    if (ctx._chartInstance) ctx._chartInstance.destroy();
    ctx._chartInstance = new window.Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Profiles', 'Team Members', 'Monthly Posts', 'AI Requests'],
            datasets: [{
                data: [{{ $usage['profiles'] }}, {{ $usage['members'] }}, {{ $usage['posts'] }}, {{ max(1, $usage['ai_requests']) }}],
                backgroundColor: ['#3b82f6', '#10b981', '#0f172a', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
}
</script>
@endpush

