@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'placeholder' => 'Pilih tanggal',
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'wrapperClass' => null,
    'labelClass' => null,
    'format' => 'YYYY-MM-DD',
    'displayFormat' => null,
    'separator' => ' - ',
    'single' => false,
    'timePicker' => false,
    'timePicker24Hour' => false,
    'timePickerSeconds' => false,
    'autoApply' => false,
    'autoUpdateInput' => true,
    'showDropdowns' => true,
    'showCustomRangeLabel' => true,
    'showRanges' => null,
    'opens' => 'left',
    'drops' => 'auto',
    'minDate' => null,
    'maxDate' => null,
    'startDate' => null,
    'endDate' => null,
    'ranges' => null,
])

@php
    $inputId =
        $id ?:
        \Illuminate\Support\Str::of($name)
            ->replace(['[', ']'], '-')
            ->trim('-')
            ->append('-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)));
    $errorKey = (string) \Illuminate\Support\Str::of($name)
        ->replace(['[]', '[', ']'], ['', '.', ''])
        ->trim('.');
    $fieldValue = old($errorKey, $value);
    $wrapperClasses = collect(['mb-3', $wrapperClass])
        ->filter()
        ->implode(' ');
    $usePresetRanges = $showRanges ?? !$single;
    $resolvedDisplayFormat = $displayFormat ?: $format;
@endphp

<div class="{{ $wrapperClasses }}" {{ $attributes->only('wire:key') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="{{ collect(['form-label', $labelClass])->filter()->implode(' ') }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <input id="{{ $inputId }}" type="text" name="{{ $name }}" @disabled($disabled)
        @readonly($readonly) @required($required) @autofocus($autofocus)
        @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
        @if ($fieldValue !== null) value="{{ $fieldValue }}" @endif data-control="daterangepicker"
        data-format="{{ $format }}" data-display-format="{{ $resolvedDisplayFormat }}"
        data-separator="{{ $separator }}" data-single-date-picker="{{ $single ? 'true' : 'false' }}"
        data-time-picker="{{ $timePicker ? 'true' : 'false' }}"
        data-time-picker-24-hour="{{ $timePicker24Hour ? 'true' : 'false' }}"
        data-time-picker-seconds="{{ $timePickerSeconds ? 'true' : 'false' }}"
        data-auto-apply="{{ $autoApply ? 'true' : 'false' }}"
        data-auto-update-input="{{ $autoUpdateInput ? 'true' : 'false' }}"
        data-show-dropdowns="{{ $showDropdowns ? 'true' : 'false' }}"
        data-show-custom-range-label="{{ $showCustomRangeLabel ? 'true' : 'false' }}"
        data-show-ranges="{{ $usePresetRanges ? 'true' : 'false' }}" data-opens="{{ $opens }}"
        data-drops="{{ $drops }}" @if ($minDate) data-min-date="{{ $minDate }}" @endif
        @if ($maxDate) data-max-date="{{ $maxDate }}" @endif
        @if ($startDate) data-start-date="{{ $startDate }}" @endif
        @if ($endDate) data-end-date="{{ $endDate }}" @endif
        @if ($ranges) data-ranges='@json($ranges)' @endif
        {{ $attributes->except(['wire:key'])->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }}>

    @if ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
