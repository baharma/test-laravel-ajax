import './bootstrap';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import moment from 'moment';
import Dropzone from 'dropzone';

window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;
window.moment = moment;
window.Dropzone = Dropzone;

Dropzone.autoDiscover = false;

const readBoolean = (value, fallback = true) => {
    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    return !['0', 'false', 'no', 'off'].includes(String(value).toLowerCase());
};

const readNumber = (value, fallback) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : fallback;
};

const readJson = (value, fallback = null) => {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('Failed to parse JSON option:', value, error);

        return fallback;
    }
};

const parseMomentValue = (value, format = undefined) => {
    if (value === undefined || value === null || value === '') {
        return null;
    }

    const parsed = format ? moment(value, format, true) : moment(value);

    return parsed.isValid() ? parsed : null;
};

const normalizeDateRanges = (ranges, format) => {
    if (!ranges || typeof ranges !== 'object' || Array.isArray(ranges)) {
        return null;
    }

    return Object.fromEntries(
        Object.entries(ranges)
            .map(([label, range]) => {
                if (!Array.isArray(range) || range.length < 2) {
                    return null;
                }

                const start = moment.isMoment(range[0])
                    ? range[0]
                    : parseMomentValue(range[0], format) ?? parseMomentValue(range[0]);
                const end = moment.isMoment(range[1])
                    ? range[1]
                    : parseMomentValue(range[1], format) ?? parseMomentValue(range[1]);

                if (!start || !end) {
                    return null;
                }

                return [label, [start, end]];
            })
            .filter(Boolean),
    );
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

const defaultDateRanges = {
    Today: [moment(), moment()],
    Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    'This Month': [moment().startOf('month'), moment().endOf('month')],
    'Last Month': [
        moment().subtract(1, 'month').startOf('month'),
        moment().subtract(1, 'month').endOf('month'),
    ],
};

const initializeDataTables = (DataTable) => {
    document.querySelectorAll('[data-control="datatable"]').forEach((table) => {
        if (DataTable.isDataTable(table)) {
            return;
        }

        const order = readJson(table.dataset.order, []);

        new DataTable(table, {
            pageLength: readNumber(table.dataset.pageLength, 5),
            paging: readBoolean(table.dataset.paging, true),
            ordering: readBoolean(table.dataset.ordering, true),
            searching: readBoolean(table.dataset.searching, true),
            info: readBoolean(table.dataset.info, true),
            lengthChange: readBoolean(table.dataset.lengthChange, true),
            order: Array.isArray(order) ? order : [],
        });
    });
};

const initializeDateRangePickers = () => {
    $('[data-control="daterangepicker"]').each(function initializeElement() {
        const $element = $(this);

        if ($element.data('daterangepicker')) {
            return;
        }

        const format = $element.data('format') ?? 'YYYY-MM-DD';
        const displayFormat = $element.data('displayFormat') ?? format;
        const separator = $element.data('separator') ?? ' - ';
        const singleDatePicker = readBoolean($element.data('singleDatePicker'), false);
        const autoUpdateInput = readBoolean($element.data('autoUpdateInput'), true);
        const showRanges = readBoolean($element.data('showRanges'), !singleDatePicker);
        const initialValue = ($element.val() ?? '').trim();
        const configuredRanges = normalizeDateRanges(readJson($element.attr('data-ranges')), format);
        const ranges = showRanges ? configuredRanges ?? defaultDateRanges : undefined;
        const startDate =
            parseMomentValue($element.data('startDate'), format) ??
            (!singleDatePicker && initialValue.includes(separator)
                ? parseMomentValue(initialValue.split(separator)[0]?.trim(), displayFormat)
                : parseMomentValue(initialValue, displayFormat));
        const endDate =
            parseMomentValue($element.data('endDate'), format) ??
            (!singleDatePicker && initialValue.includes(separator)
                ? parseMomentValue(initialValue.split(separator)[1]?.trim(), displayFormat)
                : startDate);

        const pickerConfig = {
            autoUpdateInput,
            autoApply: readBoolean($element.data('autoApply'), false),
            singleDatePicker,
            showDropdowns: readBoolean($element.data('showDropdowns'), true),
            showCustomRangeLabel: readBoolean($element.data('showCustomRangeLabel'), true),
            timePicker: readBoolean($element.data('timePicker'), false),
            timePicker24Hour: readBoolean($element.data('timePicker24Hour'), false),
            timePickerSeconds: readBoolean($element.data('timePickerSeconds'), false),
            opens: $element.data('opens') ?? 'left',
            drops: $element.data('drops') ?? 'auto',
            buttonClasses: ['btn', 'btn-sm'],
            applyButtonClasses: 'btn-primary',
            cancelButtonClasses: 'btn-outline-secondary',
            locale: {
                cancelLabel: 'Clear',
                separator,
                format: displayFormat,
            },
        };

        if (ranges) {
            pickerConfig.ranges = ranges;
        }

        const minDate = parseMomentValue($element.data('minDate'), format);
        const maxDate = parseMomentValue($element.data('maxDate'), format);

        if (minDate) {
            pickerConfig.minDate = minDate;
        }

        if (maxDate) {
            pickerConfig.maxDate = maxDate;
        }

        if (startDate) {
            pickerConfig.startDate = startDate;
        }

        if (endDate && !singleDatePicker) {
            pickerConfig.endDate = endDate;
        }

        $element.daterangepicker(pickerConfig);

        const updateValue = (picker) => {
            if (!autoUpdateInput) {
                return;
            }

            const nextValue = singleDatePicker
                ? picker.startDate.format(displayFormat)
                : `${picker.startDate.format(displayFormat)}${separator}${picker.endDate.format(displayFormat)}`;

            $element.val(nextValue).trigger('change');
        };

        $element.on('apply.daterangepicker', (event, picker) => {
            updateValue(picker);
        });

        $element.on('cancel.daterangepicker', () => {
            $element.val('').trigger('change');
        });

        if (autoUpdateInput && initialValue === '' && startDate) {
            updateValue($element.data('daterangepicker'));
        }
    });
};

const initializeFileInputs = () => {
    $('[data-control="fileinput"]').each(function initializeElement() {
        const $element = $(this);

        if ($element.data('fileinput')) {
            return;
        }

        $element.fileinput({
            theme: 'bs5',
            showUpload: false,
            dropZoneEnabled: true,
            browseClass: 'btn btn-outline-primary',
            allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
            maxFileCount: readNumber($element.data('maxFiles'), 5),
        });
    });
};

const initializeDropzones = () => {
    document.querySelectorAll('[data-control="dropzone"]').forEach((element) => {
        if (element.dropzone) {
            return;
        }

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        const hiddenInput = element.dataset.hiddenInput
            ? document.querySelector(element.dataset.hiddenInput)
            : null;
        const fileLink = element.dataset.fileLink
            ? document.querySelector(element.dataset.fileLink)
            : null;
        const uploadParams = readJson(element.dataset.uploadParams, {});
        const maxFiles = readNumber(element.dataset.maxFiles, 1);
        const setUploadedValue = (path = '', url = '') => {
            if (hiddenInput) {
                hiddenInput.value = path;
            }

            if (!fileLink) {
                return;
            }

            const emptyText = fileLink.dataset.emptyText ?? 'Belum ada file terupload.';

            if (!path) {
                fileLink.innerHTML = emptyText;

                return;
            }

            const fileUrl = url || new URL(path, window.location.origin).toString();
            fileLink.innerHTML = `<a href="${fileUrl}" target="_blank" rel="noopener noreferrer">${path}</a>`;
        };
        const resolveUploadResponse = (response) => {
            if (!response) {
                return null;
            }

            if (typeof response === 'string') {
                try {
                    return resolveUploadResponse(JSON.parse(response));
                } catch (error) {
                    return {
                        path: response,
                        url: new URL(response, window.location.origin).toString(),
                    };
                }
            }

            const path =
                getNestedValue(response, 'data.path') ??
                response.path ??
                response.file_path ??
                response.value ??
                null;

            if (!path) {
                return null;
            }

            return {
                path,
                url: getNestedValue(response, 'data.url') ?? response.url ?? '',
            };
        };

        const dropzone = new Dropzone(element, {
            url: element.dataset.url || element.getAttribute('action'),
            method: 'post',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            paramName: element.dataset.paramName ?? 'file',
            params: uploadParams,
            maxFiles,
            maxFilesize: readNumber(element.dataset.maxFilesize, 5),
            acceptedFiles: element.dataset.acceptedFiles ?? 'image/*,.pdf',
            addRemoveLinks: true,
            timeout: 120000,
            dictDefaultMessage: 'Drop files here or click to upload',
        });

        dropzone.on('addedfile', (file) => {
            if (maxFiles === 1 && dropzone.files.length > 1) {
                dropzone.files
                    .filter((queuedFile) => queuedFile !== file)
                    .forEach((queuedFile) => dropzone.removeFile(queuedFile));
            }

            setUploadedValue('', '');
        });

        dropzone.on('success', (file, response) => {
            const uploadedFile = resolveUploadResponse(response);

            if (!uploadedFile?.path) {
                dropzone.emit('error', file, 'Response upload tidak mengandung path file.');
                dropzone.removeFile(file);

                return;
            }

            file._uploadedPath = uploadedFile.path;
            setUploadedValue(uploadedFile.path, uploadedFile.url);
        });

        dropzone.on('removedfile', (file) => {
            if (!hiddenInput) {
                return;
            }

            if (file._uploadedPath && hiddenInput.value === file._uploadedPath) {
                setUploadedValue('', '');
            }
        });

        dropzone.on('error', (file, errorMessage) => {
            const message =
                typeof errorMessage === 'string'
                    ? errorMessage
                    : getNestedValue(errorMessage, 'errors.file.0') ??
                      errorMessage?.message ??
                      'Gagal upload file.';

            if (file?.previewElement) {
                const errorNode = file.previewElement.querySelector('[data-dz-errormessage]');

                if (errorNode) {
                    errorNode.textContent = message;
                }
            }
        });
    });
};

const initializeVendorUi = async () => {
    let DataTable = null;

    try {
        const dataTableModule = await import('datatables.net-bs5');
        DataTable = dataTableModule.default;
        window.DataTable = DataTable;
    } catch (error) {
        console.error('Failed to load DataTables.', error);
    }

    try {
        await import('select2/dist/js/select2.full.js');
    } catch (error) {
        console.error('Failed to initialize Select2.', error);
    }

    try {
        if (DataTable) {
            initializeDataTables(DataTable);
        }
    } catch (error) {
        console.error('Failed to initialize DataTables.', error);
    }

    try {
        await import('daterangepicker');
        initializeDateRangePickers();
    } catch (error) {
        console.error('Failed to initialize daterangepicker.', error);
    }

    try {
        await import('bootstrap-fileinput/js/fileinput.js');
        await import('bootstrap-fileinput/themes/bs5/theme.js');
        initializeFileInputs();
    } catch (error) {
        console.error('Failed to initialize bootstrap-fileinput.', error);
    }

    try {
        initializeDropzones();
    } catch (error) {
        console.error('Failed to initialize Dropzone.', error);
    }

    window.appPlugins = {
        $,
        bootstrap,
        moment,
        DataTable,
        Dropzone,
    };
};

window.initializeVendorUi = initializeVendorUi;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        void initializeVendorUi();
    });
} else {
    void initializeVendorUi();
}
