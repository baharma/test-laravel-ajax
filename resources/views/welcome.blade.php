<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | Test</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="plugin-demo">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <span class="badge text-bg-primary mb-3">Bootstrap Input Component</span>
                        <h1 class="h2 mb-3">Reusable <code>&lt;x-input&gt;</code></h1>
                        <p class="text-secondary mb-4">
                            Komponen ini sekarang siap untuk form Bootstrap dengan support
                            <code>old()</code>, error validation, prepend, append, dan helper text.
                        </p>

                        <x-input
                            name="employee_code"
                            label="Kode Karyawan"
                            placeholder="EMP-0001"
                            hint="Contoh field text standar."
                        />

                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input
                                    name="full_name"
                                    label="Nama Lengkap"
                                    placeholder="Nama karyawan"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-input
                                    name="email"
                                    type="email"
                                    label="Email"
                                    placeholder="nama@company.com"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-input
                                    name="salary"
                                    type="number"
                                    label="Gaji Pokok"
                                    prefix="Rp"
                                    step="1000"
                                    min="0"
                                    hint="Contoh input group dengan prefix."
                                />
                            </div>

                            <div class="col-md-6">
                                <x-input
                                    name="photo"
                                    type="file"
                                    label="Foto"
                                    accept="image/*"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>
