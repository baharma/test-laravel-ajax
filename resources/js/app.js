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

const initializeSelect2 = () => {
    $('[data-control="select2"]').each(function initializeElement() {
        const $element = $(this);

        if ($element.data('select2')) {
            return;
        }

        const dropdownParent = $element.closest('.modal').length
            ? $element.closest('.modal')
            : $(document.body);

        $element.select2({
            width: $element.data('width') ?? '100%',
            dropdownParent,
            placeholder: $element.data('placeholder') ?? $element.attr('placeholder') ?? '',
            tags: readBoolean($element.data('tags'), false),
            minimumResultsForSearch: readBoolean($element.data('search'), true) ? 0 : Infinity,
        });
    });
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

        $element.daterangepicker({
            autoUpdateInput: false,
            opens: $element.data('opens') ?? 'left',
            ranges: defaultDateRanges,
            buttonClasses: ['btn', 'btn-sm'],
            applyButtonClasses: 'btn-primary',
            cancelButtonClasses: 'btn-outline-secondary',
            locale: {
                cancelLabel: 'Clear',
                format: $element.data('format') ?? 'YYYY-MM-DD',
            },
        });

        $element.on('apply.daterangepicker', (event, picker) => {
            $element.val(
                `${picker.startDate.format('YYYY-MM-DD')} - ${picker.endDate.format('YYYY-MM-DD')}`,
            );
        });

        $element.on('cancel.daterangepicker', () => {
            $element.val('');
        });
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

        new Dropzone(element, {
            url: element.dataset.url || element.getAttribute('action'),
            method: 'post',
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            maxFiles: readNumber(element.dataset.maxFiles, 3),
            maxFilesize: readNumber(element.dataset.maxFilesize, 5),
            acceptedFiles: element.dataset.acceptedFiles ?? 'image/*,.pdf',
            addRemoveLinks: true,
            timeout: 120000,
            dictDefaultMessage: 'Drop files here or click to upload',
        });
    });
};

const initializeVendorUi = async () => {
    try {
        const [{ default: DataTable }] = await Promise.all([
            import('datatables.net-bs5'),
            import('select2/dist/js/select2.full.js'),
            import('daterangepicker'),
            import('bootstrap-fileinput/js/fileinput.js'),
            import('bootstrap-fileinput/themes/bs5/theme.js'),
        ]);

        window.DataTable = DataTable;
        window.appPlugins = {
            $,
            bootstrap,
            moment,
            DataTable,
            Dropzone,
        };

        initializeSelect2();
        initializeDataTables(DataTable);
        initializeDateRangePickers();
        initializeFileInputs();
        initializeDropzones();
    } catch (error) {
        console.error('Failed to initialize vendor UI plugins.', error);
    }
};

window.initializeVendorUi = initializeVendorUi;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        void initializeVendorUi();
    });
} else {
    void initializeVendorUi();
}
