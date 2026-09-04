(function () {
    'use strict';

    const config = window.reportsConfig || {};

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

    function getFilters() {
        return {
            keyword: $('#reportKeyword').val().trim(),
            region_id: $('#reportRegion').val(),
            date_from: $('#reportDateFrom').val(),
            date_to: $('#reportDateTo').val()
        };
    }

    function loadReports() {
        const tbody = $('#reportsTableBody');

        tbody.html(`
            <tr>
                <td colspan="8" class="reports-loading">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Memuat data laporan...
                </td>
            </tr>
        `);

        $.ajax({
            url: config.dataUrl,
            type: 'GET',
            data: getFilters(),
            dataType: 'json',
            cache: false
        }).done(function (res) {
            console.log('REPORT DATA', res);

            if (!res || res.status === false) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Gagal memuat data laporan.
                        </td>
                    </tr>
                `);

                notifyMessage(
                    res?.message || 'Gagal memuat data laporan.',
                    'error'
                );

                return;
            }

            renderReports(res.data || []);
        }).fail(function (xhr) {
            console.error(
                'REPORT DATA ERROR',
                xhr.status,
                xhr.responseText
            );

            let message = 'Gagal memuat data laporan.';

            try {
                const response = JSON.parse(
                    xhr.responseText
                );

                if (response.message) {
                    message = response.message;
                }
            } catch (e) {
            }

            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${escapeHtml(message)}
                    </td>
                </tr>
            `);

            notifyMessage(
                message,
                'error'
            );
        });
    }

    function renderReports(data) {
        const tbody = $('#reportsTableBody');

        $('#reportTotal').text(data.length);

        if (!data.length) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="reports-empty">
                        <i class="fas fa-inbox"></i>
                        <div>Tidak ada data Monev.</div>
                    </td>
                </tr>
            `);

            return;
        }

        let html = '';

        data.forEach(function (row, index) {
            const status = row.status || '';

            const statusClass = {
                DRAFT: 'secondary',
                IN_PROGRESS: 'warning',
                COMPLETED: 'success'
            }[status] || 'secondary';

            const statusText = {
                DRAFT: 'Draft',
                IN_PROGRESS: 'Sedang Berjalan',
                COMPLETED: 'Selesai'
            }[status] || status || '-';

            html += `
                <tr>
                    <td class="text-center">
                        ${index + 1}
                    </td>
                    <td>
                        <strong>
                            ${escapeHtml(row.region_name || '-')}
                        </strong>
                    </td>
                    <td>
                        <div class="fw-semibold">
                            ${escapeHtml(row.school_name || '-')}
                        </div>
                        <small class="text-muted">
                            ${escapeHtml(row.level || '')}
                        </small>
                    </td>
                    <td>
                        ${escapeHtml(row.npsn || '-')}
                    </td>
                    <td>
                        ${formatDate(row.visit_date)}
                    </td>
                    <td>
                        ${escapeHtml(row.member_names || '-')}
                    </td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            ${escapeHtml(statusText)}
                        </span>
                    </td>
                    <td>
                        <button
                            type="button"
                            class="btn btn-sm btn-danger btn-report-pdf"
                            data-id="${escapeHtml(row.id)}">
                            <i class="fas fa-file-pdf me-1"></i>
                            PDF
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);
    }

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

    $(document).on(
        'click',
        '#btnSearchReport',
        function () {
            loadReports();
        }
    );

    $(document).on(
        'click',
        '#btnResetReport',
        function () {
            $('#reportKeyword').val('');
            $('#reportRegion').val('');
            $('#reportDateFrom').val('');
            $('#reportDateTo').val('');

            loadReports();
        }
    );

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

    $(document).on(
        'click',
        '.btn-report-pdf',
        function () {
            const id = $(this).data('id');

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

    $(document).on(
        'click',
        '#btnExportAllPdf',
        function () {
            const query = $.param(
                getFilters()
            );

            const url =
                config.exportAllPdfUrl +
                (query ? '?' + query : '');

            window.open(
                url,
                '_blank'
            );
        }
    );

    $(document).ready(function () {
        loadRegions();
        loadReports();
    });
})();