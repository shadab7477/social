@props(['target' => null, 'text' => 'Loading...'])

<div wire:loading @if($target) wire:target="{{ $target }}" @endif class="text-center py-4">
    <div class="spinner-border text-primary" role="status" style="width: 2.25rem; height: 2.25rem;">
        <span class="visually-hidden">{{ $text }}</span>
    </div>
    <div class="text-muted small mt-2 fw-medium">{{ $text }}</div>
</div>

