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
    'labelFields' => [],
    'labelSeparator' => ' - ',
    'ajaxUrl' => null,
    'ajaxMethod' => 'GET',
    'ajaxDelay' => 250,
    'ajaxParams' => [],
    'queryParam' => 'q',
    'pageParam' => 'page',
    'resultsKey' => null,
    'dependsOn' => null,
    'dependsOnParam' => null,
    'preload' => null,
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
    $inputId =
        $id ?:
        \Illuminate\Support\Str::of($name)
            ->replace(['[', ']'], '-')
            ->trim('-')
            ->append('-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)));
    $inputName = $multiple && !\Illuminate\Support\Str::endsWith($name, '[]') ? $name . '[]' : $name;
    $errorKey = (string) \Illuminate\Support\Str::of($name)
        ->replace(['[]', '[', ']'], ['', '.', ''])
        ->trim('.');
    $selected = old($errorKey, $selected);

    $resolveOptionValue = function ($item, $path, $fallback = null) {
        if (blank($path)) {
            return $fallback;
        }

        if (is_object($item)) {
            $item = (array) $item;
        }

        if (!is_array($item)) {
            return $fallback;
        }

        return \Illuminate\Support\Arr::get($item, $path, $fallback);
    };

    $normalizeOption = function ($item, $index = null) use (
        $valueField,
        $textField,
        $labelFields,
        $labelSeparator,
        $resolveOptionValue,
    ) {
        if (is_object($item)) {
            $item = (array) $item;
        }

        if (is_array($item)) {
            $composedLabel = collect(\Illuminate\Support\Arr::wrap($labelFields))
                ->map(fn($field) => $resolveOptionValue($item, $field))
                ->filter(fn($value) => filled($value))
                ->implode($labelSeparator);

            return [
                'id' => $resolveOptionValue($item, $valueField, $item['id'] ?? ($item['value'] ?? $index)),
                'text' => filled($composedLabel) ?
                    $composedLabel :
                    $resolveOptionValue(
                        $item,
                        $textField,
                        $item['text'] ?? ($item['label'] ?? ($item['name'] ?? $index)),
                    ),
            ];
        }

        return [
            'id' => $index ?? $item,
            'text' => $item,
        ];
    };

    $normalizedOptions = collect($options)->map(fn($item, $index) => $normalizeOption($item, $index));

    $selectedItems = collect(\Illuminate\Support\Arr::wrap($selected))
        ->filter(fn($item) => $item !== null && $item !== '')
        ->map(function ($item, $index) use ($selectedLabel, $normalizeOption) {
            if (is_array($selectedLabel) && array_key_exists($index, $selectedLabel)) {
                return [
                    'id' => is_array($item) ? $item['id'] ?? ($item['value'] ?? $index) : $item,
                    'text' => $selectedLabel[$index],
                ];
            }

            if (!is_array($item) && !is_object($item) && $selectedLabel && !is_array($selectedLabel)) {
                return [
                    'id' => $item,
                    'text' => $selectedLabel,
                ];
            }

            return $normalizeOption($item, $index);
        });

    $renderOptions = $normalizedOptions->concat($selectedItems)->keyBy(fn($option) => (string) $option['id'])->values();

    $selectedValues = $selectedItems->pluck('id')->map(fn($value) => (string) $value)->all();

    if ($selected && empty($selectedValues) && !$multiple) {
        $selectedValues = [(string) $selected];
    }

    $shouldPreload = $preload ?? ($ajaxUrl && (int) $minimumInputLength === 0 && blank($dependsOn));

    $select2AjaxConfig = [
        'ajaxUrl' => $ajaxUrl,
        'ajaxMethod' => strtoupper($ajaxMethod),
        'ajaxDelay' => $ajaxDelay,
        'ajaxParams' => $ajaxParams,
        'queryParam' => $queryParam,
        'pageParam' => $pageParam,
        'resultsKey' => $resultsKey,
        'dependsOn' => $dependsOn,
        'dependsOnParam' => $dependsOnParam,
        'valueField' => $valueField,
        'textField' => $textField,
        'labelFields' => \Illuminate\Support\Arr::wrap($labelFields),
        'labelSeparator' => $labelSeparator,
        'debugLabel' => $inputName,
        'preload' => (bool) $shouldPreload,
    ];
@endphp

<div class="mb-3" {{ $attributes->only('wire:key') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif

    <select id="{{ $inputId }}" name="{{ $inputName }}" @disabled($disabled) @required($required)
        @if ($multiple) multiple @endif
        {{ $attributes->except(['wire:key'])->class(['form-select', 'is-invalid' => $errors->has($errorKey)]) }}
        data-control="select2" data-placeholder="{{ $placeholder }}"
        data-allow-clear="{{ $allowClear ? 'true' : 'false' }}" data-tags="{{ $tags ? 'true' : 'false' }}"
        data-search="{{ $search ? 'true' : 'false' }}" data-preload="{{ $shouldPreload ? 'true' : 'false' }}"
        data-minimum-input-length="{{ $minimumInputLength }}"
        data-value-field="{{ $valueField }}" data-text-field="{{ $textField }}"
        @if (!empty(\Illuminate\Support\Arr::wrap($labelFields))) data-label-fields='@json(\Illuminate\Support\Arr::wrap($labelFields))' @endif
        data-label-separator="{{ $labelSeparator }}"
        @if ($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
        @if ($ajaxMethod) data-ajax-method="{{ strtoupper($ajaxMethod) }}" @endif
        @if ($ajaxDelay) data-ajax-delay="{{ $ajaxDelay }}" @endif
        @if ($queryParam) data-query-param="{{ $queryParam }}" @endif
        @if ($pageParam) data-page-param="{{ $pageParam }}" @endif
        @if ($resultsKey) data-results-key="{{ $resultsKey }}" @endif
        @if ($dependsOn) data-depends-on="{{ $dependsOn }}" @endif
        @if ($dependsOnParam) data-depends-on-param="{{ $dependsOnParam }}" @endif
        @if (!empty($ajaxParams)) data-ajax-params='@json($ajaxParams)' @endif>
        @if (!$multiple)
            <option value=""></option>
        @endif

        @foreach ($renderOptions as $option)
            <option value="{{ $option['id'] }}" @selected(in_array((string) $option['id'], $selectedValues, true))>
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

<script>
    (() => {
        const elementId = {{ \Illuminate\Support\Js::from($inputId) }};
        const config = {{ \Illuminate\Support\Js::from($select2AjaxConfig) }};

            const readBoolean = (value, fallback = true) => {
                if (value === undefined || value === null || value === '') {
                    return fallback;
                }

                return !['0', 'false', 'no', 'off'].includes(String(value).toLowerCase());
            };

            const getNestedValue = (object, path, fallback = undefined) => {
                if (!object || !path) {
                    return fallback;
                }

                return path.split('.').reduce((current, key) => {
                    if (current === undefined || current === null) {
                        return undefined;
                    }

                    return current[key];
                }, object) ?? fallback;
            };

            const normalizeItems = (response) => {
                const resolveItemValue = (item, path, fallback = undefined) => {
                    if (!path) {
                        return fallback;
                    }

                    const resolved = getNestedValue(item, path, undefined);

                    return resolved === undefined || resolved === null || resolved === '' ? fallback : resolved;
                };

                let items = [];

                if (Array.isArray(response)) {
                    items = response;
                } else if (config.resultsKey) {
                    items = getNestedValue(response, config.resultsKey, []);
                } else {
                    items = response?.results ??
                        (Array.isArray(response?.data) ? response.data : response?.data?.data) ??
                        response?.items ??
                        [];
                }

                return (Array.isArray(items) ? items : []).map((item) => {
                    const composedLabel = Array.isArray(config.labelFields) && config.labelFields.length > 0 ?
                        config.labelFields
                        .map((field) => resolveItemValue(item, field))
                        .filter((value) => value !== undefined && value !== null && value !== '')
                        .join(config.labelSeparator ?? ' - ') :
                        '';

                    return {
                        ...item,
                        id: resolveItemValue(item, config.valueField, item?.id ?? item?.value),
                        text: composedLabel || resolveItemValue(
                            item,
                            config.textField,
                            item?.text ?? item?.name ?? item?.label ?? '',
                        ),
                    };
                }).filter((item) => item.id !== undefined && item.id !== null && item.id !== '');
            };

            const resolveDependency = () => {
                if (!config.dependsOn) {
                    return {
                        element: null,
                        key: null,
                        value: null,
                    };
                }

                const dependentElement = document.querySelector(config.dependsOn);

                return {
                    element: dependentElement,
                    key: config.dependsOnParam ?? dependentElement?.name ?? 'parent_id',
                    value: dependentElement?.value ?? '',
                };
            };

            const snapshotSelectedOptions = (element) =>
                Array.from(element.selectedOptions ?? [])
                .filter((option) => option.value !== '')
                .map((option) => ({
                    value: String(option.value),
                    text: option.text,
                }));

            const replaceOptions = (element, items, preserveSelection = true) => {
                const selectedOptions = preserveSelection ? snapshotSelectedOptions(element) : [];
                const selectedValues = new Set(selectedOptions.map((option) => option.value));
                const appendedValues = new Set();

                element.innerHTML = '';

                if (!element.multiple) {
                    element.add(new Option('', '', false, false));
                }

                items.forEach((item) => {
                    const value = String(item.id ?? '');

                    if (!value || appendedValues.has(value)) {
                        return;
                    }

                    appendedValues.add(value);
                    element.add(new Option(item.text ?? value, value, false, selectedValues.has(value)));
                });

                selectedOptions.forEach((option) => {
                    if (appendedValues.has(option.value)) {
                        return;
                    }

                    element.add(new Option(option.text, option.value, false, true));
                });

                if (!element.multiple) {
                    element.value = selectedOptions[0]?.value ?? '';
                }
            };

            const boot = () => {
                const element = document.getElementById(elementId);

                if (!element || typeof window.fetch !== 'function') {
                    window.setTimeout(boot, 50);
                    return;
                }

                if (element.dataset.selectComponentReady === 'true') {
                    return;
                }

                element.dataset.selectComponentReady = 'true';
                element.dataset.baseDisabled = element.disabled ? 'true' : 'false';

                const select2Config = {
                    width: element.dataset.width ?? '100%',
                    placeholder: element.dataset.placeholder ?? '',
                    tags: readBoolean(element.dataset.tags, false),
                    minimumResultsForSearch: readBoolean(element.dataset.search, true) ? 0 : Infinity,
                    allowClear: readBoolean(element.dataset.allowClear, true),
                };

                const getJQueryElement = () => {
                    const $ = window.jQuery ?? null;

                    return $ ? $(element) : null;
                };

                const initializeSelect2 = () => {
                    const $ = window.jQuery ?? null;

                    if (!$?.fn?.select2) {
                        return false;
                    }

                    const $element = $(element);
                    const dropdownParent = $element.closest('.modal').length ? $element.closest('.modal') : $(document.body);

                    if (!$element.data('select2')) {
                        $element.select2({
                            ...select2Config,
                            dropdownParent,
                        });

                        return true;
                    }

                    $element.trigger('change.select2');

                    return true;
                };

                const refreshSelect2 = () => {
                    initializeSelect2();
                };

                const ensureSelect2Initialized = (attempt = 0) => {
                    if (initializeSelect2() || attempt >= 100) {
                        return;
                    }

                    window.setTimeout(() => ensureSelect2Initialized(attempt + 1), 100);
                };

                const loadOptions = async ({
                    force = false,
                    preserveSelection = true
                } = {}) => {
                    if (!config.ajaxUrl) {
                        refreshSelect2();
                        return;
                    }

                    const dependency = resolveDependency();

                    if (dependency.element && !dependency.value) {
                        replaceOptions(element, [], false);
                        element.disabled = true;
                        element.dataset.loadedKey = '';
                        refreshSelect2();
                        return;
                    }

                    const params = {
                        ...(config.ajaxParams ?? {}),
                    };

                    if (config.queryParam) {
                        params[config.queryParam] = '';
                    }

                    if (config.pageParam) {
                        params[config.pageParam] = 1;
                    }

                    if (dependency.key && dependency.value) {
                        params[dependency.key] = dependency.value;
                    }

                    const requestKey = JSON.stringify(params);

                    if (!force && element.dataset.loadedKey === requestKey) {
                        refreshSelect2();
                        return;
                    }

                    console.groupCollapsed(`[Select2] ${config.debugLabel} request`);
                    console.log('config', {
                        url: config.ajaxUrl,
                        method: config.ajaxMethod,
                        params,
                    });

                    try {
                        const requestUrl = new URL(config.ajaxUrl, window.location.origin);
                        Object.entries(params).forEach(([key, value]) => {
                            if (value === undefined || value === null) {
                                return;
                            }

                            requestUrl.searchParams.set(key, value);
                        });

                        console.log('requestUrl', requestUrl.toString());

                        const response = await fetch(requestUrl.toString(), {
                            method: config.ajaxMethod ?? 'GET',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            cache: 'no-store',
                        });

                        const payload = await response.json().catch(() => null);

                        if (!response.ok) {
                            throw payload ?? new Error(`HTTP ${response.status}`);
                        }

                        const items = normalizeItems(payload);

                        console.log('response', payload);
                        console.log('normalized results', items);

                        replaceOptions(element, items, preserveSelection);
                        element.dataset.loadedKey = requestKey;
                        element.disabled = readBoolean(element.dataset.baseDisabled, false);
                        refreshSelect2();
                    } catch (error) {
                        console.error('error', error?.response?.data ?? error);
                    } finally {
                        console.groupEnd();
                    }
                };

                ensureSelect2Initialized();

                const dependency = resolveDependency();

                if (dependency.element) {
                    const syncDependency = () => {
                        if (!dependency.element.value) {
                            replaceOptions(element, [], false);
                            element.disabled = true;
                            element.dataset.loadedKey = '';
                            const $element = getJQueryElement();

                            if ($element) {
                                $element.val(null).trigger('change');
                            } else {
                                element.value = '';
                            }
                            return;
                        }

                        void loadOptions({
                            force: true,
                            preserveSelection: true
                        });
                    };

                    dependency.element.addEventListener('change', syncDependency);
                    syncDependency();
                    return;
                }

                if (readBoolean(element.dataset.preload, false)) {
                    void loadOptions({
                        force: true,
                        preserveSelection: true
                    });
                    return;
                }

                if (config.ajaxUrl) {
                    const bindOpenHandler = () => {
                        const $element = getJQueryElement();

                        if (!$element?.data('select2')) {
                            window.setTimeout(bindOpenHandler, 100);
                            return;
                        }

                        $element.on('select2:open', () => {
                            if (element.dataset.loadedKey) {
                                return;
                            }

                            void loadOptions({
                                force: true,
                                preserveSelection: true
                            });
                        });
                    };

                    bindOpenHandler();
                }
            };

        boot();
    })();
</script>
