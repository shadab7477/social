@props(['status' => 'draft', 'label' => null])

@php
    $statusClass = match(strtolower($status)) {
        'published', 'connected', 'active', 'approved' => 'published',
        'scheduled', 'queued' => 'scheduled',
        'pending_approval', 'reauthorization_required', 'paused' => 'pending_approval',
        'failed', 'expired', 'error', 'rejected' => 'failed',
        'partially_published' => 'partially_published',
        default => 'draft',
    };

    $displayLabel = $label ?? str_replace('_', ' ', ucfirst($status));
@endphp

<span {{ $attributes->merge(['class' => "badge badge-status {$statusClass}"]) }}>
    {{ $displayLabel }}
</span>

