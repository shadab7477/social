@extends('layouts.public')

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <div class="bg-light p-5 rounded-4 shadow-sm border">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-check fs-2"></i>
                </div>

                <h2 class="fw-bold text-dark mb-2">User Data Deletion Request Status</h2>
                <p class="text-muted mb-4">PostSync complies strictly with Meta Platform Data Deletion requirements.</p>

                @php
                    $code = request('code');
                    $record = $code ? \Illuminate\Support\Facades\Cache::get("meta_deletion_{$code}") : null;
                @endphp

                @if ($code)
                    <div class="alert alert-success border-0 py-3 mb-4 text-start">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-bold">Deletion Completed</span>
                        </div>
                        <div class="small text-secondary">
                            <strong>Confirmation Code:</strong> <code>{{ $code }}</code><br>
                            <strong>Status:</strong> All authentication tokens, cached profile data, and associated user identifiers have been permanently purged from PostSync.<br>
                            <strong>Timestamp:</strong> {{ $record['requested_at'] ?? now()->toIso8601String() }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-info border-0 py-3 mb-4 text-start">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                            <span class="fw-bold">Track Your Deletion Request</span>
                        </div>
                        <p class="small text-secondary mb-0">
                            If you initiated a data deletion request through Facebook, enter your confirmation code in the URL query (e.g. <code>?code=YOUR_CODE</code>) to review the deletion timestamp.
                        </p>
                    </div>
                @endif

                <a href="{{ route('home') }}" class="btn btn-primary px-4">Return to PostSync Homepage</a>
            </div>
        </div>
    </div>
</div>
@endsection

