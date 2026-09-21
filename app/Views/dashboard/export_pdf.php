<?php

$e = static function ($value) {
    return esc((string) ($value ?? '-'));
};

$num = static function ($value) {
    return number_format((float) ($value ?? 0), 0, ',', '.');
};

$decimal = static function ($value) {
    return number_format((float) ($value ?? 0), 1, ',', '.');
};

$summary = $summary ?? [];

$totalSchools = (int) ($summary['totalSchools'] ?? 0);
$visitedSchools = (int) ($summary['visitedSchools'] ?? 0);
$inProgressSchools = (int) ($summary['inProgressSchools'] ?? 0);
$readinessPercent = (float) ($summary['readinessPercent'] ?? 0);

$visitsByLevel = $visitsByLevel ?? [
    'SMA' => 0,
    'SMK' => 0,
    'MA'  => 0
];

$visitsByRegion = $visitsByRegion ?? [];

$readiness = $readiness ?? [
    'Sangat Baik' => 0,
    'Baik' => 0,
    'Cukup' => 0,
    'Kurang Memadai' => 0
];

$readinessData = $readinessData ?? [];

$monevStatus = $monevStatus ?? [];
$officerRecap = $officerRecap ?? [];

$problemRecommendations =
    $problemRecommendations ?? [];

$electricity = $electricity ?? [];
$internet = $internet ?? [];
$ispUtama = $ispUtama ?? [];
$ispCadangan = $ispCadangan ?? [];

$students = $students ?? [];
$sessions = $sessions ?? [];
$waves = $waves ?? [];

$infrastructure = $infrastructure ?? [];

$filterStart = $filters['start_date'] ?? '';
$filterEnd = $filters['end_date'] ?? '';
$filterLevel = $filters['level'] ?? '';
$filterRegion = $filters['region_id'] ?? '';
$filterDistrict = $filters['district_id'] ?? '';

$periode = 'Semua Periode';

if ($filterStart || $filterEnd) {
    $periode =
        ($filterStart ?: '-') .
        ' s.d. ' .
        ($filterEnd ?: '-');
}

/*
|--------------------------------------------------------------------------
| SVG BAR CHART
|--------------------------------------------------------------------------
*/

$barChart = static function (
    $title,
    $data,
    $suffix = ''
) use ($e, $num) {

    $data = is_array($data) ? $data : [];

    if (empty($data)) {
        return '
        <div class="chart-empty">
            Tidak ada data
        </div>';
    }

    $max = max(array_values($data));
    $max = $max > 0 ? $max : 1;

    ob_start();
    ?>

    <div class="chart-card">

        <div class="chart-title">
            <?= $e($title) ?>
        </div>

        <?php foreach ($data as $label => $value): ?>

            <?php
            $width = ($value / $max) * 100;
            ?>

            <div class="bar-row">

                <div class="bar-label">
                    <?= $e($label) ?>
                </div>

                <div class="bar-background">
                    <div
                        class="bar-fill"
                        style="width:<?= $width ?>%;"
                    ></div>
                </div>

                <div class="bar-number">
                    <?= $num($value) ?>
                    <?= $e($suffix) ?>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <?php
    return ob_get_clean();
};

/*
|--------------------------------------------------------------------------
| DETAIL TABLE
|--------------------------------------------------------------------------
*/

$detailTable = static function (
    $title,
    $headers,
    $rows
) use ($e) {

    ob_start();
    ?>

    <div class="detail-title">
        <?= $e($title) ?>
    </div>

    <?php if (empty($rows)): ?>

        <div class="empty">
            Tidak ada data.
        </div>

    <?php else: ?>

        <table class="data-table">

            <thead>

                <tr>

                    <th style="width:35px;">
                        No
                    </th>

                    <?php foreach ($headers as $header): ?>

                        <th>
                            <?= $e($header) ?>
                        </th>

                    <?php endforeach; ?>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($rows as $index => $row): ?>

                    <tr>

                        <td class="center">
                            <?= $index + 1 ?>
                        </td>

                        <?php foreach ($row as $value): ?>

                            <td>
                                <?= $e($value) ?>
                            </td>

                        <?php endforeach; ?>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <?php
    return ob_get_clean();
};

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<title>
    Laporan Executive Monev TKAP
</title>

<style>

@page {
    size: A4 portrait;
    margin: 12mm 11mm 15mm 11mm;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5pt;
    color: #1e293b;
    line-height: 1.35;
}

.header {
    background: #2563eb;
    color: #ffffff;
    padding: 18px 20px;
    border-radius: 8px;
    margin-bottom: 12px;
}

.header-title {
    font-size: 18pt;
    font-weight: bold;
    margin-bottom: 4px;
}

.header-subtitle {
    font-size: 9pt;
    opacity: .95;
}

.header-line {
    border-top: 1px solid rgba(255,255,255,.4);
    margin: 10px 0 7px;
}

.header-info {
    font-size: 7.5pt;
}

.header-info span {
    margin-right: 18px;
}

.filter-box {
    background: #f8fafc;
    border: 1px solid #dbe3ec;
    border-radius: 5px;
    padding: 7px 9px;
    margin-bottom: 12px;
}

.filter-table {
    width: 100%;
    border-collapse: collapse;
}

.filter-table td {
    padding: 3px 5px;
}

.filter-label {
    font-weight: bold;
    color: #475569;
    width: 13%;
}

.summary-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 7px 0;
    margin: 0 -7px 14px;
}

.summary-card {
    border: 1px solid #dbe3ec;
    background: #ffffff;
    border-radius: 5px;
    padding: 10px 6px;
    text-align: center;
    height: 70px;
}

.summary-value {
    font-size: 18pt;
    font-weight: bold;
    color: #1e40af;
}

.summary-label {
    font-size: 7.5pt;
    font-weight: bold;
    color: #64748b;
    margin-top: 2px;
}

.section {
    margin-top: 13px;
}

.section-title {
    border-left: 4px solid #2563eb;
    padding-left: 8px;
    font-size: 11pt;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 7px;
}

.section-description {
    font-size: 7.8pt;
    color: #64748b;
    margin-bottom: 7px;
}

.two-column {
    width: 100%;
    border-spacing: 7px;
}

.two-column td {
    width: 50%;
    vertical-align: top;
}

.chart-card {
    border: 1px solid #dbe3ec;
    border-radius: 5px;
    padding: 9px;
    margin-bottom: 8px;
    background: #ffffff;
}

.chart-title {
    font-weight: bold;
    text-align: center;
    font-size: 8.5pt;
    margin-bottom: 9px;
    color: #334155;
}

.bar-row {
    width: 100%;
    height: 18px;
    margin-bottom: 4px;
}

.bar-label {
    display: inline-block;
    width: 28%;
    vertical-align: middle;
    font-size: 7.5pt;
}

.bar-background {
    display: inline-block;
    width: 57%;
    height: 10px;
    background: #e2e8f0;
    vertical-align: middle;
}

.bar-fill {
    height: 10px;
    background: #2563eb;
}

.bar-number {
    display: inline-block;
    width: 13%;
    text-align: right;
    font-size: 7.5pt;
    font-weight: bold;
    vertical-align: middle;
}

.chart-empty {
    border: 1px solid #dbe3ec;
    padding: 20px;
    text-align: center;
    color: #64748b;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    page-break-inside: auto;
}

.data-table thead {
    display: table-header-group;
}

.data-table tr {
    page-break-inside: avoid;
}

.data-table th {
    background: #eef2f7;
    color: #334155;
    border: 1px solid #d7dee8;
    padding: 5px 5px;
    font-size: 7.5pt;
    font-weight: bold;
}

.data-table td {
    border: 1px solid #dfe5ec;
    padding: 4px 5px;
    font-size: 7.5pt;
    vertical-align: top;
}

.data-table tr:nth-child(even) td {
    background: #f8fafc;
}

.center {
    text-align: center;
}

.detail-title {
    font-size: 8.5pt;
    font-weight: bold;
    color: #334155;
    margin: 7px 0 4px;
}

.empty {
    padding: 8px;
    border: 1px solid #dbe3ec;
    color: #64748b;
}

.status-good {
    background: #dcfce7;
    color: #166534;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: bold;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: bold;
}

.status-danger {
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: bold;
}

.problem-box {
    border: 1px solid #dbe3ec;
    border-radius: 5px;
    padding: 9px;
    margin-bottom: 8px;
    page-break-inside: avoid;
}

.problem-number {
    display: inline-block;
    width: 22px;
    height: 22px;
    background: #2563eb;
    color: #ffffff;
    text-align: center;
    padding-top: 4px;
    border-radius: 50%;
    font-weight: bold;
}

.problem-title {
    display: inline-block;
    font-weight: bold;
    margin-left: 5px;
    font-size: 8.5pt;
}

.problem-total {
    float: right;
    font-weight: bold;
    color: #1e40af;
}

.problem-detail {
    margin-top: 6px;
    font-size: 7.8pt;
    color: #475569;
}

.recommendation {
    margin-top: 7px;
    background: #eff6ff;
    border-left: 3px solid #2563eb;
    padding: 6px 8px;
    font-size: 7.8pt;
}

.page-break {
    page-break-before: always;
}

.footer {
    margin-top: 15px;
    border-top: 1px solid #cbd5e1;
    padding-top: 6px;
    font-size: 7pt;
    color: #64748b;
}

.signature {
    margin-top: 45px;
    width: 100%;
    border-collapse: collapse;
}

.signature td {
    width: 50%;
    text-align: center;
    vertical-align: top;
}

.signature-space {
    height: 55px;
}

</style>

</head>

<body>

<!-- =========================================================
     HEADER
========================================================= -->

<div class="header">

    <div class="header-title">
        LAPORAN EXECUTIVE MONITORING & EVALUASI
    </div>

    <div class="header-subtitle">
        Analisis Kesiapan Infrastruktur & Progres Monev TKAP Sekolah
    </div>

    <div class="header-line"></div>

    <div class="header-info">

        <span>
            <strong>Periode:</strong>
            <?= $e($periode) ?>
        </span>

        <span>
            <strong>Jenjang:</strong>
            <?= $e($filterLevel ?: 'Semua') ?>
        </span>

        <span>
            <strong>Wilayah:</strong>
            <?= $e($filterRegion ?: 'Semua') ?>
        </span>

        <span>
            <strong>Kecamatan:</strong>
            <?= $e($filterDistrict ?: 'Semua') ?>
        </span>

    </div>

</div>

<!-- =========================================================
     SUMMARY
========================================================= -->

<table class="summary-table">

<tr>

<td class="summary-card">

    <div class="summary-value">
        <?= $num($totalSchools) ?>
    </div>

    <div class="summary-label">
        TOTAL SEKOLAH
    </div>

</td>

<td class="summary-card">

    <div class="summary-value">
        <?= $num($visitedSchools) ?>
    </div>

    <div class="summary-label">
        SUDAH MONEV
    </div>

</td>

<td class="summary-card">

    <div class="summary-value">
        <?= $num($inProgressSchools) ?>
    </div>

    <div class="summary-label">
        DALAM PROSES
    </div>

</td>

<td class="summary-card">

    <div class="summary-value">
        <?= $decimal($readinessPercent) ?>%
    </div>

    <div class="summary-label">
        KESIAPAN BAIK
    </div>

</td>

</tr>

</table>

<!-- =========================================================
     1. STATUS & JENJANG
========================================================= -->

<div class="section">

    <div class="section-title">
        1. RINGKASAN STATUS MONEV & DISTRIBUSI JENJANG
    </div>

    <table class="two-column">

        <tr>

            <td>

                <?php

                $statusChart = [
                    'Sudah Monev' =>
                        (int)($monevStatus['sudah_monev'] ?? $visitedSchools),

                    'Sedang Berlangsung' =>
                        (int)($monevStatus['sedang_berlangsung'] ?? $inProgressSchools),

                    'Draft' =>
                        (int)($monevStatus['belum_monev'] ?? 0)
                ];

                echo $barChart(
                    'Status Progres Kunjungan',
                    $statusChart
                );

                ?>

            </td>

            <td>

                <?php

                echo $barChart(
                    'Cakupan Sekolah per Jenjang',
                    $visitsByLevel,
                    'Sekolah'
                );

                ?>

            </td>

        </tr>

    </table>

    <?php

    $statusRows = [
        [
            'Sudah Monev',
            $statusChart['Sudah Monev']
        ],
        [
            'Sedang Berlangsung',
            $statusChart['Sedang Berlangsung']
        ],
        [
            'Draft',
            $statusChart['Draft']
        ]
    ];

    echo $detailTable(
        'Detail Status Monev',
        [
            'Status',
            'Jumlah'
        ],
        $statusRows
    );

    ?>

</div>

<!-- =========================================================
     2. KESIAPAN
========================================================= -->

<div class="section">

    <div class="section-title">
        2. DISTRIBUSI KESIAPAN INFRASTRUKTUR & JARINGAN
    </div>

    <table class="data-table">

        <thead>

            <tr>

                <th>
                    Parameter Kesiapan
                </th>

                <th>
                    Sangat Baik
                </th>

                <th>
                    Baik
                </th>

                <th>
                    Cukup
                </th>

                <th>
                    Kurang Memadai
                </th>

                <th>
                    Tingkat Pemenuhan
                </th>

            </tr>

        </thead>

        <tbody>

            <?php

            $readinessTotal = array_sum($readiness);

            $readinessGood =
                ($readiness['Sangat Baik'] ?? 0) +
                ($readiness['Baik'] ?? 0);

            $readinessRate =
                $readinessTotal > 0
                    ? round(
                        ($readinessGood / $readinessTotal) * 100,
                        1
                    )
                    : 0;

            ?>

            <tr>

                <td>
                    <strong>
                        Kesiapan Umum
                    </strong>
                </td>

                <td>
                    <?= $num($readiness['Sangat Baik'] ?? 0) ?>
                    Sekolah
                </td>

                <td>
                    <?= $num($readiness['Baik'] ?? 0) ?>
                    Sekolah
                </td>

                <td>
                    <?= $num($readiness['Cukup'] ?? 0) ?>
                    Sekolah
                </td>

                <td>
                    <?= $num($readiness['Kurang Memadai'] ?? 0) ?>
                    Sekolah
                </td>

                <td>

                    <span class="status-good">
                        <?= $decimal($readinessRate) ?>%
                    </span>

                </td>

            </tr>

            <tr>

                <td>
                    <strong>
                        Sumber Daya Listrik
                    </strong>
                </td>

                <td colspan="4">
                    Berdasarkan data kelistrikan hasil Monev.
                </td>

                <td>
                    <?= $num(count($electricity['data'] ?? [])) ?>
                    Sekolah
                </td>

            </tr>

            <tr>

                <td>
                    <strong>
                        Koneksi Internet Utama
                    </strong>
                </td>

                <td colspan="4">
                    Berdasarkan data jaringan internet hasil Monev.
                </td>

                <td>
                    <?= $num(count($internet['data'] ?? [])) ?>
                    Sekolah
                </td>

            </tr>

            <tr>

                <td>
                    <strong>
                        ISP Cadangan
                    </strong>
                </td>

                <td colspan="4">
                    Berdasarkan data bandwidth ISP cadangan.
                </td>

                <td>
                    <?= $num(count($ispCadangan['data'] ?? [])) ?>
                    Sekolah
                </td>

            </tr>

        </tbody>

    </table>

</div>

<!-- =========================================================
     DETAIL KESIAPAN
========================================================= -->

<div class="section">

    <?php

    $rows = [];

    foreach ($readinessData as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['answer'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail Kesiapan Infrastruktur per Sekolah',
        [
            'Sekolah',
            'NPSN',
            'Status Kesiapan'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     3. WILAYAH
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        3. REKAPITULASI DISTRIBUSI MONEV PER WILAYAH
    </div>

    <div class="section-description">

        Distribusi sekolah yang telah masuk dalam hasil monitoring
        berdasarkan kode wilayah.

    </div>

    <?php

    $regionRows = [];

    foreach ($visitsByRegion as $code => $total) {

        $regionRows[] = [
            $code,
            $total
        ];

    }

    echo $detailTable(
        'Distribusi Sekolah per Wilayah',
        [
            'Kode Wilayah',
            'Jumlah Sekolah'
        ],
        $regionRows
    );

    ?>

</div>

<!-- =========================================================
     4. PETUGAS
========================================================= -->

<div class="section">

    <div class="section-title">
        4. KINERJA PETUGAS KUNJUNGAN FIELD OFFICER
    </div>

    <div class="section-description">

        Rekapitulasi beban kerja dan penyelesaian tugas lapangan
        berdasarkan data Monev.

    </div>

    <?php

    $officerRows = [];

    foreach (($officerRecap['data'] ?? []) as $item) {

        $officerRows[] = [

            $item['officer_name'] ?? '-',

            $item['region_name'] ?? '-',

            ($item['jumlah_sasaran'] ?? 0) .
            ' Sekolah',

            $item['sudah_monev'] ?? 0,

            $item['sedang_berlangsung'] ?? 0,

            $item['belum_monev'] ?? 0,

            ($item['persentase'] ?? 0) . '%',

            $item['keterangan'] ?? '-'

        ];

    }

    echo $detailTable(
        'Rekap Kinerja Petugas',
        [
            'Nama Petugas',
            'Wilayah',
            'Sasaran',
            'Selesai',
            'Berlangsung',
            'Belum Monev',
            'Progres',
            'Keterangan'
        ],
        $officerRows
    );

    ?>

</div>

<!-- =========================================================
     5. MASALAH & REKOMENDASI
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        5. REKAPITULASI MASALAH & REKOMENDASI SOLUSI
    </div>

    <div class="section-description">

        Identifikasi hambatan teknis berdasarkan hasil pengolahan
        data Monev dan rekomendasi tindak lanjut.

    </div>

    <?php foreach ($problemRecommendations as $item): ?>

        <div class="problem-box">

            <span class="problem-number">
                <?= $e($item['no'] ?? '-') ?>
            </span>

            <span class="problem-title">
                <?= $e($item['problem'] ?? '-') ?>
            </span>

            <span class="problem-total">
                <?= $num($item['total_school'] ?? 0) ?>
                Sekolah
            </span>

            <?php if (!empty($item['details'])): ?>

                <div class="problem-detail">

                    <?php foreach ($item['details'] as $detail): ?>

                        <?php

                        $school =
                            $detail['school'] ?? '-';

                        ?>

                        <div style="margin-bottom:4px;">

                            <strong>
                                <?= $e($school) ?>
                            </strong>

                            <?php if (
                                isset($detail['available']) &&
                                isset($detail['total_kebutuhan'])
                            ): ?>

                                — tersedia
                                <?= $num($detail['available']) ?>
                                unit dari kebutuhan
                                <?= $num($detail['total_kebutuhan']) ?>
                                unit.

                            <?php elseif (
                                isset($detail['bandwidth']) &&
                                isset($detail['need'])
                            ): ?>

                                — bandwidth
                                <?= $e($detail['bandwidth']) ?>
                                Mbps dari kebutuhan
                                <?= $e($detail['need']) ?>
                                Mbps.

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="recommendation">

                <strong>
                    Rekomendasi:
                </strong>

                <?= $e($item['recommendation'] ?? 'Tidak ada') ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<!-- =========================================================
     6. DETAIL LISTRIK
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        6. DATA DETAIL KELISTRIKAN SEKOLAH
    </div>

    <?php

    $rows = [];

    foreach (($electricity['data'] ?? []) as $item) {

        $value = $item['value'] ?? '-';

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $value
        ];

    }

    echo $detailTable(
        'Detail Daya Listrik',
        [
            'Sekolah',
            'NPSN',
            'Daya'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     7. INTERNET
========================================================= -->

<div class="section">

    <div class="section-title">
        7. DATA DETAIL JARINGAN INTERNET
    </div>

    <?php

    $rows = [];

    foreach (($internet['data'] ?? []) as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['value'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail Jaringan Internet',
        [
            'Sekolah',
            'NPSN',
            'Jaringan'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     8. ISP UTAMA
========================================================= -->

<div class="section">

    <div class="section-title">
        8. DATA DETAIL BANDWIDTH ISP UTAMA
    </div>

    <?php

    $rows = [];

    foreach (($ispUtama['data'] ?? []) as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['value'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail ISP Utama',
        [
            'Sekolah',
            'NPSN',
            'Bandwidth'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     9. ISP CADANGAN
========================================================= -->

<div class="section">

    <div class="section-title">
        9. DATA DETAIL BANDWIDTH ISP CADANGAN
    </div>

    <?php

    $rows = [];

    foreach (($ispCadangan['data'] ?? []) as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['value'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail ISP Cadangan',
        [
            'Sekolah',
            'NPSN',
            'Bandwidth'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     10. SISWA
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        10. KEIKUTSERTAAN SISWA DALAM TKAP
    </div>

    <?php

    $rows = [];

    foreach ($students as $item) {

        $rows[] = [

            $item['school_name'] ?? '-',

            $item['npsn'] ?? '-',

            $num($item['total'] ?? 0),

            $num($item['ikut'] ?? 0),

            $num($item['tidak_ikut'] ?? 0),

            ($item['percentage'] ?? 0) . '%'

        ];

    }

    echo $detailTable(
        'Detail Keikutsertaan Siswa',
        [
            'Sekolah',
            'NPSN',
            'Jumlah Siswa',
            'Mengikuti',
            'Tidak Mengikuti',
            'Persentase'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     11. SESI & GELOMBANG
========================================================= -->

<div class="section">

    <div class="section-title">
        11. DISTRIBUSI SESI DAN GELOMBANG
    </div>

    <table class="two-column">

        <tr>

            <td>

                <?= $barChart(
                    'Distribusi Sesi',
                    $sessions['distribution'] ?? []
                ) ?>

            </td>

            <td>

                <?= $barChart(
                    'Distribusi Gelombang',
                    $waves['distribution'] ?? []
                ) ?>

            </td>

        </tr>

    </table>

    <?php

    $rows = [];

    foreach (($sessions['data'] ?? []) as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['value'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail Sesi',
        [
            'Sekolah',
            'NPSN',
            'Sesi'
        ],
        $rows
    );

    ?>

    <?php

    $rows = [];

    foreach (($waves['data'] ?? []) as $item) {

        $rows[] = [
            $item['school_name'] ?? '-',
            $item['npsn'] ?? '-',
            $item['value'] ?? '-'
        ];

    }

    echo $detailTable(
        'Detail Gelombang',
        [
            'Sekolah',
            'NPSN',
            'Gelombang'
        ],
        $rows
    );

    ?>

</div>

<!-- =========================================================
     12. INFRASTRUKTUR
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        12. DATA DETAIL INFRASTRUKTUR
    </div>

    <?php

    foreach ($infrastructure as $code => $items):

        if (!is_array($items)) {
            continue;
        }

        if (isset($items['data'])) {

            $rows = [];

            foreach ($items['data'] as $item) {

                $rows[] = [

                    $item['school_name'] ?? '-',

                    $item['npsn'] ?? '-',

                    $item['value'] ?? '-'

                ];

            }

            echo $detailTable(
                $code,
                [
                    'Sekolah',
                    'NPSN',
                    'Nilai'
                ],
                $rows
            );
        }

    endforeach;

    ?>

</div>

<!-- =========================================================
     PENGESAHAN
========================================================= -->

<div class="section page-break">

    <div class="section-title">
        13. LEMBAR PENGESAHAN
    </div>

    <p style="text-align:justify;">
        Demikian laporan hasil olahan data monitoring dan evaluasi
        kesiapan infrastruktur TKAP ini disusun sebagai bahan
        monitoring, evaluasi, dan tindak lanjut pelaksanaan
        TKAP di satuan pendidikan.
    </p>

    <table class="signature">

        <tr>

            <td>
                Disiapkan Oleh,
                <br>
                Koordinator Tim Monev

                <div class="signature-space"></div>

                <strong>
                    ( ........................................ )
                </strong>

                <br>

                NIP. ........................................
            </td>

            <td>
                Disetujui Oleh,
                <br>
                Kepala Penanggung Jawab TKAP

                <div class="signature-space"></div>

                <strong>
                    ( ........................................ )
                </strong>

                <br>

                NIP. ........................................
            </td>

        </tr>

    </table>

</div>

<div class="footer">

    Laporan Executive Monitoring & Evaluasi TKAP —
    DashboardModel CI4 —
    Tahun 2026

</div>

</body>

</html>