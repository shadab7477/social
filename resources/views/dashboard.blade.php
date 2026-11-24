@extends('layouts.app', ['title' => 'Dashboard', 'headerTitle' => 'Workspace Overview'])

@section('content')
<div class="row g-3 mb-4">
    <!-- Stat 1 -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Connected Accounts</span>
                    <h3 class="fw-bold mb-0 mt-1">12</h3>
                    <span class="text-success small fw-medium"><i class="bi bi-arrow-up-right"></i> 100% Operational</span>
                </div>
                <div class="bg-primary-subtle text-primary rounded-3 p-3">
                    <i class="bi bi-share-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Scheduled Posts</span>
                    <h3 class="fw-bold mb-0 mt-1">28</h3>
                    <span class="text-indigo small fw-medium"><i class="bi bi-clock-history"></i> Next: in 45 mins</span>
                </div>
                <div class="bg-info-subtle text-info rounded-3 p-3">
                    <i class="bi bi-calendar3 fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Pending Approvals</span>
                    <h3 class="fw-bold mb-0 mt-1">5</h3>
                    <span class="text-warning small fw-medium"><i class="bi bi-hourglass-split"></i> 2 Client Reviews</span>
                </div>
                <div class="bg-warning-subtle text-warning rounded-3 p-3">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Total Audience Reach</span>
                    <h3 class="fw-bold mb-0 mt-1">142.8k</h3>
                    <span class="text-success small fw-medium"><i class="bi bi-arrow-up-right"></i> +14.2% this month</span>
                </div>
                <div class="bg-success-subtle text-success rounded-3 p-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions & Publishing Pipeline -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-send-check text-primary me-2"></i>Upcoming Queue (Redis Worker Status)</h6>
                <a href="{{ route('calendar') }}" class="btn btn-sm btn-outline-primary">Open Calendar</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-3">Campaign / Post</th>
                                <th>Target Client</th>
                                <th>Platforms</th>
                                <th>Scheduled Time</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;">Weekend Brunch Announcement</div>
                                    <span class="text-muted small text-truncate d-block" style="max-width: 200px;">"Hand-crafted desserts & live acoustic tunes..."</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">Sharma Restaurant</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <span class="platform-icon facebook" style="width:24px;height:24px;font-size:0.75rem;"><i class="bi bi-facebook"></i></span>
                                        <span class="platform-icon instagram" style="width:24px;height:24px;font-size:0.75rem;"><i class="bi bi-instagram"></i></span>
                                    </div>
                                </td>
                                <td class="small">Today at 6:30 PM <span class="text-muted">(UTC)</span></td>
                                <td><x-badge status="scheduled" /></td>
                                <td class="pe-3 text-end">
                                    <a href="#" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;">Summer Collection Teaser</div>
                                    <span class="text-muted small text-truncate d-block" style="max-width: 200px;">"Sneak peek at our sustainable cotton linen..."</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">Fashion Store</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <span class="platform-icon instagram" style="width:24px;height:24px;font-size:0.75rem;"><i class="bi bi-instagram"></i></span>
                                        <span class="platform-icon pinterest" style="width:24px;height:24px;font-size:0.75rem;"><i class="bi bi-pin-angle-fill"></i></span>
                                    </div>
                                </td>
                                <td class="small">Tomorrow at 10:00 AM</td>
                                <td><x-badge status="approved" /></td>
                                <td class="pe-3 text-end">
                                    <a href="#" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Health Status -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-activity text-success me-2"></i>Platform Health</h6>
                <a href="{{ route('social-accounts.index') }}" class="small text-decoration-none">Manage</a>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon facebook" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-facebook"></i></span>
                            <span class="fw-semibold">Facebook Pages</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon instagram" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-instagram"></i></span>
                            <span class="fw-semibold">Instagram Professional</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon linkedin" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-linkedin"></i></span>
                            <span class="fw-semibold">LinkedIn Company</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon x" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-twitter-x"></i></span>
                            <span class="fw-semibold">X (Twitter v2)</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="platform-icon youtube" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-youtube"></i></span>
                            <span class="fw-semibold">YouTube Channels</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

