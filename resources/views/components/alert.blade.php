@props(['type' => 'info', 'dismissible' => true, 'icon' => null])

@php
    $defaultIcon = match($type) {
        'success' => 'bi-check-circle-fill',
        'danger', 'error' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default => 'bi-info-circle-fill',
    };
    $alertType = ($type === 'error') ? 'danger' : $type;
    $iconClass = $icon ?? $defaultIcon;
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$alertType} d-flex align-items-center gap-2" . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    <i class="bi {{ $iconClass }} fs-5 flex-shrink-0"></i>
    <div class="flex-grow-1">
        {{ $slot }}
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>

