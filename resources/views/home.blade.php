@extends('layout')

@section('title', 'Employees Browser')

@section('content')
    <main class="container py-5">
        <section class="demo-card card border-0 rounded-5" data-control="employees-browser"
            data-endpoint="{{ route('employees.index') }}">
            <div class="card-body p-4 p-lg-5">
                <form class="row g-3 mb-4" data-role="employees-filter-form">
                    <div class="col-lg-4">
                        <x-input name="search" label="Pencarian" placeholder="Cari nama, kode, email, atau no HP"
                            hint="Search memakai parameter `search` di endpoint." />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-select name="department_id" label="Department" placeholder="Semua department" :ajax-url="route('lookups.departments')"
                            minimum-input-length="0" allow-clear="true" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-select name="employment_status_id" label="Status Kerja" placeholder="Semua status"
                            :ajax-url="route('lookups.employment-statuses')" minimum-input-length="0" allow-clear="true" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-date-picker name="hire_date" label="Tanggal Masuk" placeholder="Pilih rentang tanggal masuk"
                            hint="Filter memakai range `hire_date` dari daterangepicker." />
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <x-select name="is_active" label="Aktif" placeholder="Semua" :options="[['id' => '1', 'text' => 'Aktif'], ['id' => '0', 'text' => 'Nonaktif']]"
                            allow-clear="true" />
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <x-select name="orderBy" label="Urutkan" :options="[
                            ['id' => 'full_name', 'text' => 'Nama'],
                            ['id' => 'employee_code', 'text' => 'Kode Karyawan'],
                            ['id' => 'hire_date', 'text' => 'Tanggal Masuk'],
                            ['id' => 'created_at', 'text' => 'Tanggal Dibuat'],
                        ]" :allow-clear="false" />
                    </div>
                    <div class="col-lg-12 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-role="employees-reset">
                            Reset Filter
                        </button>
                    </div>
                </form>

                <div class="alert alert-info d-none" data-role="employees-feedback"></div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle w-100" data-role="employees-table">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Kontak</th>
                                <th>Department</th>
                                <th>Posisi</th>
                                <th>Status</th>
                                <th>Aktif</th>
                                <th>Hire Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', () => {
            const container = document.querySelector('[data-control="employees-browser"]');

            if (!container) {
                return;
            }

            const form = container.querySelector('[data-role="employees-filter-form"]');
            const tableElement = container.querySelector('[data-role="employees-table"]');
            const tableBody = tableElement?.querySelector('tbody');
            const feedbackElement = container.querySelector('[data-role="employees-feedback"]');
            const totalElement = container.querySelector('[data-role="employees-total"]');
            const resetButton = container.querySelector('[data-role="employees-reset"]');
            const endpoint = container.dataset.endpoint;
            const DataTable = window.DataTable ?? null;
            const moment = window.moment ?? null;
            const $ = window.$ ?? null;

            if (!form || !tableElement || !endpoint) {
                return;
            }

            const escapeHtml = (value) =>
                String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const debounce = (callback, wait = 300) => {
                let timeoutId;

                return (...args) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => callback(...args), wait);
                };
            };

            const dataTable = DataTable ?
                DataTable.isDataTable(tableElement) ?
                new DataTable(tableElement) :
                new DataTable(tableElement, {
                    searching: false,
                    lengthChange: false,
                    pageLength: 10,
                    order: [],
                }) :
                null;

            const setFeedback = (message = '', type = 'info') => {
                if (!feedbackElement) {
                    return;
                }

                if (!message) {
                    feedbackElement.className = 'alert d-none';
                    feedbackElement.textContent = '';

                    return;
                }

                feedbackElement.className = `alert alert-${type}`;
                feedbackElement.textContent = message;
            };

            const normalizeEmployeesPayload = (payload) => {
                const source = payload?.data;

                if (Array.isArray(source)) {
                    return {
                        items: source,
                        total: source.length,
                    };
                }

                if (Array.isArray(source?.data)) {
                    return {
                        items: source.data,
                        total: source.total ?? source.data.length,
                    };
                }

                return {
                    items: [],
                    total: 0,
                };
            };

            const formatHireDate = (value) => {
                if (!value) {
                    return '-';
                }

                if (!moment) {
                    return escapeHtml(value);
                }

                const parsed = moment(value);

                return parsed.isValid() ? parsed.format('YYYY-MM-DD') : escapeHtml(value);
            };

            const buildRows = (employees) =>
                employees.map((employee) => [
                    `<span class="fw-semibold">${escapeHtml(employee.employee_code ?? '-')}</span>`,
                    `<div class="fw-semibold">${escapeHtml(employee.full_name ?? '-')}</div>`,
                    `
                            <div>${escapeHtml(employee.email ?? '-')}</div>
                            <div class="small text-secondary">${escapeHtml(employee.phone ?? '-')}</div>
                        `,
                    escapeHtml(employee.department?.name ?? '-'),
                    escapeHtml(employee.position?.name ?? '-'),
                    escapeHtml(employee.employment_status?.name ?? '-'),
                    employee.is_active ?
                    '<span class="badge text-bg-success">Aktif</span>' :
                    '<span class="badge text-bg-secondary">Nonaktif</span>',
                    escapeHtml(formatHireDate(employee.hire_date)),
                ]);

            const replaceTableRows = (rows) => {
                if (dataTable) {
                    dataTable.clear();
                    dataTable.rows.add(rows).draw();

                    return;
                }

                if (!tableBody) {
                    return;
                }

                tableBody.innerHTML = rows
                    .map(
                        (columns) =>
                        `<tr>${columns.map((column) => `<td>${column}</td>`).join('')}</tr>`,
                    )
                    .join('');
            };

            const collectParams = () => {
                const formData = new FormData(form);
                const params = new URLSearchParams();

                for (const [key, value] of formData.entries()) {
                    if (value !== null && value !== '') {
                        params.append(key, String(value));
                    }
                }

                params.set('per_page', 'all');

                return params;
            };

            const loadEmployees = async () => {
                const url = `${endpoint}?${collectParams().toString()}`;

                setFeedback('Memuat data karyawan...', 'info');

                try {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload?.message ?? 'Gagal memuat data karyawan.');
                    }

                    const {
                        items,
                        total
                    } = normalizeEmployeesPayload(payload);

                    replaceTableRows(buildRows(items));

                    if (totalElement) {
                        totalElement.textContent = String(total);
                    }

                    if (items.length === 0) {
                        setFeedback(
                            'Tidak ada data yang cocok dengan filter saat ini. Pastikan data employee sudah ada di database.',
                            'warning',
                        );
                    } else {
                        setFeedback('', 'info');
                    }
                } catch (error) {
                    replaceTableRows([]);

                    if (totalElement) {
                        totalElement.textContent = '0';
                    }

                    setFeedback(
                        error instanceof Error ?
                        error.message :
                        'Terjadi kesalahan saat mengambil data karyawan.',
                        'danger',
                    );
                }
            };

            const debouncedLoadEmployees = debounce(() => {
                void loadEmployees();
            }, 400);

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                void loadEmployees();
            });

            form.querySelector('[name="search"]')?.addEventListener('input', debouncedLoadEmployees);

            form.querySelectorAll('select').forEach((element) => {
                element.addEventListener('change', () => {
                    void loadEmployees();
                });
            });

            form.querySelectorAll('[data-control="daterangepicker"]').forEach((element) => {
                element.addEventListener('change', () => {
                    void loadEmployees();
                });
            });

            resetButton?.addEventListener('click', () => {
                form.reset();

                if ($) {
                    form.querySelectorAll('[data-control="select2"]').forEach((element) => {
                        $(element).val(null).trigger('change');
                    });
                }

                form.querySelectorAll('[data-control="daterangepicker"]').forEach((element) => {
                    if ($) {
                        const picker = $(element).data('daterangepicker');

                        if (picker && moment) {
                            picker.setStartDate(moment());
                            picker.setEndDate(moment());
                        }
                    }

                    element.value = '';
                });

                void loadEmployees();
            });

            void loadEmployees();
        });
    </script>
@endpush
