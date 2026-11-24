@props([
    'id',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
    'footer' => null,
])

@php
    $modalSize = match($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog {{ $modalSize }} modal-dialog-centered">
        <div class="modal-content shadow">
            @if ($title)
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if ($footer)
                <div class="modal-footer bg-light py-2">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

