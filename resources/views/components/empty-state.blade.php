@props([
    'icon' => 'bi-inbox',
    'title' => 'No items found',
    'description' => 'There are no records to display at this time.',
    'action' => null,
])

<div class="text-center py-5 px-3">
    <div class="d-inline-flex align-items-center justify-content-center bg-light text-secondary rounded-circle mb-3 p-3" style="width: 64px; height: 64px;">
        <i class="bi {{ $icon }} fs-2"></i>
    </div>
    <h5 class="fw-semibold text-dark mb-1">{{ $title }}</h5>
    <p class="text-muted small mb-3" style="max-width: 400px; margin: 0 auto;">{{ $description }}</p>
    @if ($action)
        <div>
            {{ $action }}
        </div>
    @endif
</div>

