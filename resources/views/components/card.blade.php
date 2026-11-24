@props(['title' => null, 'actions' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || $actions)
        <div class="card-header d-flex align-items-center justify-content-between">
            @if ($title)
                <h5 class="card-title mb-0 fs-6 fw-semibold text-dark">{{ $title }}</h5>
            @endif
            @if ($actions)
                <div class="card-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="card-footer bg-light border-top border-light-subtle py-2 px-3">
            {{ $footer }}
        </div>
    @endif
</div>

