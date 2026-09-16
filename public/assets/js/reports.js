(function () {
    'use strict';

    const config = window.reportsConfig || {};

    let reportsTable = null;

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function notifyMessage(message, type) {
        if (typeof notify === 'function') {
            notify(message, type);
        } else {
            console.error(message);
        }
    }

    // =========================================================
    // LOAD WILAYAH
    // =========================================================
    function loadRegions() {

        const select = $('#reportRegion');

        select.html(
            '<option value="">Memuat wilayah...</option>'
        );

        $.ajax({
            url: config.regionsUrl,
            type: 'GET',
            dataType: 'json',
            cache: false

        }).done(function (res) {

            if (!res || res.status === false) {

                select.html(
                    '<option value="">Semua Wilayah</option>'
                );

                notifyMessage(
                    res?.message || 'Gagal memuat wilayah.',
                    'error'
                );

                return;
            }

            select.html(
                '<option value="">Semua Wilayah</option>'
            );

            (res.data || []).forEach(function (row) {

                select.append(
                    $('<option>', {
                        value: row.id,
                        text: row.name
                    })
                );

            });

        }).fail(function (xhr) {

            console.error(
                'REGIONS ERROR',
                xhr.status,
                xhr.responseText
            );

            select.html(
                '<option value="">Semua Wilayah</option>'
            );

            notifyMessage(
                'Gagal memuat wilayah.',
                'error'
            );
        });
    }


    // =========================================================
    // GET FILTER
    // =========================================================
    function getFilters() {
        return {
            keyword: $('#reportKeyword').val().trim(),
            region_id: $('#reportRegion').val(),
            status: $('#reportStatus').val(),
            date_from: $('#reportDateFrom').val(),
            date_to: $('#reportDateTo').val()
        };
    }


    // =========================================================
    // FORMAT DATE
    // =========================================================
    function formatDate(value) {

        if (!value) {
            return '-';
        }

        const parts = String(value)
            .substring(0, 10)
            .split('-');

        if (parts.length !== 3) {
            return escapeHtml(value);
        }

        return (
            parts[2] +
            '-' +
            parts[1] +
            '-' +
            parts[0]
        );
    }


    // =========================================================
    // STATUS
    // =========================================================
    function getStatus(status) {

        const statusClass = {
            DRAFT: 'secondary',
            IN_PROGRESS: 'warning',
            COMPLETED: 'success'
        };

        const statusText = {
            DRAFT: 'Draft',
            IN_PROGRESS: 'Sedang Berjalan',
            COMPLETED: 'Selesai'
        };

        return {
            className: statusClass[status] || 'secondary',
            text: statusText[status] || status || '-'
        };
    }


    // =========================================================
    // INITIALIZE DATATABLE
    // =========================================================
    function initReportsTable() {

        reportsTable = $('#reportsTable').DataTable({

            data: [],

            pageLength: 10,

            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],

            ordering: true,

            searching: false,

            info: true,

            autoWidth: false,

            responsive: false,

            language: {

                emptyTable:
                    'Tidak ada data Monev.',

                zeroRecords:
                    'Tidak ada data yang sesuai.',

                info:
                    'Menampilkan _START_–_END_ dari _TOTAL_ sekolah',

                infoEmpty:
                    'Menampilkan 0–0 dari 0 sekolah',

                lengthMenu:
                    'Tampilkan _MENU_ data',

                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: '›',
                    previous: '‹'
                }
            },

            columns: [

                // NO
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    width: '55px',

                    render: function (data, type, row, meta) {

                        return meta.row +
                            meta.settings._iDisplayStart +
                            1;
                    }
                },

                // WILAYAH
                {
                    data: 'region_name',

                    render: function (data) {

                        return `
                            <strong>
                                ${escapeHtml(data || '-')}
                            </strong>
                        `;
                    }
                },

                // SEKOLAH
                {
                    data: null,

                    render: function (data) {

                        return `
                            <div class="fw-semibold">
                                ${escapeHtml(
                                    data.school_name || '-'
                                )}
                            </div>

                            <small class="text-muted">
                                ${escapeHtml(
                                    data.level || ''
                                )}
                            </small>
                        `;
                    }
                },

                // NPSN
                {
                    data: 'npsn',

                    render: function (data) {

                        return escapeHtml(
                            data || '-'
                        );
                    }
                },

                // TANGGAL
                {
                    data: 'visit_date',

                    render: function (data) {

                        return formatDate(data);
                    }
                },

                // PETUGAS
                {
                    data: 'member_names',

                    render: function (data) {

                        return escapeHtml(
                            data || '-'
                        );
                    }
                },

                // STATUS
                {
                    data: 'status',

                    render: function (data) {

                        const status =
                            getStatus(data);

                        return `
                            <span class="badge bg-${status.className}">
                                ${escapeHtml(status.text)}
                            </span>
                        `;
                    }
                },

                // LAPORAN
                {
                    data: 'id',

                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '130px',

                    render: function (data) {

                        if (!data) {
                            return '-';
                        }

                        return `
                            <button
                                type="button"
                                class="btn btn-sm btn-danger btn-report-pdf"
                                data-id="${escapeHtml(data)}">

                                <i class="fas fa-file-pdf me-1"></i>
                                PDF

                            </button>
                        `;
                    }
                }
            ]
        });
    }


    // =========================================================
    // LOAD REPORTS VIA AJAX
    // =========================================================
    function loadReports() {

        if (!reportsTable) {
            return;
        }

        const tbody = $('#reportsTable tbody');

        // Loading di dalam tabel
        tbody.html(
            '<tr>' +
                '<td colspan="8" class="school-loading">' +
                    '<div class="school-spinner"></div>' +
                    'Loading data sekolah...' +
                '</td>' +
            '</tr>'
        );

        const filters = getFilters();

        $.ajax({

            url: config.dataUrl,

            type: 'GET',

            data: filters,

            dataType: 'json',

            cache: false

        }).done(function (res) {

            console.log('REPORT DATA', res);

            if (!res || res.status === false) {

                reportsTable
                    .clear()
                    .draw();

                $('#reportTotal').text('0');

                $('#btnExportAllPdf').hide();

                notifyMessage(
                    res?.message ||
                    'Gagal memuat data laporan.',
                    'error'
                );

                return;
            }

            const data = res.data || [];

            $('#reportTotal').text(
                data.length
            );

            if (data.length > 0) {
                $('#btnExportAllPdf').show();
            } else {
                $('#btnExportAllPdf').hide();
            }

            reportsTable
                .clear()
                .rows
                .add(data)
                .draw();

        }).fail(function (xhr) {

            console.error(
                'REPORT DATA ERROR',
                xhr.status,
                xhr.responseText
            );

            reportsTable
                .clear()
                .draw();

            $('#reportTotal').text('0');

            $('#btnExportAllPdf').hide();

            let message =
                'Gagal memuat data laporan.';

            try {

                const response =
                    JSON.parse(
                        xhr.responseText
                    );

                if (response.message) {
                    message =
                        response.message;
                }

            } catch (e) {}

            notifyMessage(
                message,
                'error'
            );
        });
    }


    // =========================================================
    // SEARCH
    // =========================================================
    $(document).on(
        'click',
        '#btnSearchReport',
        function () {

            loadReports();

        }
    );


    // =========================================================
    // RESET
    // =========================================================
    $(document).on(
        'click',
        '#btnResetReport',
        function () {

            $('#reportKeyword').val('');
            $('#reportRegion').val('');
            $('#reportStatus').val('');
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');

            loadReports();
        }
    );


    // =========================================================
    // ENTER SEARCH
    // =========================================================
    $(document).on(
        'keypress',
        '#reportKeyword',
        function (e) {

            if (e.which === 13) {

                e.preventDefault();

                loadReports();
            }
        }
    );


    // =========================================================
    // PDF PER LAPORAN
    // =========================================================
    $(document).on(
        'click',
        '.btn-report-pdf',
        function () {

            const id =
                $(this).data('id');

            if (!id) {
                return;
            }

            window.open(
                config.pdfUrl +
                '/' +
                encodeURIComponent(id),

                '_blank'
            );
        }
    );


    // =========================================================
    // EXPORT ALL PDF
    // =========================================================
    $(document).on(
        'click',
        '#btnExportAllPdf',
        function () {

            const query =
                $.param(
                    getFilters()
                );

            const url =
                config.exportAllPdfUrl +
                (
                    query
                        ? '?' + query
                        : ''
                );

            window.open(
                url,
                '_blank'
            );
        }
    );


    // =========================================================
    // DOCUMENT READY
    // =========================================================
    $(document).ready(function () {

        loadRegions();

        initReportsTable();

        loadReports();

    });

})();