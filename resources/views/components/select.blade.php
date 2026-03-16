@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => 'Pilih data',
    'options' => [],
    'selected' => null,
    'selectedLabel' => null,
    'valueField' => 'id',
    'textField' => 'text',
    'ajaxUrl' => null,
    'ajaxMethod' => 'GET',
    'ajaxDelay' => 250,
    'ajaxParams' => [],
    'queryParam' => 'q',
    'pageParam' => 'page',
    'resultsKey' => null,
    'minimumInputLength' => 0,
    'allowClear' => true,
    'multiple' => false,
    'disabled' => false,
    'required' => false,
    'tags' => false,
    'search' => true,
    'hint' => null,
])

@php
    $inputId = $id ?: \Illuminate\Support\Str::of($name)->replace(['[', ']'], '-')->trim('-')->append('-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)));
    $inputName = $multiple && ! \Illuminate\Support\Str::endsWith($name, '[]') ? $name.'[]' : $name;
    $errorKey = (string) \Illuminate\Support\Str::of($name)->replace(['[]', '[', ']'], ['', '.', ''])->trim('.');
    $selected = old($errorKey, $selected);

    $normalizeOption = function ($item, $index = null) use ($valueField, $textField) {
        if (is_object($item)) {
            $item = (array) $item;
        }

        if (is_array($item)) {
            return [
                'id' => $item[$valueField] ?? $item['id'] ?? $item['value'] ?? $index,
                'text' => $item[$textField] ?? $item['text'] ?? $item['label'] ?? $item['name'] ?? $index,
            ];
        }

        return [
            'id' => $index ?? $item,
            'text' => $item,
        ];
    };

    $normalizedOptions = collect($options)
        ->map(fn ($item, $index) => $normalizeOption($item, $index));

    $selectedItems = collect(\Illuminate\Support\Arr::wrap($selected))
        ->filter(fn ($item) => $item !== null && $item !== '')
        ->map(function ($item, $index) use ($selectedLabel, $normalizeOption) {
            if (is_array($selectedLabel) && array_key_exists($index, $selectedLabel)) {
                return [
                    'id' => is_array($item) ? ($item['id'] ?? $item['value'] ?? $index) : $item,
                    'text' => $selectedLabel[$index],
                ];
            }

            if (! is_array($item) && ! is_object($item) && $selectedLabel && ! is_array($selectedLabel)) {
                return [
                    'id' => $item,
                    'text' => $selectedLabel,
                ];
            }

            return $normalizeOption($item, $index);
        });

    $renderOptions = $normalizedOptions
        ->concat($selectedItems)
        ->keyBy(fn ($option) => (string) $option['id'])
        ->values();

    $selectedValues = $selectedItems
        ->pluck('id')
        ->map(fn ($value) => (string) $value)
        ->all();

    if ($selected && empty($selectedValues) && ! $multiple) {
        $selectedValues = [(string) $selected];
    }
@endphp

<div class="mb-3" {{ $attributes->only('wire:key') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif

    <select
        id="{{ $inputId }}"
        name="{{ $inputName }}"
        @disabled($disabled)
        @required($required)
        @if ($multiple) multiple @endif
        {{ $attributes
            ->except(['wire:key'])
            ->class(['form-select', 'is-invalid' => $errors->has($errorKey)]) }}
        data-control="select2"
        data-placeholder="{{ $placeholder }}"
        data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
        data-tags="{{ $tags ? 'true' : 'false' }}"
        data-search="{{ $search ? 'true' : 'false' }}"
        data-minimum-input-length="{{ $minimumInputLength }}"
        data-value-field="{{ $valueField }}"
        data-text-field="{{ $textField }}"
        @if ($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
        @if ($ajaxMethod) data-ajax-method="{{ strtoupper($ajaxMethod) }}" @endif
        @if ($ajaxDelay) data-ajax-delay="{{ $ajaxDelay }}" @endif
        @if ($queryParam) data-query-param="{{ $queryParam }}" @endif
        @if ($pageParam) data-page-param="{{ $pageParam }}" @endif
        @if ($resultsKey) data-results-key="{{ $resultsKey }}" @endif
        @if (! empty($ajaxParams)) data-ajax-params='@json($ajaxParams)' @endif
    >
        @if (! $multiple)
            <option value=""></option>
        @endif

        @foreach ($renderOptions as $option)
            <option
                value="{{ $option['id'] }}"
                @selected(in_array((string) $option['id'], $selectedValues, true))
            >
                {{ $option['text'] }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
