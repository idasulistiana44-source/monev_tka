<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Laporan Monitoring & Evaluasi TKAP') ?></title>
<style>
@page {
    size: A4 portrait;
    margin: 12mm 10mm;
}
body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    line-height: 1.45;
    color: #1e293b;
}
.header {
    border-bottom: 2px solid #334155;
    padding-bottom: 8px;
    margin-bottom: 12px;
}
.header-title {
    font-size: 13px;
    font-weight: bold;
}
.header-subtitle {
    font-size: 9px;
    color: #64748b;
    margin-top: 2px;
}
.report-title {
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    margin-top: 8px;
}
.report-subtitle {
    text-align: center;
    font-size: 10px;
    color: #64748b;
    margin-top: 3px;
    margin-bottom: 12px;
}
.summary-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}
.summary-card {
    width: 20%;
    border: 1px solid #cbd5e1;
    text-align: center;
    padding: 8px 4px;
}
.summary-label {
    font-size: 7px;
    font-weight: bold;
    color: #64748b;
}
.summary-value {
    font-size: 16px;
    font-weight: bold;
    color: #1d4ed8;
    margin-top: 4px;
}
.summary-note {
    font-size: 7px;
    color: #64748b;
    margin-top: 2px;
}
.section {
    margin-top: 14px;
}
.section-title {
    border-left: 4px solid #2563eb;
    padding-left: 7px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
}
.section-description {
    font-size: 9px;
    color: #475569;
    margin-bottom: 7px;
}
.chart-table {
    width: 100%;
    border-collapse: collapse;
}
.chart-box {
    width: 50%;
    border: 1px solid #cbd5e1;
    padding: 8px;
    vertical-align: top;
}
.chart-title {
    text-align: center;
    font-size: 9px;
    font-weight: bold;
    margin-bottom: 8px;
}
.bar-row {
    margin-bottom: 7px;
}
.bar-label {
    display: inline-block;
    width: 29%;
    font-size: 8px;
}
.bar-background {
    display: inline-block;
    width: 54%;
    height: 9px;
    background: #e2e8f0;
    vertical-align: middle;
}
.bar {
    height: 9px;
    background: #2563eb;
}
.bar-value {
    display: inline-block;
    width: 12%;
    text-align: right;
    font-size: 8px;
    font-weight: bold;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}
.table th {
    background: #334155;
    color: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 6px 5px;
    font-size: 8px;
    text-align: center;
}
.table td {
    border: 1px solid #cbd5e1;
    padding: 5px;
    font-size: 8px;
    vertical-align: top;
}
.table tr:nth-child(even) td {
    background: #f8fafc;
}
.center {
    text-align: center;
}
.blue {
    color: #1d4ed8;
    font-weight: bold;
}
.detail-box {
    border: 1px solid #cbd5e1;
    padding: 9px;
    margin-top: 10px;
    margin-bottom: 12px;
}
.detail-title {
    font-size: 11px;
    font-weight: bold;
    margin-bottom: 5px;
}
.detail-description {
    font-size: 9px;
    line-height: 1.5;
    margin-bottom: 6px;
}
.recommendation {
    margin-top: 7px;
    margin-bottom: 7px;
    padding: 7px 8px;
    background: #f8fafc;
    border-left: 3px solid #2563eb;
    font-size: 9px;
    line-height: 1.5;
}
.detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 7px;
}
.detail-table th {
    background: #e2e8f0;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    padding: 5px;
    font-size: 8px;
    text-align: center;
}
.detail-table td {
    border: 1px solid #cbd5e1;
    padding: 5px;
    font-size: 8px;
    vertical-align: top;
}
.small-note {
    font-size: 8px;
    color: #64748b;
    margin-top: 5px;
}
.footer {
    border-top: 1px solid #cbd5e1;
    margin-top: 14px;
    padding-top: 5px;
    text-align: center;
    font-size: 7px;
    color: #64748b;
}
.text-danger{
    color:#dc2626;
    font-weight:700;
}
.status-danger{
    color:#dc2626;
    font-weight:700;
}
.status-success{
    color:#15803d;
    font-weight:700;
}
.infra-category{
    margin-top:18px;
    page-break-inside:auto !important;
    break-inside:auto !important;
}
.infra-category-header{
    border-bottom:1px solid #dbe2ea;
    padding:0 0 8px 0;
    margin-bottom:10px;
}
.infra-category-title{
    font-size:15px;
    font-weight:700;
    color:#1f2937;
}
.infra-category-description{
    font-size:10px;
    color:#64748b;
    margin-top:3px;
}
.infra-chart{
    width:100%;
    padding:6px 0 10px 0;
}
.infra-chart-item{
    width:100%;
    margin-bottom:7px;
    font-size:9px;
}
.infra-chart-school{
    width:28%;
    display:inline-block;
    vertical-align:middle;
    color:#334155;
    white-space:nowrap;
    overflow:hidden;
}
.infra-chart-track{
    width:58%;
    height:13px;
    display:inline-block;
    vertical-align:middle;
    background:#e5e7eb;
    border-radius:3px;
    overflow:hidden;
}
.infra-chart-bar{
    height:13px;
    background:#4aa3df;
    border-radius:3px;
}
.infra-chart-number{
    width:10%;
    display:inline-block;
    vertical-align:middle;
    text-align:right;
    font-weight:700;
    color:#334155;
}
.infra-summary{
    width:100%;
    border-top:1px solid #e2e8f0;
    border-bottom:1px solid #e2e8f0;
    padding:8px 0;
    margin:4px 0 10px 0;
}
.infra-summary-box{
    width:23%;
    display:inline-block;
    vertical-align:top;
    background:#f8fafc;
    border:1px solid #eef2f7;
    padding:6px 8px;
    margin-right:1%;
    box-sizing:border-box;
}
.infra-summary-box:last-child{
    margin-right:0;
}
.infra-summary-box span{
    display:block;
    font-size:8px;
    color:#64748b;
    margin-bottom:3px;
}
.infra-summary-box strong{
    display:block;
    font-size:12px;
    color:#1f2937;
}
.infra-detail-page{
    width:100%;
}
.detail-page-break{
    page-break-after:always;
}
.detail-info{
    font-size:10px;
    font-weight:700;
    color:#334155;
    margin-top:10px;
    margin-bottom:6px;
}
.detail-count{
    font-weight:400;
    color:#64748b;
    margin-left:6px;
}

.infra-category{
    margin-top:18px;
    page-break-inside:auto !important;
    break-inside:auto !important;
}
.detail-table{
    width:100%;
    page-break-inside:auto !important;
    break-inside:auto !important;
}
.detail-table thead{
    display:table-header-group;
}
.detail-table tbody{
    display:table-row-group;
}
.detail-table tr{
    page-break-inside:avoid;
    break-inside:avoid;
}
.center{
    text-align:center;
}
.empty-data{
    padding:12px;
    text-align:center;
    font-size:10px;
    color:#64748b;
    border:1px solid #e2e8f0;
}
/* Mengatur baris agar tetap 1 baris (tidak bertumpuk) */
.electricity-chart-row {
    width: 100%;
    margin-bottom: 6px;
    white-space: nowrap;
    font-size: 10px;
    height: 18px;
    line-height: 18px;
    box-sizing: border-box;
}

/* PEMBUNGKUS (Untuk Chart Daya Listrik) */
.electricity-rank-label {
    display: inline-block;
    width: 236px; /* 20px rank + 210px label + 6px margin */
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 1. Rangking (Untuk Chart Sekolah & Chart Daya) */
.electricity-rank {
    display: inline-block;
    width: 20px;
    text-align: right;
    font-weight: bold;
    vertical-align: middle;
}

/* 2. Nama Sekolah / Nilai Daya */
.electricity-label {
    display: inline-block;
    width: 210px;
    margin-left: 6px;
    font-weight: bold;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Khusus jika .electricity-label berada di dalam .electricity-rank-label */
.electricity-rank-label .electricity-label {
    width: auto; /* Agar tidak double width */
}

/* 3. Track Bar (Lebar otomatis menyesuaikan sisa ruang) */
.electricity-track {
    display: inline-block;
    width: calc(100% - 360px);
    height: 14px;
    vertical-align: middle;
    background: #e2e8f0;
    margin: 0 8px;
    overflow: hidden;
    border-radius: 2px;
}

.electricity-bar {
    display: block;
    height: 100%;
    min-width: 2px;
    background: #2563eb;
    border-radius: 2px 0 0 2px;
}

/* 4. Keterangan Angka (Siswa / Sekolah / Watt) */
.electricity-count {
    display: inline-block;
    width: 80px;
    vertical-align: middle;
    white-space: nowrap;
    text-align: right;
    font-weight: bold;
    color: #0f172a;
}
.page-break-before {
    page-break-before: always;
     break-before:page;
}
.heading-title{
    font-size: 11px;
    font-weight: bold;
    color: #334155;
    margin-top: 12px;
    margin-bottom: 7px;
    padding: 5px 8px;
    border-left: 3px solid #334155;
    background: #f1f5f9;
}
.readiness-card {

    margin-top: 12px;

    border: 1px solid #e5e7eb;

    border-radius: 5px;

    background: #fff;

    overflow: hidden;

}

.readiness-card-title {

    padding: 10px 12px 0;

    font-size: 13px;

    font-weight: 600;

    color: #374151;

}

.readiness-card-description {

    padding: 2px 12px 8px;

    font-size: 8px;

    color: #6b7280;

}

.readiness-chart {

    height: 215px;

    text-align: center;

}

.readiness-chart svg {

    width: 300px;

    height: 215px;

}

.readiness-legend {

    display: flex;

    justify-content: center;

    gap: 15px;

    padding: 5px 10px 10px;

    border-bottom: 1px solid #e5e7eb;

    font-size: 8px;

}

.readiness-legend i {

    display: inline-block;

    width: 25px;

    height: 7px;

    margin-right: 3px;

}
.legend-blue {
    background: #36a2eb;
}

.legend-pink {
    background: #ff6384;
}

.legend-orange {
    background: #ff9f40;
}

.legend-yellow {
    background: #ffcd56;
}

.readiness-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;
    padding: 10px;
    background: #f8fafc;
}

.readiness-summary > div {
    padding: 7px 9px;
    border-left: 3px solid #d1d5db;
}

.readiness-summary > div:nth-child(1) {
    border-left-color: #198754;
}

.readiness-summary > div:nth-child(2) {
    border-left-color: #0d6efd;
}

.readiness-summary > div:nth-child(3) {
    border-left-color: #ffc107;
}

.readiness-summary > div:nth-child(4) {
    border-left-color: #dc3545;
}

.readiness-summary small {
    display: block;
    font-size: 7px;
    color: #6b7280;
}

.readiness-summary strong {
    display: block;
    margin-top: 3px;
    font-size: 13px;
    color: #374151;
}

.readiness-detail {
    margin-top: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    overflow: hidden;
}

.readiness-detail-title {
    padding: 10px 12px 0;
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.readiness-detail-description {
    padding: 2px 12px 8px;
    font-size: 8px;
    color: #6b7280;
}

.readiness-detail table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8px;
}

.readiness-detail th {
    background: #f8fafc;
    padding: 7px;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}

.readiness-detail td {
    padding: 7px;
    border-bottom: 1px solid #e5e7eb;
}

.text-center {
    text-align: center;
}
</style>
</head>
<body>
    <?php
    $summary = $summary ?? [];
    $monevStatus = $monevStatus ?? [];
    $officerRecap = $officerRecap ?? [];
    $problemRecommendations = $problemRecommendations ?? [];
    $visitsByLevel = $visitsByLevel ?? [];
    $totalSchools = (int) ($summary['totalSchools'] ?? $summary['total_schools'] ?? $summary['total'] ?? 1154);
    $visitedSchools = (int) ($summary['visitedSchools'] ?? $summary['visited_schools'] ?? $summary['sudah_monev'] ?? 98);
    $inProgressSchools = (int) ($summary['inProgressSchools'] ?? $summary['in_progress_schools'] ?? $summary['sedang_berlangsung'] ?? 65);
    $readinessPercent = (float) ($summary['readinessPercent'] ?? $summary['readiness_percent'] ?? 99);
    $statusRows = $monevStatus['data'] ?? $monevStatus;
    if (!is_array($statusRows)) {
        $statusRows = [];
    }
    $officerRows = $officerRecap['data'] ?? $officerRecap;
    if (!is_array($officerRows)) {
        $officerRows = [];
    }
    $sudahMonev = (int) $visitedSchools;
    $sedangBerlangsung = (int) $inProgressSchools;
    $draftMonev = 0;
    foreach ($statusRows as $statusRow) {
        if (!is_array($statusRow)) {
            continue;
        }
        $draftMonev += (int) ($statusRow['draft_monev'] ?? 0);
    }
    $totalStatus = $sudahMonev + $sedangBerlangsung + $draftMonev;
    $maxStatus = max($sudahMonev, $sedangBerlangsung, $draftMonev, 1);

    $reportRegionName = trim((string)($reportRegionName ?? ''));

    if ($reportRegionName === '') {
        $reportRegionName = 'SELURUH WILAYAH DKI JAKARTA';
    }
    ?>

<div class="header">
    <div class="header-title">
        PEMERINTAH PROVINSI DKI JAKARTA
    </div>
    <div class="header-title">
        DINAS PENDIDIKAN
    </div>
    <div class="header-subtitle">
        TAHUN 2026
    </div>
    <div class="header-subtitle">
        <?= esc($reportRegionName) ?>
    </div>
</div>
<div class="report-title">
    LAPORAN MONITORING &amp; EVALUASI TKAP
</div>
<div class="report-subtitle">
    Analisis Kesiapan Infrastruktur &amp; Progres Monev TKAP Sekolah
</div>
<table class="summary-table">
    <tr>
        <td class="summary-card">
            <div class="summary-label">
                TOTAL SEKOLAH
            </div>
            <div class="summary-value">
                <?= number_format($totalSchools, 0, ',', '.') ?>
            </div>
            <div class="summary-note">
                Seluruh Sekolah
            </div>
        </td>
        <td class="summary-card">
            <div class="summary-label">
                DRAFT MONEV
            </div>
            <div class="summary-value">
                <?= number_format($draftMonev, 0, ',', '.') ?>
            </div>
            <div class="summary-note">
                Belum Dimulai
            </div>
        </td>
        <td class="summary-card">
            <div class="summary-label">
                DALAM PROSES
            </div>
            <div class="summary-value">
                <?= number_format($sedangBerlangsung, 0, ',', '.') ?>
            </div>
            <div class="summary-note">
                Sedang Berlangsung
            </div>
        </td>
        <td class="summary-card">
            <div class="summary-label">
                SUDAH MONEV
            </div>
            <div class="summary-value">
                <?= number_format($sudahMonev, 0, ',', '.') ?>
            </div>
            <div class="summary-note">
                Monev Selesai
            </div>
        </td>
        <td class="summary-card">
            <div class="summary-label">
                KESIAPAN BAIK
            </div>
            <div class="summary-value">
                <?= number_format($readinessPercent, 1, ',', '.') ?>%
            </div>
            <div class="summary-note">
                Tingkat Pemenuhan
            </div>
        </td>
    </tr>
</table>
<div class="section">
    <div class="section-title">
        1. RINGKASAN STATUS MONEV &amp; DISTRIBUSI JENJANG
    </div>
    <div class="section-description">
        Ringkasan progres kunjungan Monev dan cakupan sekolah berdasarkan jenjang.
    </div>
    <table class="chart-table">
        <tr>
            <td class="chart-box">
                <div class="chart-title">
                    Status Progres Kunjungan
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        Sudah Monev
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= ($sudahMonev / $maxStatus) * 100 ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $sudahMonev ?>
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        Sedang Berlangsung
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= ($sedangBerlangsung / $maxStatus) * 100 ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $sedangBerlangsung ?>
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        Draft
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= ($draftMonev / $maxStatus) * 100 ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $draftMonev ?>
                    </span>
                </div>
            </td>
           <td class="chart-box">
                <div class="chart-title">
                    Cakupan Sekolah per Jenjang
                </div>
                <?php
                $levelData = is_array($visitsByLevel ?? null) ? $visitsByLevel : [];
                $sma = (int) ($levelData['SMA'] ?? 0);
                $smk = (int) ($levelData['SMK'] ?? 0);
                $ma = (int) ($levelData['MA'] ?? 0);
                $maxLevel = max($sma, $smk, $ma, 1);
                ?>
                <div class="bar-row">
                    <span class="bar-label">
                        SMA
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= number_format(($sma / $maxLevel) * 100, 2, '.', '') ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $sma ?>
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        SMK
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= number_format(($smk / $maxLevel) * 100, 2, '.', '') ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $smk ?>
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        MA
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:<?= number_format(($ma / $maxLevel) * 100, 2, '.', '') ?>%;"></span>
                    </span>
                    <span class="bar-value">
                        <?= $ma ?>
                    </span>
                </div>
            </td>
        </tr>
    </table>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Status Monev</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">
                    1
                </td>
                <td>
                    Sudah Monev
                </td>
                <td class="center">
                    <?= $sudahMonev ?>
                </td>
                <td class="center blue">
                    <?= number_format($totalStatus > 0 ? ($sudahMonev / $totalStatus) * 100 : 0, 1, ',', '.') ?>%
                </td>
            </tr>
            <tr>
                <td class="center">
                    2
                </td>
                <td>
                    Sedang Berlangsung
                </td>
                <td class="center">
                    <?= $sedangBerlangsung ?>
                </td>
                <td class="center blue">
                    <?= number_format($totalStatus > 0 ? ($sedangBerlangsung / $totalStatus) * 100 : 0, 1, ',', '.') ?>%
                </td>
            </tr>
            <tr>
                <td class="center">
                    3
                </td>
                <td>
                    Draft
                </td>
                <td class="center">
                    <?= $draftMonev ?>
                </td>
                <td class="center blue">
                    <?= number_format($totalStatus > 0 ? ($draftMonev / $totalStatus) * 100 : 0, 1, ',', '.') ?>%
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="section">
    <div class="section-title">
        2. REKAP STATUS MONEV PER WILAYAH
    </div>
    <div class="section-description">
        Rekap status pelaksanaan Monev berdasarkan seluruh wilayah.
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Wilayah</th>
                <th>Sudah Monev</th>
                <th>Sedang Berlangsung</th>
                <th>Draft Monev</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($statusRows)): ?>
                <?php $no = 1; ?>
                <?php foreach ($statusRows as $row): ?>
                    <?php if (!is_array($row)) continue; ?>
                    <?php
                    $wilayah = $row['wilayah'] ?? $row['region_name'] ?? $row['nama_wilayah'] ?? '-';
                    $sudah = (int) ($row['sudah_monev'] ?? $row['selesai'] ?? 0);
                    $sedang = (int) ($row['sedang_berlangsung'] ?? $row['berlangsung'] ?? 0);
                    $draft = (int) ($row['draft_monev'] ?? $row['draft'] ?? 0);
                    $total = (int) ($row['jumlah_sasaran'] ?? ($sudah + $sedang + $draft));
                    $persentase = $row['persentase'] ?? ($total > 0 ? ($sudah / $total) * 100 : 0);
                    ?>
                    <tr>
                        <td class="center">
                            <?= $no++ ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($wilayah) ?>
                        </td>
                        <td class="center">
                            <?= $sudah ?>
                        </td>
                        <td class="center">
                            <?= $sedang ?>
                        </td>
                        <td class="center">
                            <?= $draft ?>
                        </td>
                        <td class="center blue">
                            <?= number_format((float) $persentase, 1, ',', '.') ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="center">
                        Data wilayah belum tersedia.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="section">
    <div class="section-title">
        3. REKAP LAPORAN PELAKSANA MONEV
    </div>
    <div class="section-description">
        Rekap sasaran dan progres Monev berdasarkan pelaksana dan wilayah yang dikerjakan.
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pelaksana</th>
                <th>Wilayah yang Dikerjakan</th>
                <th>Jumlah Sasaran</th>
                <th>Sudah Monev</th>
                <th>Sedang Berlangsung</th>
                <th>Belum Monev</th>
                <th>Progres</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($officerRows)): ?>
                <?php $no = 1; ?>
                <?php foreach ($officerRows as $row): ?>
                    <?php if (!is_array($row)) continue; ?>
                    <?php
                    $nama = $row['nama_pelaksana'] ?? $row['officer_name'] ?? $row['nama'] ?? '-';
                    $wilayah = $row['wilayah'] ?? $row['region_name'] ?? $row['wilayah_kerja'] ?? '-';
                    $sasaran = (int) ($row['jumlah_sasaran'] ?? $row['total_sasaran'] ?? 0);
                    $sudah = (int) ($row['sudah_monev'] ?? 0);
                    $sedang = (int) ($row['sedang_berlangsung'] ?? 0);
                    $belum = (int) ($row['belum_monev'] ?? 0);
                    $progres = (float) ($row['progres'] ?? $row['persentase'] ?? ($sasaran > 0 ? ($sudah / $sasaran) * 100 : 0));
                    $keterangan = $row['keterangan'] ?? '-';
                    ?>
                    <tr>
                        <td class="center">
                            <?= $no++ ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($nama) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($wilayah) ?>
                        </td>
                        <td class="center">
                            <?= $sasaran ?>
                        </td>
                        <td class="center">
                            <?= $sudah ?>
                        </td>
                        <td class="center">
                            <?= $sedang ?>
                        </td>
                        <td class="center">
                            <?= $belum ?>
                        </td>
                        <td class="center blue">
                            <?= number_format($progres, 1, ',', '.') ?>%
                        </td>
                        <td>
                            <?= htmlspecialchars($keterangan) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="center">
                        Data pelaksana belum tersedia.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="section">
    <div class="section-title">4. REKAP PERMASALAHAN YANG DITEMUKAN &amp; REKOMENDASI TINDAK LANJUT</div>
    <div class="section-description">Rekap permasalahan yang ditemukan berdasarkan hasil Monitoring dan Evaluasi Kesiapan Infrastruktur TKAP serta rekomendasi tindak lanjut.</div>
    <table class="table">
        <thead>
            <tr>
                <th width="6%">No</th>
                <th width="30%">Permasalahan</th>
                <th width="9%">Jumlah</th>
                <th width="55%">Rekomendasi Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($problemRecommendations ?? []) as $problem): ?>
                <tr>
                    <td class="center"><?= htmlspecialchars($problem['no'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($problem['problem'] ?? '-') ?></td>
                    <td class="center"><strong><?= htmlspecialchars($problem['total_school'] ?? 0) ?></strong></td>
                    <td><?= htmlspecialchars($problem['recommendation'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php foreach (($problemRecommendations ?? []) as $problem): ?>
    <?php $type = $problem['type'] ?? ''; ?>

    <div class="detail-box">
        <div class="detail-title">
            Detail Masalah <?= htmlspecialchars($problem['no'] ?? '-') ?> —
            <?= htmlspecialchars($problem['problem'] ?? '-') ?>
        </div>

       <?php if ($type === 'device'): ?>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Sekolah</th>
                    <th>Siswa Ikut TKA</th>
                    <th>Gelombang</th>
                    <th>Sesi</th>
                    <th>Kebutuhan Utama/Sesi</th>
                    <th>Cadangan 10%</th>
                    <th>Total Kebutuhan/Sesi</th>
                    <th>Perangkat Tersedia</th>
                    <th>Kekurangan</th>
                    <th>Status</th>
                </tr>
            </thead>
           <tbody>
                <?php if (empty($problem['details'])): ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:12px;">
                            Tidak ada data.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <?php
                        $peserta = (int)($row['peserta'] ?? 0);
                        $gelombang = (int)($row['gelombang'] ?? 0);
                        $sesi = (int)($row['sesi'] ?? 0);
                        $kebutuhanUtama = (int)($row['kebutuhan_utama'] ?? 0);
                        $cadangan = (int)($row['kebutuhan_cadangan'] ?? 0);
                        $totalKebutuhan = (int)($row['total_kebutuhan'] ?? ($kebutuhanUtama + $cadangan));
                        $tersedia = (int)($row['available'] ?? 0);
                        $kekuranganUtama = max($kebutuhanUtama - $tersedia, 0);
                        $kekuranganCadangan = $kekuranganUtama > 0 ? $cadangan : max($totalKebutuhan - $tersedia, 0);
                        $kurang = $tersedia < $kebutuhanUtama;
                        $status = $kurang ? 'PERANGKAT UTAMA KURANG' : 'PERANGKAT UTAMA CUKUP';
                        ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
                            <td class="center"><strong><?= $peserta ?> siswa</strong></td>
                            <td class="center"><?= $gelombang ?></td>
                            <td class="center"><?= $sesi ?></td>
                            <td class="center"><strong><?= $kebutuhanUtama ?> unit</strong></td>
                            <td class="center"><?= $cadangan ?> unit</td>
                            <td class="center"><strong><?= $totalKebutuhan ?> unit</strong></td>
                            <td class="center"><?= $tersedia ?> unit</td>
                            <td class="center <?= $kurang || $kekuranganCadangan > 0 ? 'text-danger' : '' ?>">
                                <?php if ($kurang): ?>
                                    <strong><?= $kekuranganUtama ?> unit utama + <?= $kekuranganCadangan ?> unit cadangan</strong>
                                <?php elseif ($kekuranganCadangan > 0): ?>
                                    <strong><?= $kekuranganCadangan ?> unit cadangan</strong>
                                <?php else: ?>
                                    <strong>0 unit</strong>
                                <?php endif; ?>
                            </td>
                            <td class="center <?= $kurang ? 'status-danger' : 'status-success' ?>">
                                <strong><?= $status ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php elseif ($type === 'main_isp'): ?>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sekolah</th>
                        <th>ISP Utama</th>
                        <th>Bandwidth Tersedia</th>
                        <th>Kebutuhan Bandwidth</th>
                        <th>Kekurangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['isp'] ?? '-') ?></td>
                            <td class="center"><?= htmlspecialchars($row['bandwidth'] ?? '-') ?> Mbps</td>
                            <td class="center"><?= htmlspecialchars($row['need'] ?? '-') ?> Mbps</td>
                            <td class="center"><strong><?= htmlspecialchars($row['shortage'] ?? '-') ?> Mbps</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($type === 'backup_missing'): ?>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th width="8%">No</th>
                        <th>Sekolah</th>
                        <th>NPSN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
                             <td style="text-align:center;"><?= htmlspecialchars($row['npsn'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($type === 'backup_bandwidth'): ?>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sekolah</th>
                        <th>Bandwidth Cadangan</th>
                        <th>Kebutuhan</th>
                        <th>Kekurangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
                            <td class="center"><?= htmlspecialchars($row['bandwidth'] ?? '-') ?> Mbps</td>
                            <td class="center"><?= htmlspecialchars($row['need'] ?? '-') ?> Mbps</td>
                            <td class="center"><strong><?= htmlspecialchars($row['shortage'] ?? '-') ?> Mbps</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
<?php endforeach; ?>
<div class="section page-break-before">
    <div class="section-title">4. INFRASTRUKTUR DAN SARANA</div>
    <div class="section-description">
        Perbandingan jumlah perangkat dan fasilitas sekolah berdasarkan hasil Monitoring dan Evaluasi.
    </div>    <?php
    $infrastructure = is_array($infrastructure ?? null) ? $infrastructure : [];
    $categories = [
        'INF-01' => 'Komputer / PC Milik',
        'INF-02' => 'Laptop Milik',
        'INF-03' => 'Laptop Bukan Milik',
        'INF-04' => 'Labkom',
        'INF-05' => 'Ruang yang Dipakai TKAP',
        'INF-06' => 'Switch Hub',
        'INF-07' => 'UPS',
        'INF-08' => 'Access Point'
    ];
    foreach ($categories as $code => $categoryTitle):
        $items = $infrastructure[$code] ?? [];
        if (isset($items['data']) && is_array($items['data'])) {
            $items = $items['data'];
        }
        $rows = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $school = $item['school_name']
                    ?? $item['school']
                    ?? $item['name']
                    ?? $item['nama_sekolah']
                    ?? '-';
                $npsn = $item['npsn']
                    ?? $item['NPSN']
                    ?? $item['school_npsn']
                    ?? '-';
                $jumlah = $item['value']
                    ?? $item['jumlah']
                    ?? $item['total']
                    ?? $item['available']
                    ?? $item['jumlah_perangkat']
                    ?? $item['count']
                    ?? 0;
                $rows[] = [
                    'school' => (string)$school,
                    'npsn' => (string)$npsn,
                    'jumlah' => (float)$jumlah
                ];
            }
        }
        usort($rows, function ($a, $b) {
            return $b['jumlah'] <=> $a['jumlah'];
        });
        if (empty($rows)) {
            continue;
        }
        $chartRows = array_slice($rows, 0, 10);
        $values = array_column($rows, 'jumlah');
        $totalValue = array_sum($values);
        $minValue = min($values);
        $maxValue = max($values);
        $averageValue = count($rows) > 0 ? $totalValue / count($rows) : 0;
        $maxChartValue = max(array_column($chartRows, 'jumlah'));
        if ($maxChartValue <= 0) {
            $maxChartValue = 1;
        }
        $formatNumber = function ($value) {
            return rtrim(rtrim(number_format((float)$value, 1, '.', ''), '0'), '.');
        };
    ?>
        <div class="infra-category <?= $code !== 'INF-01' ? 'page-break-before' : '' ?>">
            <div class="infra-category-header">
                <div class="infra-category-title"><?= esc($categoryTitle) ?></div>
                <div class="infra-category-description">10 sekolah dengan jumlah   <?= esc($categoryTitle) ?>  terbanyak</div>
            </div>
            <div class="infra-chart">
                <?php foreach ($chartRows as $chartIndex => $row): ?>
                    <?php
                    $percent = ($row['jumlah'] / $maxChartValue) * 100;
                    if ($percent < 2 && $row['jumlah'] > 0) {
                        $percent = 2;
                    }
                    $schoolName = $row['school'];
                    ?>
                    <div class="infra-chart-item">
                        <div class="infra-chart-school">
                            <?= ($chartIndex + 1) ?>. <?= esc($schoolName) ?>
                        </div>
                        <div class="infra-chart-track">
                            <div class="infra-chart-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
                        </div>
                        <div class="infra-chart-number">
                          <?= $formatNumber($row['jumlah']) ?>
                          <?= in_array($code, ['INF-04', 'INF-05']) ? 'ruangan' : 'unit' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
           <div class="infra-detail-page">
                <div class="detail-info">
                    Detail <?= esc($categoryTitle) ?>
                    <span class="detail-count">
                        <?= count($rows) ?> sekolah
                    </span>
                </div>

                <table class="detail-table">
                    <thead>
                        <tr>
                            <th width="7%">No</th>
                            <th>Sekolah</th>
                            <th width="18%">NPSN</th>
                            <th width="18%">Jumlah</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $index => $row): ?>
                            <tr>
                                <td class="center">
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <?= esc($row['school']) ?>
                                </td>

                                <td class="center">
                                    <?= esc($row['npsn']) ?>
                                </td>

                                <td class="center">
                                    <strong>
                                       <?= $formatNumber($row['jumlah']) ?> <?= in_array($code, ['INF-04', 'INF-05']) ? 'ruangan' : 'unit' ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="section page-break-before">
    <div class="section-title">5. DAYA LISTRIK</div>
    <div class="section-description">
        Distribusi daya listrik yang digunakan sekolah berdasarkan hasil Monitoring dan Evaluasi.
    </div>

    <?php
    $electricityData = is_array($electricity ?? null) ? $electricity : [];
    $electricityRows = is_array($electricityData['data'] ?? null) ? $electricityData['data'] : [];
    $rows = [];

    foreach ($electricityRows as $item) {
        if (!is_array($item)) {
            continue;
        }

        $school = $item['school_name'] ?? $item['school'] ?? $item['name'] ?? '-';
        $npsn = $item['npsn'] ?? $item['NPSN'] ?? '-';
        $value = $item['value'] ?? $item['daya'] ?? $item['power'] ?? $item['electricity'] ?? $item['watt'] ?? $item['kapasitas'] ?? '';

        $power = (float) preg_replace('/[^0-9]/', '', (string) $value);

        if ($power <= 0) {
            continue;
        }

        $rows[] = [
            'school' => (string) $school,
            'npsn' => (string) $npsn,
            'power' => $power
        ];
    }

    usort($rows, function ($a, $b) {
        return $b['power'] <=> $a['power'];
    });

    $powerRecap = [];

    foreach ($rows as $row) {
        $key = (string) $row['power'];

        if (!isset($powerRecap[$key])) {
            $powerRecap[$key] = [
                'power' => $row['power'],
                'count' => 0
            ];
        }

        $powerRecap[$key]['count']++;
    }

    $powerRecap = array_values($powerRecap);

    usort($powerRecap, function ($a, $b) {
        return $b['power'] <=> $a['power'];
    });

    $maxCount = 1;

    foreach ($powerRecap as $item) {
        if ($item['count'] > $maxCount) {
            $maxCount = $item['count'];
        }
    }

    $formatPower = function ($value) {
        return number_format((float) $value, 0, ',', '.') . ' Watt';
    };
    ?>

    <?php if (!empty($rows)): ?>

<div class="electricity-chart-title heading-title"">
    10 Kapasitas Daya Tertinggi
</div>

<div class="electricity-chart">
    <?php
    $highestPower = array_slice($powerRecap, 0, 10);
    $maxHighestCount = 1;

    foreach ($highestPower as $item) {
        $maxHighestCount = max($maxHighestCount, (int)$item['count']);
    }
    ?>

    <?php foreach ($highestPower as $rank => $item): ?>
        <?php
        $percent = $maxHighestCount > 0
            ? ((int)$item['count'] / $maxHighestCount) * 100
            : 0;
        ?>

        <div class="electricity-chart-row">

            <div class="electricity-rank-label">
                <span class="electricity-rank">
                    <?= $rank + 1 ?>
                </span>
                <span class="electricity-label">
                    <?= esc($formatPower($item['power'])) ?>
                </span>
            </div>

            <div class="electricity-track">
                <div
                    class="electricity-bar"
                    style="width:<?= number_format($percent, 2, '.', '') ?>%;"
                ></div>
            </div>

            <div class="electricity-count">
                <?= (int)$item['count'] ?> sekolah
            </div>

        </div>
    <?php endforeach; ?>
</div>

<div class="electricity-chart-title heading-title"">
    10 Kapasitas Daya Terendah
</div>

<div class="electricity-chart">
    <?php
    $lowestPower = array_slice(array_reverse($powerRecap), 0, 10);
    $maxLowestCount = 1;

    foreach ($lowestPower as $item) {
        $maxLowestCount = max($maxLowestCount, (int)$item['count']);
    }
    ?>

    <?php foreach ($lowestPower as $rank => $item): ?>
        <?php
        $percent = $maxLowestCount > 0
            ? ((int)$item['count'] / $maxLowestCount) * 100
            : 0;
        ?>

        <div class="electricity-chart-row">

            <div class="electricity-rank-label">
                <span class="electricity-rank">
                    <?= $rank + 1 ?>
                </span>
                <span class="electricity-label">
                    <?= esc($formatPower($item['power'])) ?>
                </span>
            </div>

            <div class="electricity-track">
                <div
                    class="electricity-bar"
                    style="width:<?= number_format($percent, 2, '.', '') ?>%;"
                ></div>
            </div>

            <div class="electricity-count">
                <?= (int)$item['count'] ?> sekolah
            </div>

        </div>
    <?php endforeach; ?>
</div>

<div class="detail-info">
    Detail Daya Listrik — <?= count($rows) ?> sekolah
</div>

<table class="detail-table">
    <thead>
        <tr>
            <th width="7%">No</th>
            <th>Sekolah</th>
            <th width="20%">NPSN</th>
            <th width="22%">Daya Listrik</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $index => $row): ?>
            <tr>
                <td class="center">
                    <?= $index + 1 ?>
                </td>
                <td>
                    <?= esc($row['school']) ?>
                </td>
                <td class="center">
                    <?= esc($row['npsn']) ?>
                </td>
                <td class="center">
                    <strong>
                        <?= esc($formatPower($row['power'])) ?>
                    </strong>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else: ?>

<div class="empty-data">
    Tidak ada data daya listrik.
</div>

<?php endif; ?>
</div>
<div class="section page-break-before">
    <div class="section-title">6. JARINGAN INTERNET</div>
    <div class="section-description">
        Distribusi jenis jaringan internet yang digunakan sekolah berdasarkan hasil Monitoring dan Evaluasi.
    </div>
    <?php
        $internetData = is_array($internet ?? null) ? $internet : [];
        $internetRows = is_array($internetData['data'] ?? null) ? $internetData['data'] : [];
        $internetRecap = [];
        foreach ($internetRows as $row) {
            $type = trim((string) ($row['value'] ?? $row['network'] ?? $row['jaringan'] ?? ''));
            if ($type === '') {
                continue;
            }
            $key = strtoupper($type);
            if (!isset($internetRecap[$key])) {
                $internetRecap[$key] = 0;
            }
            $internetRecap[$key]++;
        }
        arsort($internetRecap);
        $maxInternet = max($internetRecap ?: [1]);
        usort($internetRows, function ($a, $b) {
            $networkA = strtoupper(trim((string) ($a['value'] ?? $a['network'] ?? $a['jaringan'] ?? '')));
            $networkB = strtoupper(trim((string) ($b['value'] ?? $b['network'] ?? $b['jaringan'] ?? '')));
            $order = [
                'LAN' => 1,
                'WIFI' => 2,
                'WI-FI' => 2
            ];
            $orderA = $order[$networkA] ?? 99;
            $orderB = $order[$networkB] ?? 99;
            if ($orderA === $orderB) {
                $schoolA = (string) ($a['school_name'] ?? $a['school'] ?? $a['nama_sekolah'] ?? '');
                $schoolB = (string) ($b['school_name'] ?? $b['school'] ?? $b['nama_sekolah'] ?? '');
                return strcasecmp($schoolA, $schoolB);
            }
            return $orderA <=> $orderB;
        });
        ?>

    <?php if (!empty($internetRecap)): ?>
        <div class="electricity-chart">
            <?php foreach ($internetRecap as $type => $count): ?>
                <?php $percent = ($count / $maxInternet) * 100; ?>

                <div class="electricity-chart-row">
                    <div class="electricity-label">
                        <?= esc($type) ?>
                    </div>
                    <div class="electricity-track">
                        <div class="electricity-bar" style="width:<?= $percent ?>%;"></div>
                    </div>
                    <div class="electricity-count">
                        <?= $count ?> sekolah
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="detail-info">
            Detail Jaringan Internet — <?= count($internetRows) ?> sekolah
        </div>

        <table class="detail-table">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>Sekolah</th>
                    <th width="20%">NPSN</th>
                    <th width="20%">Jaringan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($internetRows as $index => $row): ?>
                    <tr>
                        <td class="center"><?= $index + 1 ?></td>
                        <td><?= esc($row['school_name'] ?? $row['school'] ?? '-') ?></td>
                        <td class="center"><?= esc($row['npsn'] ?? '-') ?></td>
                        <td class="center">
                            <strong>
                                <?= esc($row['value'] ?? $row['network'] ?? $row['jaringan'] ?? '-') ?>
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <div class="empty-data">Tidak ada data jaringan internet.</div>

    <?php endif; ?>
</div>

<div class="section page-break-before">
    <div class="section-title">7. BANDWIDTH ISP UTAMA</div>
    <div class="section-description">
        Bandwidth ISP utama yang digunakan sekolah berdasarkan hasil Monitoring dan Evaluasi.
    </div>
    <?php
    $bandwidthData = is_array($mainBandwidth ?? null) ? $mainBandwidth : [];
    $rows = is_array($bandwidthData['data'] ?? null) ? $bandwidthData['data'] : [];
    $bandwidthRows = [];
    foreach ($rows as $row) {
        $value = trim((string)($row['value'] ?? ''));
        if ($value === '') {
            continue;
        }
        $numericValue = isset($row['numeric_value'])
            ? (float)$row['numeric_value']
            : (float)preg_replace('/[^0-9.]/', '', $value);
        if ($numericValue <= 0) {
            continue;
        }
        $bandwidthRows[] = [
            'school_name' => $row['school_name'] ?? '-',
            'npsn' => $row['npsn'] ?? '-',
            'value' => $value,
            'numeric_value' => $numericValue
        ];
    }
    $highestBandwidth = $bandwidthRows;
    usort($highestBandwidth, function ($a, $b) {
        return $b['numeric_value'] <=> $a['numeric_value'];
    });
    $highestDistribution = [];
    foreach ($highestBandwidth as $row) {
        $key = (string)$row['numeric_value'];
        if (!isset($highestDistribution[$key])) {
            $highestDistribution[$key] = [
                'value' => $row['value'],
                'count' => 0
            ];
        }
        $highestDistribution[$key]['count']++;
    }
    uasort($highestDistribution, function ($a, $b) {
        return (float)preg_replace('/[^0-9.]/', '', $b['value'])
            <=>
            (float)preg_replace('/[^0-9.]/', '', $a['value']);
    });
    $highestDistribution = array_slice($highestDistribution, 0, 10, true);
    $maxHighestCount = 1;
    foreach ($highestDistribution as $item) {
        $maxHighestCount = max($maxHighestCount, (int)$item['count']);
    }
    $lowestBandwidth = $bandwidthRows;
    usort($lowestBandwidth, function ($a, $b) {
        return $a['numeric_value'] <=> $b['numeric_value'];
    });
    $lowestDistribution = [];
    foreach ($lowestBandwidth as $row) {
        $key = (string)$row['numeric_value'];
        if (!isset($lowestDistribution[$key])) {
            $lowestDistribution[$key] = [
                'value' => $row['value'],
                'count' => 0
            ];
        }
        $lowestDistribution[$key]['count']++;
    }
    uasort($lowestDistribution, function ($a, $b) {
        return (float)preg_replace('/[^0-9.]/', '', $a['value'])
            <=>
            (float)preg_replace('/[^0-9.]/', '', $b['value']);
    });
    $lowestDistribution = array_slice($lowestDistribution, 0, 10, true);
    $maxLowestCount = 1;
    foreach ($lowestDistribution as $item) {
        $maxLowestCount = max($maxLowestCount, (int)$item['count']);
    }
    $detailRows = $bandwidthRows;
    usort($detailRows, function ($a, $b) {
        return $b['numeric_value'] <=> $a['numeric_value'];
    });
    ?>
    <?php if (!empty($bandwidthRows)): ?>

        <div class="electricity-chart-title heading-title"">
           10 Bandwidth ISP Utama Tertinggi
        </div>
        <div class="electricity-chart">
          <?php $rank = 1; ?>
          <?php foreach ($highestDistribution as $item): ?>
                <?php
                $percent = $maxHighestCount > 0
                    ? ((int)$item['count'] / $maxHighestCount) * 100
                    : 0;
                ?>
                <div class="electricity-chart-row">
                    <div class="electricity-rank">
                        <?= $rank++ ?>
                    </div>
                    <div class="electricity-label">
                        <?= esc($item['value']) ?>
                    </div>
                    <div class="electricity-track">
                        <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
                    </div>
                    <div class="electricity-count">
                        <?= (int)$item['count'] ?> sekolah
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="electricity-chart-title heading-title">
           10 Bandwidth ISP Utama Terendah
        </div>
        <div class="electricity-chart">
            <?php $rank = 1; ?>
            <?php foreach ($lowestDistribution as $item): ?>
                <?php
                $percent = $maxLowestCount > 0
                    ? ((int)$item['count'] / $maxLowestCount) * 100
                    : 0;
                ?>
                <div class="electricity-chart-row">
                    <div class="electricity-rank">
                        <?= $rank++ ?>
                    </div>
                    <div class="electricity-label">
                        <?= esc($item['value']) ?>
                    </div>
                    <div class="electricity-track">
                        <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
                    </div>
                    <div class="electricity-count">
                        <?= (int)$item['count'] ?> sekolah
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="detail-info">
            Detail Bandwidth ISP Utama — <?= count($detailRows) ?> sekolah
        </div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>Sekolah</th>
                    <th width="20%">NPSN</th>
                    <th width="20%">Bandwidth</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detailRows as $index => $row): ?>
                    <tr>
                        <td class="center">
                            <?= $index + 1 ?>
                        </td>
                        <td>
                            <?= esc($row['school_name']) ?>
                        </td>
                        <td class="center">
                            <?= esc($row['npsn']) ?>
                        </td>
                        <td class="center">
                            <strong>
                                <?= esc($row['value']) ?>
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-data">
            Tidak ada data bandwidth ISP utama.
        </div>
    <?php endif; ?>
</div>

    <?php
    $backupData = is_array($backupBandwidth ?? null) ? $backupBandwidth : [];
    $backupRows = is_array($backupData['data'] ?? null) ? $backupData['data'] : [];
    $backupRows = array_values(array_filter($backupRows, function ($row) {
        $value = trim((string)($row['value'] ?? ''));
        return $value !== '' && (float)preg_replace('/[^0-9.]/', '', $value) > 0;
    }));
    if (!empty($backupRows)):
    $backupRecap = [];
    foreach ($backupRows as $row) {
        $numericValue = (float)($row['numeric_value'] ?? preg_replace('/[^0-9.]/', '', (string)($row['value'] ?? '')));
        if ($numericValue <= 0) {
            continue;
        }
        $key = (string)$numericValue;
        if (!isset($backupRecap[$key])) {
            $backupRecap[$key] = [
                'value' => $row['value'],
                'numeric_value' => $numericValue,
                'count' => 0
            ];
        }
        $backupRecap[$key]['count']++;
    }
    $backupRecap = array_values($backupRecap);
    usort($backupRecap, function ($a, $b) {
        return $b['numeric_value'] <=> $a['numeric_value'];
    });
    $highestBackup = array_slice($backupRecap, 0, 10);
    $lowestBackup = array_slice(array_reverse($backupRecap), 0, 10);
    $maxHighestBackup = 1;
    foreach ($highestBackup as $item) {
        $maxHighestBackup = max($maxHighestBackup, (int)$item['count']);
    }
    $maxLowestBackup = 1;
    foreach ($lowestBackup as $item) {
        $maxLowestBackup = max($maxLowestBackup, (int)$item['count']);
    }
    usort($backupRows, function ($a, $b) {
        return (float)($b['numeric_value'] ?? preg_replace('/[^0-9.]/', '', (string)($b['value'] ?? ''))) <=> (float)($a['numeric_value'] ?? preg_replace('/[^0-9.]/', '', (string)($a['value'] ?? '')));
    });
    ?>
    <div class="section page-break-before">
        <div class="section-title">8. BANDWIDTH ISP CADANGAN</div>
        <div class="section-description">
            Bandwidth ISP cadangan yang digunakan sekolah berdasarkan hasil Monitoring dan Evaluasi.
        </div>
        <?php if (!empty($highestBackup)): ?>
        <div class="electricity-chart-title heading-title">
            10 Bandwidth ISP Cadangan Tertinggi
        </div>
        <div class="electricity-chart">
            <?php foreach ($highestBackup as $rank => $item): ?>
            <?php $percent = $maxHighestBackup > 0 ? ((int)$item['count'] / $maxHighestBackup) * 100 : 0; ?>
            <div class="electricity-chart-row">
                <div class="electricity-rank">
                    <?= $rank + 1 ?>
                </div>
                <div class="electricity-label">
                    <?= esc($item['value']) ?>
                </div>
                <div class="electricity-track">
                    <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
                </div>
                <div class="electricity-count">
                    <?= (int)$item['count'] ?> sekolah
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($lowestBackup)): ?>
        <div class="electricity-chart-title heading-title"">
            10 Bandwidth ISP Cadangan Terendah
        </div>
        <div class="electricity-chart">
            <?php foreach ($lowestBackup as $rank => $item): ?>
            <?php $percent = $maxLowestBackup > 0 ? ((int)$item['count'] / $maxLowestBackup) * 100 : 0; ?>
            <div class="electricity-chart-row">
                <div class="electricity-rank">
                    <?= $rank + 1 ?>
                </div>
                <div class="electricity-label">
                    <?= esc($item['value']) ?>
                </div>
                <div class="electricity-track">
                    <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
                </div>
                <div class="electricity-count">
                    <?= (int)$item['count'] ?> sekolah
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="detail-info">
            Detail Bandwidth ISP Cadangan — <?= count($backupRows) ?> sekolah
        </div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>Sekolah</th>
                    <th width="20%">NPSN</th>
                    <th width="20%">Bandwidth</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backupRows as $index => $row): ?>
                <tr>
                    <td class="center">
                        <?= $index + 1 ?>
                    </td>
                    <td>
                        <?= esc($row['school_name'] ?? '-') ?>
                    </td>
                    <td class="center">
                        <?= esc($row['npsn'] ?? '-') ?>
                    </td>
                    <td class="center">
                        <strong>
                            <?= esc($row['value'] ?? '-') ?>
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php
$studentData = is_array($studentReadiness ?? null) ? $studentReadiness : [];
$studentRows = is_array($studentData['data'] ?? null) ? $studentData['data'] : [];
usort($studentRows, function ($a, $b) {
    return (int)($b['total'] ?? 0) <=> (int)($a['total'] ?? 0);
});
$maxStudent = 1;
foreach ($studentRows as $row) {
    $maxStudent = max($maxStudent, (int)($row['total'] ?? 0));
}
?>
<?php
$studentRows=is_array($studentRows??null)?$studentRows:[];
usort($studentRows,function($a,$b){
    $aTotal=(int)($a['total']??0);
    $bTotal=(int)($b['total']??0);
    return $bTotal<=>$aTotal;
});
$highestStudents=array_slice($studentRows,0,10);
$lowestStudents=array_slice(array_reverse($studentRows),0,10);
$maxStudent=1;
foreach($highestStudents as $row){
    $maxStudent=max($maxStudent,(int)($row['total']??0));
}
?>
<div class="section page-break-before">
    <div class="section-title">9. KESIAPAN SISWA TKAP</div>
    <div class="section-description">
        Perbandingan siswa kelas 12 yang mengikuti dan tidak mengikuti TKAP.
    </div>
    <?php if(!empty($studentRows)): ?>
    <div class="electricity-chart-title heading-title">10 Sekolah dengan Peserrta TKAP Terbanyak</div>
    <div class="electricity-chart">
        <?php foreach($highestStudents as $rank=>$row): ?>
        <?php
        $total=(int)($row['total']??0);
        $barPercent=$maxStudent>0?($total/$maxStudent)*100:0;
        ?>
        <div class="electricity-chart-row">
            <div class="electricity-rank"><?= $rank+1 ?></div>
            <div class="electricity-label"><?= esc($row['school_name']??$row['school']??'-') ?></div>
            <div class="electricity-track">
                <div class="electricity-bar" style="width:<?= number_format($barPercent,2,'.','') ?>%;"></div>
            </div>
            <div class="electricity-count"><?= $total ?> siswa</div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="electricity-chart-title  heading-title">10 Sekolah dengan Peserta TKAP Terendah</div>
    <div class="electricity-chart">
        <?php foreach($lowestStudents as $rank=>$row): ?>
            <?php
            $total = (int)($row['total'] ?? 0);

            $barPercent = $maxStudent > 0
                ? min(85, ($total / $maxStudent) * 85)
                : 0;
            ?>

            <div class="electricity-chart-row">
                <div class="electricity-rank">
                    <?= $rank + 1 ?>
                </div>

                <div class="electricity-label">
                    <?= esc($row['school_name'] ?? $row['school'] ?? '-') ?>
                </div>

                <div class="electricity-track">
                    <div
                        class="electricity-bar"
                        style="width:<?= number_format($barPercent, 2, '.', '') ?>%;"
                    ></div>
                </div>

                <div class="electricity-count">
                    <?= $total ?> siswa
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="detail-info">
        Detail Keikutsertaan Siswa dalam TKAP — <?= count($studentRows) ?> sekolah
    </div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="7%">No</th>
                <th>Sekolah</th>
                <th>Total</th>
                <th>Ikut</th>
                <th>Tidak Ikut</th>
                <th>% Ikut</th>
            </tr>
        </thead>
       <?php
        $detailRows = [];

        foreach ($studentRows as $row) {
            $total = (int) ($row['total'] ?? 0);
            $ikut = (int) ($row['ikut'] ?? $row['participating'] ?? 0);
            $tidakIkut = max(0, $total - $ikut);
            $percent = $total > 0 ? ($ikut / $total) * 100 : 0;

            $detailRows[] = [
                'school_name' => $row['school_name'] ?? '-',
                'school' => $row['school'] ?? $row['school_name'] ?? '-',
                'percent' => $percent,
                'total' => $total,
                'ikut' => $ikut,
                'tidakIkut' => $tidakIkut
            ];
        }

        usort($detailRows, function ($a, $b) {
            $percentA = (float) ($a['percent'] ?? 0);
            $percentB = (float) ($b['percent'] ?? 0);

            if ($percentA == $percentB) {
                return strcasecmp(
                    (string) ($a['school_name'] ?? ''),
                    (string) ($b['school_name'] ?? '')
                );
            }

            return $percentB <=> $percentA;
        });
        ?>

        <tbody>
            <?php foreach ($detailRows as $index => $row): ?>
                <tr>
                    <td class="center">
                        <?= $index + 1 ?>
                    </td>
                    <td>
                        <?= esc($row['school_name'] ?? $row['school'] ?? '-') ?>
                    </td>
                    <td class="center">
                        <?= $row['total'] ?>
                    </td>
                    <td class="center">
                        <?= $row['ikut'] ?>
                    </td>
                    <td class="center">
                        <?= $row['tidakIkut'] ?>
                    </td>
                    <td class="center">
                        <strong>
                            <?= number_format($row['percent'], 1) ?>%
                        </strong>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-data">Tidak ada data kesiapan siswa TKAP.</div>
    <?php endif; ?>
</div>
<?php
    $sessionData = is_array($session ?? null) ? $session : [];
    $sessionRows = is_array($sessionData['data'] ?? null) ? $sessionData['data'] : [];
    $sessionRecap = [];
    foreach ($sessionRows as $row) {
        $value = trim((string) ($row['value'] ?? $row['session'] ?? $row['sesi'] ?? ''));
        if ($value === '') {
            continue;
        }
        if (!isset($sessionRecap[$value])) {
            $sessionRecap[$value] = 0;
        }
        $sessionRecap[$value]++;
    }
    uksort($sessionRecap, function ($a, $b) {
        return (float) preg_replace('/[^0-9.]/', '', $a) <=> (float) preg_replace('/[^0-9.]/', '', $b);
    });
    usort($sessionRows, function ($a, $b) {
        $sessionA = trim((string) ($a['value'] ?? $a['session'] ?? $a['sesi'] ?? ''));
        $sessionB = trim((string) ($b['value'] ?? $b['session'] ?? $b['sesi'] ?? ''));
        $numA = (int) preg_replace('/[^0-9]/', '', $sessionA);
        $numB = (int) preg_replace('/[^0-9]/', '', $sessionB);
        if ($numA === $numB) {
            $schoolA = (string) ($a['school_name'] ?? $a['school'] ?? $a['nama_sekolah'] ?? '');
            $schoolB = (string) ($b['school_name'] ?? $b['school'] ?? $b['nama_sekolah'] ?? '');
            return strcasecmp($schoolA, $schoolB);
        }
        return $numA <=> $numB;
    });
    $maxSession = max(array_values($sessionRecap) ?: [1]);
?>
<div class="section page-break-before">
    <div class="section-title">10. SESI TKAP</div>
    <?php if (!empty($sessionRecap)): ?>
    <div class="electricity-chart">
        <?php foreach ($sessionRecap as $label => $count): ?>
        <?php $percent = $maxSession > 0 ? ($count / $maxSession) * 100 : 0; ?>
        <div class="electricity-chart-row">
            <div class="electricity-label"><?= esc($label) ?></div>
            <div class="electricity-track">
                <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
            </div>
            <div class="electricity-count"><?= $count ?> sekolah</div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="detail-info">
        Detail Sesi TKAP — <?= count($sessionRows) ?> sekolah
    </div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="7%">No</th>
                <th>Sekolah</th>
                <th width="20%">NPSN</th>
                <th width="20%">Sesi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessionRows as $index => $row): ?>
            <tr>
                <td class="center"><?= $index + 1 ?></td>
                <td><?= esc($row['school_name'] ?? $row['school'] ?? '-') ?></td>
                <td class="center"><?= esc($row['npsn'] ?? '-') ?></td>
                <td class="center"><strong><?= esc($row['value'] ?? $row['session'] ?? $row['sesi'] ?? '-') ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-data">Tidak ada data sesi TKAP.</div>
    <?php endif; ?>
</div>
<?php
$waveData = is_array($wave ?? null) ? $wave : [];
$waveRows = is_array($waveData['data'] ?? null) ? $waveData['data'] : [];
$waveRecap = [];
foreach ($waveRows as $row) {
    $value = trim((string) ($row['value'] ?? $row['wave'] ?? $row['gelombang'] ?? ''));
    if ($value === '') {
        continue;
    }
    if (!isset($waveRecap[$value])) {
        $waveRecap[$value] = 0;
    }
    $waveRecap[$value]++;
}
uksort($waveRecap, function ($a, $b) {
    return (float) preg_replace('/[^0-9.]/', '', $a) <=> (float) preg_replace('/[^0-9.]/', '', $b);
});
$maxWave = max(array_values($waveRecap) ?: [1]);
usort($waveRows, function ($a, $b) {
    $waveA = trim((string) ($a['value'] ?? $a['wave'] ?? $a['gelombang'] ?? ''));
    $waveB = trim((string) ($b['value'] ?? $b['wave'] ?? $b['gelombang'] ?? ''));
    $numA = (int) preg_replace('/[^0-9]/', '', $waveA);
    $numB = (int) preg_replace('/[^0-9]/', '', $waveB);
    if ($numA === $numB) {
        $schoolA = (string) ($a['school_name'] ?? $a['school'] ?? $a['nama_sekolah'] ?? '');
        $schoolB = (string) ($b['school_name'] ?? $b['school'] ?? $b['nama_sekolah'] ?? '');
        return strcasecmp($schoolA, $schoolB);
    }
    return $numA <=> $numB;
});
?>
<div class="section page-break-before">
    <div class="section-title">11. GELOMBANG TKAP</div>
    <div class="section-description">
        Distribusi jumlah gelombang TKAP yang diikuti sekolah.
    </div>
    <?php if (!empty($waveRecap)): ?>
    <div class="electricity-chart">
        <?php foreach ($waveRecap as $label => $count): ?>
        <?php $percent = $maxWave > 0 ? ($count / $maxWave) * 100 : 0; ?>
        <div class="electricity-chart-row">
            <div class="electricity-label"><?= esc($label) ?></div>
            <div class="electricity-track">
                <div class="electricity-bar" style="width:<?= number_format($percent, 2, '.', '') ?>%;"></div>
            </div>
            <div class="electricity-count"><?= $count ?> sekolah</div>
        </div>
        <?php endforeach; ?>

        
    </div>
    <div class="detail-info">
        Detail Gelombang TKAP — <?= count($waveRows) ?> sekolah
    </div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="7%">No</th>
                <th>Sekolah</th>
                <th width="20%">NPSN</th>
                <th width="20%">Gelombang</th>
                
            </tr>
        </thead>
        <tbody>
           <?php foreach ($waveRows as $index => $row): ?>
                <tr>
                    <td class="center">
                        <?= $index + 1 ?>
                    </td>
                    <td>
                        <?= esc($row['school_name'] ?? $row['school'] ?? $row['nama_sekolah'] ?? '-') ?>
                    </td>
                    <td class="center">
                        <?= esc($row['npsn'] ?? '-') ?>
                    </td>
                    <td class="center">
                        <strong>
                            <?= esc($row['value'] ?? $row['wave'] ?? $row['gelombang'] ?? '-') ?>
                        </strong>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-data">Tidak ada data gelombang TKAP.</div>
    <?php endif; ?>
</div>

<div class="section page-break-before">
    <div class="section-title">
        12. KESIAPAN INFRASTRUKTUR TKAP
    </div>
    <div class="section-description">
        Distribusi penilaian kesiapan infrastruktur sekolah berdasarkan hasil
        Monitoring dan Evaluasi.
    </div>

    <?php
    $infrastructureReadiness=is_array($infrastructureReadiness??null)
        ?$infrastructureReadiness
        :[];

    $readinessSummary=[
        'Sangat Baik'=>0,
        'Baik'=>0,
        'Cukup'=>0,
        'Kurang Memadai'=>0
    ];

    if(
        isset($infrastructureReadiness['distribution'])&&
        is_array($infrastructureReadiness['distribution'])
    ){
        foreach($readinessSummary as $label=>$total){
            $readinessSummary[$label]=(int)(
                $infrastructureReadiness['distribution'][$label]??0
            );
        }
    }

    $readinessSchools=[];

    if(
        isset($infrastructureReadiness['data'])&&
        is_array($infrastructureReadiness['data'])
    ){
        $readinessSchools=$infrastructureReadiness['data'];
    }

  $detailRows=[];

    foreach($readinessSchools as $row){

        if(!is_array($row)){
            continue;
        }

        $readiness=trim(
            (string)($row['value']??'')
        );

        if(!isset($readinessSummary[$readiness])){
            continue;
        }

        $detailRows[]=[
            'school'=>(string)($row['school_name']??'-'),
            'npsn'=>(string)($row['npsn']??'-'),
            'readiness'=>$readiness,
            'komponen_kurang'=>(string)(
                $row['komponen_kurang']??
                'Memenuhi Kebutuhan'
            )
        ];
    }

    $readinessOrder=[
        'Sangat Baik'=>1,
        'Baik'=>2,
        'Cukup'=>3,
        'Kurang Memadai'=>4
    ];

    usort(
        $detailRows,
        function($a,$b)use($readinessOrder){

            $orderA=$readinessOrder[
                $a['readiness']
            ]??99;

            $orderB=$readinessOrder[
                $b['readiness']
            ]??99;

            if($orderA===$orderB){
                return strcasecmp(
                    $a['school'],
                    $b['school']
                );
            }

            return $orderA<=>$orderB;
        }
    );

    $maxReadiness=max(
        1,
        ...array_values($readinessSummary)
    );
    ?>

    <div class="readiness-card">
        <div class="readiness-card-title">
            Kesiapan Infrastruktur TKAP
        </div>

        <div class="readiness-card-description">
            Distribusi penilaian kesiapan infrastruktur sekolah.
        </div>

        <div class="electricity-chart">

            <?php foreach($readinessSummary as $label=>$count): ?>

                <?php
                $percent=$maxReadiness>0
                    ?($count/$maxReadiness)*100
                    :0;
                ?>

                <div class="electricity-chart-row">

                    <div class="electricity-label">
                        <?= esc($label) ?>
                    </div>

                    <div class="electricity-track">
                        <div
                            class="electricity-bar"
                            style="width:<?= number_format($percent,2,'.','') ?>%;"
                        ></div>
                    </div>

                    <div class="electricity-count">
                        <?= $count ?> sekolah
                    </div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <div class="readiness-detail">

        <div class="readiness-detail-title">
            Detail Sekolah
        </div>

        <div class="readiness-detail-description">
            Daftar sekolah berdasarkan hasil penilaian.
        </div>

        <table>

            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th>Sekolah</th>
                    <th width="15%">NPSN</th>
                    <th width="18%">Kesiapan</th>
                    <th width="31%">
                        Komponen yang Belum Memenuhi Kebutuhan
                    </th>
                </tr>
            </thead>

            <tbody>

                <?php if(empty($detailRows)): ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            Tidak ada data sekolah.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach($detailRows as $index=>$row): ?>

                        <tr>
                            <td class="text-center">
                                <?= $index+1 ?>
                            </td>

                            <td>
                                <?= esc($row['school']) ?>
                            </td>

                            <td style="text-align:center !important;">
                                <?= esc($row['npsn']) ?>
                            </td>

                            <td>
                            <strong
                                style="<?= $row['readiness']==='Kurang Memadai'
                                    ? 'color:#dc2626;'
                                    : '' ?>"
                            >
                                <?= esc($row['readiness']) ?>
                            </strong>
                        </td>

                        <td>
                            <span
                                style="<?= $row['readiness']==='Kurang Memadai'
                                    ? 'color:#dc2626;'
                                    : '' ?>"
                            >
                                <?= esc(
                                    $row['komponen_kurang']??
                                    'Memenuhi Kebutuhan'
                                ) ?>
                            </span>
                        </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<div class="footer">
    Laporan Monitoring &amp; Evaluasi TKAP — Dinas Pendidikan Provinsi DKI Jakarta — Tahun 2026
</div>
</body>
</html>