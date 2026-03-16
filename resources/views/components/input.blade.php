@props([
    'name',
    'id' => null,
    'type' => 'text',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'prefix' => null,
    'suffix' => null,
    'size' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'wrapperClass' => null,
    'labelClass' => null,
])

@php
    $inputId = $id ?: \Illuminate\Support\Str::of($name)->replace(['[', ']'], '-')->trim('-')->append('-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)));
    $errorKey = (string) \Illuminate\Support\Str::of($name)->replace(['[]', '[', ']'], ['', '.', ''])->trim('.');
    $fieldValue = in_array($type, ['password', 'file'], true) ? null : old($errorKey, $value);
    $hasGroup = $prefix || $suffix || isset($prepend) || isset($append);
    $wrapperClasses = collect(['mb-3', $wrapperClass])->filter()->implode(' ');
@endphp

@if ($type === 'hidden')
    <input
        type="hidden"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ $fieldValue }}"
        {{ $attributes->except(['wire:key']) }}
    >
@else
    <div class="{{ $wrapperClasses }}" {{ $attributes->only('wire:key') }}>
        @if ($label)
            <label for="{{ $inputId }}" class="{{ collect(['form-label', $labelClass])->filter()->implode(' ') }}">
                {{ $label }}
                @if ($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
        @endif

        @if ($hasGroup)
            <div class="input-group">
                @if ($prefix)
                    <span class="input-group-text">{{ $prefix }}</span>
                @endif

                @isset($prepend)
                    {{ $prepend }}
                @endisset

                <input
                    id="{{ $inputId }}"
                    type="{{ $type }}"
                    name="{{ $name }}"
                    @disabled($disabled)
                    @readonly($readonly)
                    @required($required)
                    @autofocus($autofocus)
                    @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
                    @if ($fieldValue !== null) value="{{ $fieldValue }}" @endif
                    {{ $attributes
                        ->except(['wire:key'])
                        ->class([
                            'form-control',
                            'form-control-'.$size => filled($size),
                            'is-invalid' => $errors->has($errorKey),
                        ]) }}
                >

                @isset($append)
                    {{ $append }}
                @endisset

                @if ($suffix)
                    <span class="input-group-text">{{ $suffix }}</span>
                @endif
            </div>
        @else
            <input
                id="{{ $inputId }}"
                type="{{ $type }}"
                name="{{ $name }}"
                @disabled($disabled)
                @readonly($readonly)
                @required($required)
                @autofocus($autofocus)
                @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
                @if ($fieldValue !== null) value="{{ $fieldValue }}" @endif
                {{ $attributes
                    ->except(['wire:key'])
                    ->class([
                        'form-control',
                        'form-control-'.$size => filled($size),
                        'is-invalid' => $errors->has($errorKey),
                    ]) }}
            >
        @endif

        @if ($hint)
            <div class="form-text">{{ $hint }}</div>
        @endif

        @error($errorKey)
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
@endif
