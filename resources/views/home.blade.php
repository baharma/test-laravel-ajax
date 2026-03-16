@extends('layout')

@section('title', 'Employee CRUD')

@section('content')
    <main class="container py-5">
        <section class="demo-card card border-0 rounded-5" data-control="employees-browser"
            data-endpoint="{{ route('employees.index') }}" data-store-endpoint="{{ route('employees.store') }}"
            data-show-endpoint-template="{{ route('employees.show', ['employee' => '__ID__']) }}"
            data-update-endpoint-template="{{ route('employees.update', ['employee' => '__ID__']) }}"
            data-destroy-endpoint-template="{{ route('employees.destroy', ['employee' => '__ID__']) }}">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <div class="small text-uppercase text-secondary fw-semibold">Total Employee</div>
                            <div class="fs-4 fw-semibold" data-role="employees-total">0</div>
                        </div>

                        <button type="button" class="btn btn-primary px-4" data-role="employees-create">
                            Tambah Employee
                        </button>
                    </div>
                </div>

                <form class="row g-3 mb-4" data-role="employees-filter-form">
                    <div class="col-lg-4">
                        <x-input name="search" label="Pencarian" placeholder="Cari nama, kode, email, atau no HP"
                            hint="Search memakai parameter `search` di endpoint." />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-select name="department_id" label="Department" placeholder="Semua department" :ajax-url="route('departments.index')"
                            query-param="search" text-field="name" :ajax-params="['per_page' => 'all', 'orderBy' => 'name', 'orderDirection' => 'asc']" minimum-input-length="0"
                            allow-clear="true" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-select name="employment_status_id" label="Status Kerja" placeholder="Semua status"
                            :ajax-url="route('employment-statuses.index')" query-param="search" text-field="name" :ajax-params="['per_page' => 'all', 'orderBy' => 'name', 'orderDirection' => 'asc']"
                            :label-fields="['code', 'name']" minimum-input-length="0" allow-clear="true" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-date-picker name="hire_date" label="Tanggal Masuk" placeholder="Pilih rentang tanggal masuk"
                            hint="Filter memakai range `hire_date` dari daterangepicker." />
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <x-select name="is_active" label="Aktif" placeholder="Semua" :options="[['id' => '1', 'text' => 'Aktif'], ['id' => '0', 'text' => 'Nonaktif']]"
                            allow-clear="true" />
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
                                <th>Employee</th>
                                <th>Department / Posisi</th>
                                <th>Status</th>
                                <th>Aktif</th>
                                <th>Hire Date</th>
                                <th>File</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>

        <x-modal id="employee-modal" size="xl" scrollable="true" :show-footer="true">
            <x-slot:header>
                <h2 class="modal-title fs-5 mb-0" id="employee-modal-label" data-role="employee-modal-title">
                    Tambah Employee
                </h2>
            </x-slot:header>

            <form data-role="employee-form">
                <div class="alert alert-danger d-none" data-role="employee-form-feedback"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input name="employee_code" label="Kode Karyawan" placeholder="EMP-0001" required />
                    </div>

                    <div class="col-md-6">
                        <x-input name="full_name" label="Nama Lengkap" placeholder="Nama employee" required />
                    </div>

                    <div class="col-md-6">
                        <x-input name="email" type="email" label="Email" placeholder="nama@company.com" />
                    </div>

                    <div class="col-md-6">
                        <x-input name="phone" label="No. HP" placeholder="08xxxxxxxxxx" />
                    </div>

                    <div class="col-md-4">
                        <x-select name="gender" label="Gender" placeholder="Pilih gender" :options="[['id' => 'male', 'text' => 'Male'], ['id' => 'female', 'text' => 'Female']]" />
                    </div>

                    <div class="col-md-4">
                        <x-input name="birth_place" label="Tempat Lahir" placeholder="Makassar" />
                    </div>

                    <div class="col-md-4">
                        <x-input name="birth_date" type="date" label="Tanggal Lahir" />
                    </div>

                    <div class="col-md-4">
                        <x-select id="employee-department-field" name="department_id" label="Department"
                            placeholder="Pilih department" :ajax-url="route('departments.index')" query-param="search" text-field="name"
                            :ajax-params="['per_page' => 'all', 'orderBy' => 'name', 'orderDirection' => 'asc']" minimum-input-length="0" allow-clear="true" required />
                    </div>

                    <div class="col-md-4">
                        <x-select id="employee-position-field" name="position_id" label="Position"
                            placeholder="Pilih position" :ajax-url="route('positions.index')" query-param="search" text-field="name"
                            :ajax-params="['per_page' => 'all', 'orderBy' => 'name', 'orderDirection' => 'asc']" minimum-input-length="0" depends-on="#employee-department-field"
                            depends-on-param="department_id" allow-clear="true" required />
                    </div>

                    <div class="col-md-4">
                        <x-select id="employee-employment-status-field" name="employment_status_id"
                            label="Employment Status" placeholder="Pilih status kerja" :ajax-url="route('employment-statuses.index')"
                            query-param="search" text-field="name" :ajax-params="['per_page' => 'all', 'orderBy' => 'name', 'orderDirection' => 'asc']" :label-fields="['code', 'name']" minimum-input-length="0"
                            allow-clear="true" required />
                    </div>

                    <div class="col-md-4">
                        <x-input name="hire_date" type="date" label="Tanggal Masuk" required />
                    </div>

                    <div class="col-md-4">
                        <x-input name="end_date" type="date" label="Tanggal Selesai" />
                    </div>

                    <div class="col-md-4">
                        <x-select name="is_active" label="Aktif" :options="[['id' => '1', 'text' => 'Aktif'], ['id' => '0', 'text' => 'Nonaktif']]" selected="1" :allow-clear="false"
                            required />
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="employee-address-field">Alamat</label>
                        <textarea id="employee-address-field" name="address" rows="3" class="form-control"
                            placeholder="Alamat lengkap employee"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="employee-notes-field">Catatan</label>
                        <textarea id="employee-notes-field" name="notes" rows="3" class="form-control"
                            placeholder="Catatan tambahan employee"></textarea>
                    </div>

                    <div class="col-12">
                        <x-upload-file-dropzone id="employee-photo-field" name="photo" label="Upload File / Foto"
                            folder="employees" accepted-files="image/*,.pdf,.doc,.docx" max-files="1"
                            hint="File diupload langsung ke local, lalu path hasil upload disimpan ke field `photo`." />
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" data-role="employee-submit">
                    Simpan Employee
                </button>
            </x-slot:footer>
        </x-modal>
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
            const createButton = container.querySelector('[data-role="employees-create"]');
            const employeeModalElement = document.getElementById('employee-modal');
            const employeeForm = employeeModalElement?.querySelector('[data-role="employee-form"]');
            const employeeFormFeedback = employeeModalElement?.querySelector(
                '[data-role="employee-form-feedback"]');
            const employeeModalTitle = employeeModalElement?.querySelector('[data-role="employee-modal-title"]');
            const employeeSubmitButton = employeeModalElement?.querySelector('[data-role="employee-submit"]');
            const departmentField = employeeForm?.querySelector('#employee-department-field');
            const positionField = employeeForm?.querySelector('#employee-position-field');
            const employmentStatusField = employeeForm?.querySelector('#employee-employment-status-field');
            const endpoint = container.dataset.endpoint;
            const storeEndpoint = container.dataset.storeEndpoint;
            const showEndpointTemplate = container.dataset.showEndpointTemplate;
            const updateEndpointTemplate = container.dataset.updateEndpointTemplate;
            const destroyEndpointTemplate = container.dataset.destroyEndpointTemplate;
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
            const DataTable = window.DataTable ?? null;
            const bootstrap = window.bootstrap ?? null;
            const moment = window.moment ?? null;
            const $ = window.$ ?? null;
            const employeeModal = employeeModalElement && bootstrap ?
                bootstrap.Modal.getOrCreateInstance(employeeModalElement) :
                null;

            let editingEmployeeId = null;

            if (!form || !tableElement || !endpoint || !employeeForm || !employeeSubmitButton) {
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

            const resolveEndpoint = (template, id) => template?.replace('__ID__', String(id)) ?? '';

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

            const setAlert = (element, message = '', type = 'info') => {
                if (!element) {
                    return;
                }

                if (!message) {
                    element.className = 'alert d-none';
                    element.innerHTML = '';

                    return;
                }

                element.className = `alert alert-${type}`;
                element.innerHTML = message;
            };

            const setFeedback = (message = '', type = 'info') => {
                setAlert(feedbackElement, escapeHtml(message), type);
            };

            const clearEmployeeFormFeedback = () => {
                setAlert(employeeFormFeedback);
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

            const formatDate = (value) => {
                if (!value) {
                    return '';
                }

                if (!moment) {
                    return String(value).slice(0, 10);
                }

                const parsed = moment(value);

                return parsed.isValid() ? parsed.format('YYYY-MM-DD') : String(value).slice(0, 10);
            };

            const assetUrl = (path) => {
                if (!path) {
                    return '';
                }

                try {
                    return new URL(String(path), window.location.origin).toString();
                } catch (error) {
                    return String(path);
                }
            };

            const resetSelectValue = (element) => {
                if (!element) {
                    return;
                }

                if ($ && $(element).data('select2')) {
                    $(element).val(null).trigger('change');

                    return;
                }

                element.value = '';
            };

            const setSelectValue = (element, value, text = null) => {
                if (!element) {
                    return;
                }

                if (value === null || value === undefined || value === '') {
                    resetSelectValue(element);

                    return;
                }

                const stringValue = String(value);
                let option = Array.from(element.options).find((item) => item.value === stringValue);

                if (!option) {
                    option = new Option(text ?? stringValue, stringValue, true, true);
                    element.add(option);
                } else {
                    option.selected = true;

                    if (text) {
                        option.text = text;
                    }
                }

                if ($ && $(element).data('select2')) {
                    $(element).val(stringValue).trigger('change');
                } else {
                    element.value = stringValue;
                }
            };

            const setPositionFieldState = () => {
                if (!positionField || !departmentField) {
                    return;
                }

                const disabled = !departmentField.value;

                if ($ && $(positionField).data('select2')) {
                    $(positionField).prop('disabled', disabled).trigger('change.select2');
                } else {
                    positionField.disabled = disabled;
                }
            };

            const resetDropzoneField = (dropzoneElement) => {
                if (!dropzoneElement) {
                    return;
                }

                const hiddenInput = dropzoneElement.dataset.hiddenInput ?
                    employeeForm.querySelector(dropzoneElement.dataset.hiddenInput) :
                    null;
                const fileLink = dropzoneElement.dataset.fileLink ?
                    document.querySelector(dropzoneElement.dataset.fileLink) :
                    null;
                const emptyText = fileLink?.dataset.emptyText ?? 'Belum ada file terupload.';

                if (dropzoneElement.dropzone) {
                    dropzoneElement.dropzone.removeAllFiles(true);
                }

                if (hiddenInput) {
                    hiddenInput.value = '';
                }

                if (fileLink) {
                    fileLink.innerHTML = escapeHtml(emptyText);
                }
            };

            const setDropzoneValue = (dropzoneElement, path) => {
                if (!dropzoneElement) {
                    return;
                }

                const hiddenInput = dropzoneElement.dataset.hiddenInput ?
                    employeeForm.querySelector(dropzoneElement.dataset.hiddenInput) :
                    null;
                const fileLink = dropzoneElement.dataset.fileLink ?
                    document.querySelector(dropzoneElement.dataset.fileLink) :
                    null;
                const emptyText = fileLink?.dataset.emptyText ?? 'Belum ada file terupload.';

                if (hiddenInput) {
                    hiddenInput.value = path ?? '';
                }

                if (!fileLink) {
                    return;
                }

                if (!path) {
                    fileLink.innerHTML = escapeHtml(emptyText);

                    return;
                }

                fileLink.innerHTML =
                    `<a href="${escapeHtml(assetUrl(path))}" target="_blank" rel="noopener noreferrer">${escapeHtml(path)}</a>`;
            };

            const resetEmployeeForm = () => {
                editingEmployeeId = null;
                employeeForm.reset();
                clearEmployeeFormFeedback();

                employeeForm.querySelectorAll('select').forEach((element) => {
                    resetSelectValue(element);
                });

                employeeForm.querySelectorAll('[data-control="dropzone"]').forEach((element) => {
                    resetDropzoneField(element);
                });

                setSelectValue(employeeForm.querySelector('[name="is_active"]'), '1', 'Aktif');
                setPositionFieldState();
            };

            const populateEmployeeForm = (employee) => {
                employeeForm.querySelector('[name="employee_code"]').value = employee.employee_code ?? '';
                employeeForm.querySelector('[name="full_name"]').value = employee.full_name ?? '';
                employeeForm.querySelector('[name="email"]').value = employee.email ?? '';
                employeeForm.querySelector('[name="phone"]').value = employee.phone ?? '';
                employeeForm.querySelector('[name="birth_place"]').value = employee.birth_place ?? '';
                employeeForm.querySelector('[name="birth_date"]').value = formatDate(employee.birth_date);
                employeeForm.querySelector('[name="hire_date"]').value = formatDate(employee.hire_date);
                employeeForm.querySelector('[name="end_date"]').value = formatDate(employee.end_date);
                employeeForm.querySelector('[name="address"]').value = employee.address ?? '';
                employeeForm.querySelector('[name="notes"]').value = employee.notes ?? '';

                setSelectValue(
                    employeeForm.querySelector('[name="gender"]'),
                    employee.gender,
                    employee.gender === 'female' ? 'Female' : 'Male',
                );

                setSelectValue(
                    departmentField,
                    employee.department_id,
                    employee.department ? [employee.department.code, employee.department.name].filter(
                        Boolean).join(' - ') :
                    employee.department_id,
                );
                setPositionFieldState();
                setSelectValue(
                    positionField,
                    employee.position_id,
                    employee.position ? [employee.position.name, employee.position.level].filter(Boolean)
                    .join(' - ') :
                    employee.position_id,
                );
                setSelectValue(
                    employmentStatusField,
                    employee.employment_status_id,
                    employee.employment_status ? [employee.employment_status.code, employee
                        .employment_status.name
                    ].filter(Boolean).join(' - ') :
                    employee.employment_status_id,
                );
                setSelectValue(
                    employeeForm.querySelector('[name="is_active"]'),
                    employee.is_active ? '1' : '0',
                    employee.is_active ? 'Aktif' : 'Nonaktif',
                );
                setDropzoneValue(
                    employeeForm.querySelector('[data-control="dropzone"]'),
                    employee.photo ?? '',
                );
            };

            const buildRows = (employees) =>
                employees.map((employee) => [
                    `<span class="fw-semibold">${escapeHtml(employee.employee_code ?? '-')}</span>`,
                    `
                        <div class="fw-semibold">${escapeHtml(employee.full_name ?? '-')}</div>
                        <div>${escapeHtml(employee.email ?? '-')}</div>
                        <div class="small text-secondary">${escapeHtml(employee.phone ?? '-')}</div>
                    `,
                    `
                        <div class="fw-semibold">${escapeHtml(employee.department?.name ?? '-')}</div>
                        <div class="small text-secondary">${escapeHtml(employee.position?.name ?? '-')}</div>
                    `,
                    escapeHtml(employee.employment_status?.name ?? '-'),
                    employee.is_active ?
                    '<span class="badge text-bg-success">Aktif</span>' :
                    '<span class="badge text-bg-secondary">Nonaktif</span>',
                    escapeHtml(formatDate(employee.hire_date) || '-'),
                    employee.photo ?
                    `<a href="${escapeHtml(assetUrl(employee.photo))}" target="_blank" rel="noopener noreferrer">Lihat File</a>` :
                    '-',
                    `
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit"
                                data-employee-id="${escapeHtml(employee.id)}">
                                Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete"
                                data-employee-id="${escapeHtml(employee.id)}"
                                data-employee-name="${escapeHtml(employee.full_name ?? employee.employee_code ?? 'employee')}">
                                Hapus
                            </button>
                        </div>
                    `,
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

            const openCreateModal = () => {
                resetEmployeeForm();

                if (employeeModalTitle) {
                    employeeModalTitle.textContent = 'Tambah Employee';
                }

                employeeSubmitButton.textContent = 'Simpan Employee';
                employeeModal?.show();
            };

            const openEditModal = async (employeeId) => {
                resetEmployeeForm();

                try {
                    const response = await fetch(resolveEndpoint(showEndpointTemplate, employeeId), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload?.message ?? 'Gagal memuat detail employee.');
                    }

                    const employee = payload?.data ?? null;

                    if (!employee) {
                        throw new Error('Data employee tidak ditemukan.');
                    }

                    editingEmployeeId = employee.id;
                    populateEmployeeForm(employee);

                    if (employeeModalTitle) {
                        employeeModalTitle.textContent = 'Edit Employee';
                    }

                    employeeSubmitButton.textContent = 'Update Employee';
                    employeeModal?.show();
                } catch (error) {
                    setFeedback(
                        error instanceof Error ? error.message :
                        'Terjadi kesalahan saat mengambil detail employee.',
                        'danger',
                    );
                }
            };

            const showEmployeeFormErrors = (errors) => {
                const messages = Object.values(errors ?? {})
                    .flat()
                    .filter(Boolean);

                if (messages.length === 0) {
                    setAlert(employeeFormFeedback, 'Terjadi kesalahan saat menyimpan employee.', 'danger');

                    return;
                }

                setAlert(
                    employeeFormFeedback,
                    `<ul class="mb-0 ps-3">${messages.map((message) => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`,
                    'danger',
                );
            };

            const submitEmployeeForm = async () => {
                clearEmployeeFormFeedback();
                employeeSubmitButton.disabled = true;

                const formData = new FormData(employeeForm);
                let url = storeEndpoint;

                if (editingEmployeeId) {
                    url = resolveEndpoint(updateEndpointTemplate, editingEmployeeId);
                    formData.append('_method', 'PATCH');
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? {
                                'X-CSRF-TOKEN': csrfToken
                            } : {}),
                        },
                        body: formData,
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (response.status === 422) {
                        showEmployeeFormErrors(payload?.errors ?? {});

                        return;
                    }

                    if (!response.ok) {
                        throw new Error(payload?.message ?? 'Gagal menyimpan employee.');
                    }

                    employeeModal?.hide();
                    await loadEmployees();
                } catch (error) {
                    setAlert(
                        employeeFormFeedback,
                        escapeHtml(
                            error instanceof Error ?
                            error.message :
                            'Terjadi kesalahan saat menyimpan employee.',
                        ),
                        'danger',
                    );
                } finally {
                    employeeSubmitButton.disabled = false;
                }
            };

            const deleteEmployee = async (employeeId, employeeName) => {
                const confirmed = window.confirm(
                    `Hapus employee "${employeeName}"? Tindakan ini tidak bisa dibatalkan.`,
                );

                if (!confirmed) {
                    return;
                }

                const formData = new FormData();
                formData.append('_method', 'DELETE');

                try {
                    const response = await fetch(resolveEndpoint(destroyEndpointTemplate, employeeId), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? {
                                'X-CSRF-TOKEN': csrfToken
                            } : {}),
                        },
                        body: formData,
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload?.message ?? 'Gagal menghapus employee.');
                    }

                    await loadEmployees();
                } catch (error) {
                    setFeedback(
                        error instanceof Error ? error.message :
                        'Terjadi kesalahan saat menghapus employee.',
                        'danger',
                    );
                }
            };

            const debouncedLoadEmployees = debounce(() => {
                void loadEmployees();
            }, 400);

            createButton?.addEventListener('click', openCreateModal);
            employeeSubmitButton.addEventListener('click', () => {
                void submitEmployeeForm();
            });

            employeeModalElement?.addEventListener('hidden.bs.modal', () => {
                resetEmployeeForm();
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                void loadEmployees();
            });

            form.querySelector('[name="search"]')?.addEventListener('input', debouncedLoadEmployees);

            // Gunakan jQuery .on('change') jika tersedia, agar Select2 juga terpanggil.
            // Select2 hanya men-trigger event change via jQuery, bukan native DOM event.
            if ($) {
                $(form).find('select').on('change', () => {
                    void loadEmployees();
                });
            } else {
                form.querySelectorAll('select').forEach((element) => {
                    element.addEventListener('change', () => {
                        void loadEmployees();
                    });
                });
            }

            form.querySelectorAll('[data-control="daterangepicker"]').forEach((element) => {
                element.addEventListener('change', () => {
                    void loadEmployees();
                });
            });

            departmentField?.addEventListener('change', () => {
                resetSelectValue(positionField);
                setPositionFieldState();
            });

            container.addEventListener('click', (event) => {
                const actionButton = event.target.closest('[data-action]');

                if (!actionButton) {
                    return;
                }

                const employeeId = actionButton.dataset.employeeId;

                if (!employeeId) {
                    return;
                }

                if (actionButton.dataset.action === 'edit') {
                    void openEditModal(employeeId);
                }

                if (actionButton.dataset.action === 'delete') {
                    void deleteEmployee(
                        employeeId,
                        actionButton.dataset.employeeName ?? 'employee',
                    );
                }
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

            setPositionFieldState();
            void loadEmployees();
        });
    </script>
@endpush
