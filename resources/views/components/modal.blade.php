@props([
    'id' => 'modal-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
    'title' => null,
    'size' => null,
    'centered' => false,
    'scrollable' => false,
    'closable' => true,
    'showFooter' => true,
    'backdrop' => 'true',
    'keyboard' => true,
])

@php
    $dialogClasses = collect([
        'modal-dialog',
        $size ? 'modal-'.$size : null,
        $centered ? 'modal-dialog-centered' : null,
        $scrollable ? 'modal-dialog-scrollable' : null,
    ])->filter()->implode(' ');

    $labelId = $id.'-label';
@endphp

<div
    {{ $attributes->class(['modal', 'fade'])->merge([
        'id' => $id,
        'tabindex' => '-1',
        'aria-labelledby' => $labelId,
        'aria-hidden' => 'true',
        'data-bs-backdrop' => $backdrop,
        'data-bs-keyboard' => $keyboard ? 'true' : 'false',
    ]) }}
>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content border-0 shadow">
            @if ($title || isset($header) || $closable)
                <div class="modal-header">
                    <div>
                        @isset($header)
                            {{ $header }}
                        @else
                            <h1 class="modal-title fs-5" id="{{ $labelId }}">{{ $title }}</h1>
                        @endisset
                    </div>

                    @if ($closable)
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if (isset($footer) || $showFooter)
                <div class="modal-footer">
                    @isset($footer)
                        {{ $footer }}
                    @else
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    @endisset
                </div>
            @endif
        </div>
    </div>
</div>
