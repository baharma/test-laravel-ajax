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

const normalizeSelect2Items = (response, valueField, textField, resultsKey) => {
    let items = [];

    if (Array.isArray(response)) {
        items = response;
    } else if (resultsKey) {
        items = getNestedValue(response, resultsKey, []);
    } else {
        items = response?.results ?? response?.data ?? response?.items ?? [];
    }

    return (Array.isArray(items) ? items : []).map((item) => {
        if (item && item.id !== undefined && item.text !== undefined) {
            return item;
        }

        return {
            ...item,
            id: item?.[valueField] ?? item?.id ?? item?.value,
            text: item?.[textField] ?? item?.text ?? item?.name ?? item?.label ?? '',
        };
    });
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

        const ajaxUrl = $element.data('ajaxUrl');
        const valueField = $element.data('valueField') ?? 'id';
        const textField = $element.data('textField') ?? 'text';
        const resultsKey = $element.data('resultsKey') ?? null;
        const queryParam = $element.data('queryParam') ?? 'q';
        const pageParam = $element.data('pageParam') ?? 'page';
        const ajaxMethod = ($element.data('ajaxMethod') ?? 'GET').toUpperCase();
        const ajaxDelay = readNumber($element.data('ajaxDelay'), 250);
        const minimumInputLength = readNumber($element.data('minimumInputLength'), 0);
        const allowClear = readBoolean($element.data('allowClear'), true);
        const baseAjaxParams = readJson($element.attr('data-ajax-params'), {});

        const config = {
            width: $element.data('width') ?? '100%',
            dropdownParent,
            placeholder: $element.data('placeholder') ?? $element.attr('placeholder') ?? '',
            tags: readBoolean($element.data('tags'), false),
            minimumResultsForSearch: readBoolean($element.data('search'), true) ? 0 : Infinity,
            minimumInputLength,
            allowClear,
        };

        if (ajaxUrl) {
            config.ajax = {
                url: ajaxUrl,
                type: ajaxMethod,
                delay: ajaxDelay,
                cache: true,
                data: (params) => ({
                    ...baseAjaxParams,
                    [queryParam]: params.term ?? '',
                    [pageParam]: params.page ?? 1,
                }),
                processResults: (response, params) => {
                    const currentPage = params.page ?? 1;
                    const results = normalizeSelect2Items(
                        response,
                        valueField,
                        textField,
                        resultsKey,
                    );

                    const explicitMore = getNestedValue(response, 'pagination.more');
                    const hasPaginatorMeta =
                        getNestedValue(response, 'meta.current_page') !== undefined &&
                        getNestedValue(response, 'meta.last_page') !== undefined;

                    const more = explicitMore !== undefined
                        ? explicitMore
                        : hasPaginatorMeta
                            ? getNestedValue(response, 'meta.current_page') <
                              getNestedValue(response, 'meta.last_page')
                            : getNestedValue(response, 'next_page_url') !== null &&
                              getNestedValue(response, 'next_page_url') !== undefined;

                    return {
                        results,
                        pagination: {
                            more: Boolean(more),
                        },
                    };
                },
            };
        }

        $element.select2(config);
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
