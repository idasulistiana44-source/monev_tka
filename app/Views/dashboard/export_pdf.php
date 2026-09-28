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
$sudahMonev = $visitedSchools;
$sedangBerlangsung = $inProgressSchools;
$draftMonev = 0;
$totalStatus = $sudahMonev + $sedangBerlangsung + $draftMonev;
$maxStatus = max($sudahMonev, $sedangBerlangsung, $draftMonev, 1);
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
                <div class="bar-row">
                    <span class="bar-label">
                        SMA
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:46.3%;"></span>
                    </span>
                    <span class="bar-value">
                        31
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        SMK
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:100%;"></span>
                    </span>
                    <span class="bar-value">
                        67
                    </span>
                </div>
                <div class="bar-row">
                    <span class="bar-label">
                        MA
                    </span>
                    <span class="bar-background">
                        <span class="bar" style="display:block;width:0%;"></span>
                    </span>
                    <span class="bar-value">
                        0
                    </span>
                </div>
            </td>
        </tr>
    </table>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Status</th>
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
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <?php
                        $total = (int)($row['total_kebutuhan'] ?? 0);
                        $available = (int)($row['available'] ?? 0);
                        $shortage = max($total - $available, 0);
                        $status = $shortage > 0 ? 'PERANGKAT UTAMA KURANG' : 'PERANGKAT UTAMA CUKUP';
                        ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
                            <td class="center"><strong><?= (int)($row['peserta'] ?? 0) ?> siswa</strong></td>
                            <td class="center"><?= htmlspecialchars($row['gelombang'] ?? '-') ?></td>
                            <td class="center"><?= htmlspecialchars($row['sesi'] ?? '-') ?></td>
                            <td class="center"><?= (int)($row['kebutuhan_utama'] ?? 0) ?> unit</td>
                            <td class="center"><?= (int)($row['kebutuhan_cadangan'] ?? 0) ?> unit</td>
                            <td class="center"><strong><?= $total ?> unit</strong></td>
                            <td class="center"><?= $available ?> unit</td>
                            <td class="center"><strong><?= $shortage ?> unit</strong></td>
                            <td class="center"><?= $status ?></td>
                        </tr>
                    <?php endforeach; ?>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($problem['details'] ?? []) as $index => $row): ?>
                        <tr>
                            <td class="center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
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
<div class="footer">
    Laporan Monitoring &amp; Evaluasi TKAP — Dinas Pendidikan Provinsi DKI Jakarta — Tahun 2026
</div>
</body>
</html>