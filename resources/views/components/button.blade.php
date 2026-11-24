@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loadingTarget' => null,
])

@php
    $sizeClass = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };
@endphp

<button {{ $attributes->merge(['class' => "btn btn-{$variant} {$sizeClass} d-inline-flex align-items-center gap-2"]) }}
    @if ($loadingTarget) wire:loading.attr="disabled" wire:target="{{ $loadingTarget }}" @endif>
    
    @if ($loadingTarget)
        <span wire:loading wire:target="{{ $loadingTarget }}" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    @endif

    @if ($icon)
        <i class="{{ $icon }}" @if($loadingTarget) wire:loading.remove wire:target="{{ $loadingTarget }}" @endif></i>
    @endif

    <span>{{ $slot }}</span>
</button>

