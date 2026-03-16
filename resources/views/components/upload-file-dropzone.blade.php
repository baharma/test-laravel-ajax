@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'hint' => null,
    'required' => false,
    'folder' => 'uploads',
    'uploadUrl' => route('upload.file'),
    'paramName' => 'file',
    'acceptedFiles' => 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip',
    'maxFiles' => 1,
    'maxFilesize' => 10,
    'wrapperClass' => null,
    'labelClass' => null,
    'dropMessage' => 'Drop file di sini atau klik untuk upload',
])

@php
    $inputId =
        $id ?:
        \Illuminate\Support\Str::of($name)
            ->replace(['[', ']'], '-')
            ->trim('-')
            ->append('-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)));
    $dropzoneId = $inputId . '-dropzone';
    $fileLinkId = $inputId . '-file-link';
    $errorKey = (string) \Illuminate\Support\Str::of($name)
        ->replace(['[]', '[', ']'], ['', '.', ''])
        ->trim('.');
    $fieldValue = old($errorKey, $value);
    $wrapperClasses = collect(['mb-3', $wrapperClass])
        ->filter()
        ->implode(' ');
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

    <input
        id="{{ $inputId }}"
        type="hidden"
        name="{{ $name }}"
        @if ($fieldValue !== null) value="{{ $fieldValue }}" @endif
    >

    <div
        id="{{ $dropzoneId }}"
        {{ $attributes
            ->except(['wire:key'])
            ->class([
                'dropzone',
                'is-invalid' => $errors->has($errorKey),
            ]) }}
        data-control="dropzone"
        data-url="{{ $uploadUrl }}"
        data-param-name="{{ $paramName }}"
        data-hidden-input="#{{ $inputId }}"
        data-file-link="#{{ $fileLinkId }}"
        data-accepted-files="{{ $acceptedFiles }}"
        data-max-files="{{ $maxFiles }}"
        data-max-filesize="{{ $maxFilesize }}"
        data-upload-params='@json(['folder' => $folder])'
    >
        <div class="dz-message needsclick">
            <div class="fs-5 mb-2">{{ $dropMessage }}</div>
            <div class="small text-secondary">Maksimal {{ $maxFilesize }} MB per file.</div>
        </div>
    </div>

    <div class="form-text mt-2">
        <span id="{{ $fileLinkId }}" data-empty-text="Belum ada file terupload.">
            @if ($fieldValue)
                <a href="{{ asset($fieldValue) }}" target="_blank" rel="noopener noreferrer">{{ $fieldValue }}</a>
            @else
                Belum ada file terupload.
            @endif
        </span>
    </div>

    @if ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
