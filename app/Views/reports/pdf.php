<?php
$visit = $data['visit'] ?? [];
$metrics = $data['metrics'] ?? [];
$template = $data['template'] ?? [];
$members = $data['members'] ?? [];
$pdfSection = $data['pdf_section'] ?? 'main';
$e = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatDate = function ($date) {
    if (!$date) {
        return '-';
    }
    $parts = explode('-', substr((string) $date, 0, 10));
    if (count($parts) !== 3) {
        return $date;
    }
    $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
    return ltrim($parts[2], '0') . ' ' . ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
};
$statusClass = function ($status) {
    $status = strtoupper((string) $status);
    if (str_contains($status, 'TIDAK')) {
        return 'bad';
    }
    if (str_contains($status, 'PERLU') || str_contains($status, 'VERIF') || str_contains($status, 'KURANG')) {
        return 'warning';
    }
    return 'good';
};
$statusDescription = function ($status, $type = '') {
    $status = strtoupper(trim((string) $status));
    if ($type === 'device') {
        if (str_contains($status, 'SANGAT')) {
            return 'Ketersediaan perangkat sangat baik dan telah mencukupi kebutuhan perangkat untuk pelaksanaan TKA pada setiap sesi.';
        }
        if (str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
            return 'Jumlah perangkat tersedia telah memenuhi kebutuhan peserta pada setiap sesi pelaksanaan TKA.';
        }
        if (str_contains($status, 'CUKUP') || str_contains($status, 'PERLU')) {
            return 'Perangkat tersedia, namun masih diperlukan penataan atau pengecekan distribusi perangkat pada setiap sesi.';
        }
        return 'Jumlah perangkat belum memenuhi kebutuhan sehingga diperlukan penambahan atau penyesuaian perangkat.';
    }
    if ($type === 'participant') {
        if (str_contains($status, 'SANGAT') || str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
            return 'Data peserta yang mengikuti TKA telah sesuai dengan data peserta yang tercatat dan menunjukkan kesiapan yang baik.';
        }
        if (str_contains($status, 'PERLU') || str_contains($status, 'VERIF')) {
            return 'Data peserta masih memerlukan verifikasi agar jumlah peserta dan pembagian peserta pada setiap sesi sesuai.';
        }
        return 'Data peserta belum menunjukkan kesiapan yang memadai dan perlu dilakukan verifikasi serta penyesuaian data.';
    }
    if ($type === 'network') {
        if (str_contains($status, 'SANGAT') || str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
            return 'Kapasitas jaringan internet telah memenuhi kebutuhan monitoring dan pelaksanaan TKA berdasarkan hasil pengukuran.';
        }
        if (str_contains($status, 'PERLU')) {
            return 'Jaringan tersedia namun kapasitas atau kestabilannya masih perlu diperhatikan dan dipastikan kembali sebelum pelaksanaan.';
        }
        return 'Kapasitas jaringan belum memenuhi kebutuhan sehingga diperlukan peningkatan kapasitas atau perbaikan jaringan.';
    }
    if ($type === 'electricity') {
        if (str_contains($status, 'SANGAT') || str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
            return 'Daya listrik dan perangkat pendukung tersedia serta dapat menunjang kebutuhan pelaksanaan TKA.';
        }
        if (str_contains($status, 'PERLU')) {
            return 'Daya listrik tersedia namun perangkat pendukung atau aspek kestabilan listrik masih perlu diperhatikan.';
        }
        return 'Kesiapan listrik dan perangkat pendukung belum memadai sehingga diperlukan tindak lanjut sebelum pelaksanaan.';
    }
    if (str_contains($status, 'SANGAT')) {
        return 'Kondisi persiapan sekolah sangat baik dan seluruh komponen utama yang dimonitor telah menunjukkan kesiapan.';
    }
    if (str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
        return 'Komponen persiapan utama telah tersedia dan memenuhi kebutuhan pelaksanaan TKA berdasarkan hasil monitoring.';
    }
    if (str_contains($status, 'PERLU') || str_contains($status, 'CUKUP')) {
        return 'Sekolah telah memiliki komponen persiapan utama, namun masih terdapat beberapa aspek yang perlu diperhatikan atau diverifikasi.';
    }
    return 'Masih terdapat komponen persiapan yang belum memenuhi kebutuhan sehingga diperlukan tindak lanjut sebelum pelaksanaan TKA.';
};
$scoreStatus = function ($status) {
    $status = strtoupper(trim((string) $status));
    if (str_contains($status, 'SANGAT')) {
        return 4;
    }
    if (str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
        return 3;
    }
    if (str_contains($status, 'CUKUP') || str_contains($status, 'PERLU')) {
        return 2;
    }
    return 1;
};
$scoreLabel = function ($score) {
    if ($score >= 4) {
        return 'SANGAT BAIK';
    }
    if ($score >= 3) {
        return 'BAIK';
    }
    if ($score >= 2) {
        return 'CUKUP';
    }
    return 'KURANG MEMADAI';
};
$cleanHtml = function ($html) {
    $html = (string) $html;
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><table><thead><tbody><tfoot><tr><td><th><div><span>');
};
$dynamic = function ($itemTitle) use ($metrics, $members, $data, $e, $formatDate, $statusClass, $statusDescription, $scoreStatus, $scoreLabel) {
    $title = strtoupper(trim((string) $itemTitle));
    if (str_contains($title, 'WAKTU DAN TEMPAT')) {
        return '
        <table class="data-table">
            <tbody>
                <tr>
                    <th>Nama Sekolah</th>
                    <td>' . $e($data['school_name'] ?? '-') . '</td>
                </tr>
                <tr>
                    <th>NPSN</th>
                    <td>' . $e($data['npsn'] ?? '-') . '</td>
                </tr>
                <tr>
                    <th>Jenjang</th>
                    <td>' . $e($data['level'] ?? '-') . '</td>
                </tr>
                <tr>
                    <th>Wilayah</th>
                    <td>' . $e($data['region_name'] ?? '-') . '</td>
                </tr>
                <tr>
                    <th>Tanggal Monitoring</th>
                    <td>' . $e($formatDate($data['visit_date'] ?? '')) . '</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'PETUGAS MONEV')) {
        $html = '
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Petugas</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($members as $index => $member) {
            $html .= '
                <tr>
                    <td class="text-center">' . ($index + 1) . '</td>
                    <td>' . $e($member['name'] ?? 'Petugas') . '</td>
                </tr>';
        }
        if (empty($members)) {
            $html .= '
                <tr>
                    <td colspan="2" class="text-center">Belum ada data petugas.</td>
                </tr>';
        }
        return $html . '
            </tbody>
        </table>';
    }
    if (str_contains($title, 'PELAKSANAAN MONEV')) {
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Uraian</th>
                    <th>Hasil</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sesi TKA-P</td>
                    <td>' . $e($metrics['sesi'] ?? 0) . ' sesi</td>
                </tr>
                <tr>
                    <td>Gelombang</td>
                    <td>' . $e($metrics['gelombang'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td>Jaringan Internet</td>
                    <td>' . $e($metrics['jaringan'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td>Kesiapan Infrastruktur</td>
                    <td>' . $e($metrics['kesiapan'] ?? '-') . '</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'KONDISI SARANA')) {
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Jumlah/Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Labkom</td>
                    <td>' . $e($metrics['labkom'] ?? 0) . ' ruang</td>
                </tr>
                <tr>
                    <td>Ruang yang Dipakai TKA-P</td>
                    <td>' . $e($metrics['ruang'] ?? 0) . ' ruang</td>
                </tr>
                <tr>
                    <td>Switch Hub</td>
                    <td>' . $e($metrics['switch'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Access Point</td>
                    <td>' . $e($metrics['access_point'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Daya Listrik</td>
                    <td>' . $e($metrics['daya'] ?? '-') . '</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'KETERSEDIAAN PERANGKAT')) {
        $deviceStatus = $metrics['device_status'] ?? '-';
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis Perangkat</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PC Milik</td>
                    <td>' . $e($metrics['pc'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Laptop Milik</td>
                    <td>' . $e($metrics['laptop_milik'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Laptop Bukan Milik</td>
                    <td>' . $e($metrics['laptop_bukan_milik'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Total Perangkat</td>
                    <td>' . $e($metrics['total_perangkat'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Peserta</td>
                    <td>' . $e($metrics['total_siswa'] ?? 0) . ' siswa</td>
                </tr>
                <tr>
                    <td>Jumlah Sesi</td>
                    <td>' . $e($metrics['sesi'] ?? 1) . ' sesi</td>
                </tr>
                <tr>
                    <td>Kebutuhan Per Sesi</td>
                    <td>' . $e($metrics['kebutuhan_per_sesi'] ?? 0) . ' perangkat</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td class="status-cell">
                        <div class="status ' . $statusClass($deviceStatus) . '">' . $e($deviceStatus) . '</div>
                        <div class="status-description">' . $e($statusDescription($deviceStatus, 'device')) . '</div>
                    </td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'KESIAPAN PESERTA')) {
        $participantStatus = $metrics['participant_status'] ?? '-';
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Uraian</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Siswa Kelas 12</td>
                    <td class="text-center">' . $e($metrics['total_siswa'] ?? 0) . '</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Mengikuti TKA-P</td>
                    <td class="text-center">' . $e($metrics['ikut'] ?? 0) . '</td>
                    <td>' . $e($metrics['participant_percentage'] ?? 0) . '%</td>
                </tr>
                <tr>
                    <td>Tidak Mengikuti TKA</td>
                    <td class="text-center">' . $e($metrics['tidak_ikut'] ?? 0) . '</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Sesi</td>
                    <td class="text-center">' . $e($metrics['sesi'] ?? 0) . '</td>
                    <td>Per sesi</td>
                </tr>
                <tr>
                    <td>Gelombang</td>
                    <td colspan="2">' . $e($metrics['gelombang'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td colspan="2" class="status-cell">
                        <div class="status ' . $statusClass($participantStatus) . '">' . $e($participantStatus) . '</div>
                        <div class="status-description">' . $e($statusDescription($participantStatus, 'participant')) . '</div>
                    </td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'JARINGAN')) {
        $networkStatus = $metrics['network_status'] ?? '-';
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Hasil</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jaringan Internet</td>
                    <td>' . $e($metrics['jaringan'] ?? '-') . '</td>
                    <td rowspan="3" class="status-cell">
                        <div class="status ' . $statusClass($networkStatus) . '">' . $e($networkStatus) . '</div>
                        <div class="status-description">' . $e($statusDescription($networkStatus, 'network')) . '</div>
                    </td>
                </tr>
                <tr>
                    <td>Bandwidth Upload</td>
                    <td>' . $e($metrics['upload'] ?? 0) . ' Mbps</td>
                </tr>
                <tr>
                    <td>Bandwidth Download</td>
                    <td>' . $e($metrics['download'] ?? 0) . ' Mbps</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'LISTRIK') || str_contains($title, 'PERANGKAT PENDUKUNG')) {
        $electricityStatus = $metrics['electricity_status'] ?? '-';
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Hasil</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Daya Listrik</td>
                    <td>' . $e($metrics['daya'] ?? '-') . '</td>
                    <td rowspan="2" class="status-cell">
                        <div class="status ' . $statusClass($electricityStatus) . '">' . $e($electricityStatus) . '</div>
                        <div class="status-description">' . $e($statusDescription($electricityStatus, 'electricity')) . '</div>
                    </td>
                </tr>
                <tr>
                    <td>UPS</td>
                    <td>' . $e($metrics['ups'] ?? 0) . ' unit</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'STATUS KESIAPAN SEKOLAH')) {
        $overallStatus = $metrics['overall_status'] ?? '-';
        return '
        <div class="overall-status ' . $statusClass($overallStatus) . '">
            ' . $e($overallStatus) . '
        </div>
        <div class="status-description overall-description">
            ' . $e($statusDescription($overallStatus)) . '
        </div>
        <div class="analysis-box">
            <div class="analysis-title">Analisis Status</div>
            <p>' . $e($metrics['status_analysis'] ?? $statusDescription($overallStatus)) . '</p>
        </div>
        <div class="readiness-title">Ringkasan Kesiapan</div>
        <table class="chart-table">
            <tbody>
                <tr>
                    <td class="chart-label">Perangkat</td>
                    <td class="chart-bar-cell">
                        <div class="bar-track">
                            <div class="bar-fill score-' . $scoreStatus($metrics['device_status'] ?? '') . '" style="width:' . (($scoreStatus($metrics['device_status'] ?? '') / 4) * 100) . '%"></div>
                        </div>
                    </td>
                    <td class="chart-status">' . $e($scoreLabel($scoreStatus($metrics['device_status'] ?? ''))) . '</td>
                </tr>
                <tr>
                    <td class="chart-label">Peserta</td>
                    <td class="chart-bar-cell">
                        <div class="bar-track">
                            <div class="bar-fill score-' . $scoreStatus($metrics['participant_status'] ?? '') . '" style="width:' . (($scoreStatus($metrics['participant_status'] ?? '') / 4) * 100) . '%"></div>
                        </div>
                    </td>
                    <td class="chart-status">' . $e($scoreLabel($scoreStatus($metrics['participant_status'] ?? ''))) . '</td>
                </tr>
                <tr>
                    <td class="chart-label">Jaringan</td>
                    <td class="chart-bar-cell">
                        <div class="bar-track">
                            <div class="bar-fill score-' . $scoreStatus($metrics['network_status'] ?? '') . '" style="width:' . (($scoreStatus($metrics['network_status'] ?? '') / 4) * 100) . '%"></div>
                        </div>
                    </td>
                    <td class="chart-status">' . $e($scoreLabel($scoreStatus($metrics['network_status'] ?? ''))) . '</td>
                </tr>
                <tr>
                    <td class="chart-label">Listrik</td>
                    <td class="chart-bar-cell">
                        <div class="bar-track">
                            <div class="bar-fill score-' . $scoreStatus($metrics['electricity_status'] ?? '') . '" style="width:' . (($scoreStatus($metrics['electricity_status'] ?? '') / 4) * 100) . '%"></div>
                        </div>
                    </td>
                    <td class="chart-status">' . $e($scoreLabel($scoreStatus($metrics['electricity_status'] ?? ''))) . '</td>
                </tr>
                <tr>
                    <td class="chart-label">Keseluruhan</td>
                    <td class="chart-bar-cell">
                        <div class="bar-track">
                            <div class="bar-fill score-' . $scoreStatus($overallStatus) . '" style="width:' . (($scoreStatus($overallStatus) / 4) * 100) . '%"></div>
                        </div>
                    </td>
                    <td class="chart-status">' . $e($scoreLabel($scoreStatus($overallStatus))) . '</td>
                </tr>
            </tbody>
        </table>';
    }
    if (str_contains($title, 'ANALISIS HASIL')) {
        return '
        <div class="analysis-box">
            <div class="analysis-title">Analisis Hasil</div>
            <p>' . $e($metrics['analysis'] ?? $metrics['status_analysis'] ?? 'Hasil monitoring menunjukkan kondisi kesiapan sekolah berdasarkan data sarana prasarana, perangkat, peserta, jaringan, listrik, dan perangkat pendukung yang telah diperiksa pada saat monitoring.') . '</p>
        </div>';
    }
    if (str_contains($title, 'TEMUAN DAN TINDAK LANJUT')) {
        $findings = $metrics['findings'] ?? [];
        $recommendations = $metrics['recommendations'] ?? [];
        if (empty($findings)) {
            return '<p>Berdasarkan hasil monitoring dan evaluasi, tidak terdapat temuan yang memerlukan tindak lanjut khusus. Seluruh komponen yang diperiksa berada dalam kondisi yang mendukung pelaksanaan TKA.</p>';
        }
        $html = '<table class="data-table"><thead><tr><th>No</th><th>Temuan</th><th>Tindak Lanjut</th></tr></thead><tbody>';
        foreach ($findings as $index => $finding) {
            $html .= '<tr><td class="text-center">' . ($index + 1) . '</td><td>' . $e($finding) . '</td><td>' . $e($recommendations[$index] ?? 'Melakukan pengecekan dan tindak lanjut sesuai kondisi sekolah.') . '</td></tr>';
        }
        return $html . '</tbody></table>';
    }
    if (str_contains($title, 'KESIMPULAN')) {
        $conclusion = $metrics['conclusion'] ?? '';
        return $conclusion !== '' ? '<p>' . $e($conclusion) . '</p>' : '';
    }
    if (str_contains($title, 'SARAN')) {
        $suggestions = $metrics['suggestions'] ?? '';
        return $suggestions !== '' ? '<p>' . $e($suggestions) . '</p>' : '';
    }
    return '';
};
if ($pdfSection === 'photos'):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 17mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
        }
        .photo-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .photo-item {
            page-break-inside: avoid;
            text-align: center;
            margin-bottom: 18px;
        }
        .photo {
            max-width: 180mm;
            max-height: 230mm;
        }
    </style>
</head>
<body>
    <div class="photo-title">
        B. DOKUMENTASI FOTO
    </div>
    <?php foreach ($data['photos'] ?? [] as $photo): ?>
        <?php
        $url = trim((string) ($photo['answer'] ?? ''));
        $path = parse_url($url, PHP_URL_PATH);
        $path = urldecode($path ?: $url);
        $fileName = basename($path);
        $candidates = [
            FCPATH . 'uploads/monev/foto/' . $fileName,
            FCPATH . 'uploads/monev/photos/' . $fileName,
            FCPATH . 'uploads/monev/dokumentasi/' . $fileName,
            FCPATH . ltrim($path, '/')
        ];
        $filePath = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }
        if (!$filePath) {
            continue;
        }
        $mime = mime_content_type($filePath) ?: 'image/jpeg';
        $base64 = base64_encode(file_get_contents($filePath));
        ?>
        <div class="photo-item">
            <img class="photo" src="data:<?= $e($mime) ?>;base64,<?= $base64 ?>">
        </div>
    <?php endforeach; ?>
</body>
</html>
<?php
else:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-main {
            font-size: 15px;
            font-weight: bold;
        }
        .header-sub {
            font-size: 12px;
            font-weight: bold;
            margin-top: 3px;
        }
        .header-year {
            font-size: 10px;
            margin-top: 3px;
        }
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 22px;
        }
        .identity {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .identity td {
            padding: 4px 5px;
            vertical-align: top;
            font-size: 12pt;
        }
        .identity td:first-child {
            width: 31%;
            font-weight: bold;
        }
        .chapter {
            font-size: 12pt;
            font-weight: bold;
            text-align: left;
            margin: 22px 0 13px;
            text-transform: uppercase;
            color: #1e3a5f;
        }
        .item-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 14px 0 7px;
        }
        .content {
            font-size: 12pt;
            line-height: 1.5;
            margin-bottom: 8px;
            text-align: justify;
        }
        .content p {
            text-align: justify;
            line-height: 1.5;
            margin: 5px 0;
        }
        .content ul,
        .content ol {
            text-align: justify;
            line-height: 1.5;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px;
            font-size: 12pt;
            line-height: 1.5;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.5;
        }
        .data-table th {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .status {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12pt;
            text-align: center;
            vertical-align: middle;
        }
        .status.good {
            background: #dcfce7;
            color: #166534;
        }
        .status.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .status.bad {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-cell {
            text-align: center !important;
            vertical-align: middle !important;
        }
        .status-description {
            margin-top: 6px;
            font-size: 12pt;
            line-height: 1.5;
            text-align: center;
        }
        .overall-status {
            text-align: center;
            vertical-align: middle;
            font-size: 12pt;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #cbd5e1;
            margin: 10px 0;
        }
        .overall-status.good {
            background: #dcfce7;
            color: #166534;
        }
        .overall-status.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .overall-status.bad {
            background: #fee2e2;
            color: #991b1b;
        }
        .analysis-box {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            margin: 8px 0 12px;
        }
        .analysis-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .analysis-box p {
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
        }
        .readiness-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 14px 0 8px;
        }
        .chart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 15px;
            font-size: 12pt;
        }
        .chart-table td {
            padding: 5px 4px;
            vertical-align: middle;
        }
        .chart-label {
            width: 25%;
            font-weight: bold;
        }
        .chart-bar-cell {
            width: 55%;
        }
        .chart-status {
            width: 20%;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
        }
        .bar-track {
            width: 100%;
            height: 13px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }
        .bar-fill {
            height: 13px;
        }
        .score-4 {
            background: #166534;
        }
        .score-3 {
            background: #65a30d;
        }
        .score-2 {
            background: #d97706;
        }
        .score-1 {
            background: #b91c1c;
        }
        .signature {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
            font-size: 12pt;
        }
        .signature-space {
            height: 65px;
        }
        .lampiran {
            page-break-before: always;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            padding-top: 100mm;
        }
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }
        p {
            margin: 5px 0;
            line-height: 1.5;
        }
        ul,
        ol {
            margin-top: 4px;
            line-height: 1.5;
        }
        .header-table {
            width: 560px;
            margin: 0 auto;
            border-collapse: collapse;
        }
        .header-logo {
            width: 80px;
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }
        .header-logo img {
            display: block;
            width: 55px;
            height: auto;
            margin: 0 auto;
        }
        .header-text {
            vertical-align: middle;
            text-align: left;
            padding: 0 0 0 10px;
        }
        .header-main {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.2;
            color: #1f2937;
        }
        .header-sub {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.2;
            color: #1f2937;
        }
        .header-year {
            font-size: 10pt;
            line-height: 1.2;
            margin-top: 2px;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <?php
                    $logoPath = FCPATH . 'assets/img/logo-disdik-dki.png';
                    if (is_file($logoPath)) {
                        $logoMime = mime_content_type($logoPath) ?: 'image/png';
                        $logoBase64 = base64_encode(file_get_contents($logoPath));
                    ?>
                        <img src="data:<?= $e($logoMime) ?>;base64,<?= $logoBase64 ?>">
                    <?php } ?>
                </td>
                <td class="header-text">
                    <div class="header-main">
                        PEMERINTAH PROVINSI DKI JAKARTA
                    </div>
                    <div class="header-sub">
                        DINAS PENDIDIKAN
                    </div>
                    <div class="header-year">
                        TAHUN 2026
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="title">
        LAPORAN MONITORING DAN EVALUASI
    </div>
    <div class="subtitle">
        TES KEMAMPUAN AKADEMIK
    </div>
    <table class="identity">
        <tr>
            <td>Nama Sekolah</td>
            <td><?= $e($data['school_name'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>NPSN</td>
            <td><?= $e($data['npsn'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Jenjang</td>
            <td><?= $e($data['level'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Wilayah</td>
            <td><?= $e($data['region_name'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>Tanggal Monitoring</td>
            <td><?= $e($formatDate($data['visit_date'] ?? '')) ?></td>
        </tr>
    </table>
    <?php
    $currentSection = '';
    $hasLampiran = !empty($data['documents']) || !empty($data['photos']);
    foreach ($template as $row):
        $sectionTitle = trim((string) ($row['section_title'] ?? ''));
        $itemTitle = trim((string) ($row['item_title'] ?? ''));
        $content = $cleanHtml($row['content'] ?? '');
        if ($sectionTitle !== $currentSection):
            $currentSection = $sectionTitle;
    ?>
        <div class="chapter">
            <?= $e($sectionTitle) ?>
        </div>
    <?php endif; ?>
    <?php if ($itemTitle !== ''): ?>
        <div class="item-title">
            <?= $e($itemTitle) ?>
        </div>
    <?php endif; ?>
    <?php if ($content !== ''): ?>
        <div class="content">
            <?= $content ?>
        </div>
    <?php endif; ?>
    <?php $dynamicHtml = $dynamic($itemTitle); ?>
    <?php if ($dynamicHtml !== ''): ?>
        <div class="content">
            <?= $dynamicHtml ?>
        </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($template)): ?>
        <div class="chapter">
            I. PENDAHULUAN
        </div>
        <div class="item-title">
            1. Latar Belakang
        </div>
        <div class="content">
            <p>Laporan monitoring dan evaluasi disusun sebagai dokumentasi hasil pemantauan kesiapan sekolah dalam pelaksanaan Tes Kemampuan Akademik.</p>
        </div>
        <div class="item-title">
            2. Dasar Pelaksanaan
        </div>
        <div class="content">
            <p>Monitoring dan evaluasi dilaksanakan sebagai bagian dari pemantauan kesiapan satuan pendidikan dalam mendukung pelaksanaan Tes Kemampuan Akademik.</p>
        </div>
        <div class="item-title">
            3. Tujuan
        </div>
        <div class="content">
            <p>Monitoring bertujuan memperoleh gambaran kondisi sarana prasarana, perangkat, peserta, jaringan, listrik, dan perangkat pendukung yang tersedia di sekolah.</p>
        </div>
        <div class="item-title">
            4. Ruang Lingkup
        </div>
        <div class="content">
            <p>Ruang lingkup monitoring meliputi kesiapan sarana prasarana, perangkat komputer, peserta, jaringan internet, listrik, perangkat pendukung, serta tindak lanjut atas hasil monitoring.</p>
        </div>
        <div class="chapter">
            II. PELAKSANAAN MONITORING DAN EVALUASI
        </div>
        <div class="item-title">
            1. Waktu dan Tempat
        </div>
        <div class="content">
            <?= $dynamic('WAKTU DAN TEMPAT') ?>
        </div>
        <div class="item-title">
            2. Petugas Monev
        </div>
        <div class="content">
            <?= $dynamic('PETUGAS MONEV') ?>
        </div>
        <div class="item-title">
            3. Pelaksanaan Monev
        </div>
        <div class="content">
            <?= $dynamic('PELAKSANAAN MONEV') ?>
        </div>
        <div class="chapter">
            III. HASIL MONITORING DAN EVALUASI
        </div>
        <div class="item-title">
            1. Kondisi Sarana dan Prasarana
        </div>
        <div class="content">
            <?= $dynamic('KONDISI SARANA DAN PRASARANA') ?>
        </div>
        <div class="item-title">
            2. Ketersediaan Perangkat
        </div>
        <div class="content">
            <?= $dynamic('KETERSEDIAAN PERANGKAT') ?>
        </div>
        <div class="item-title">
            3. Kesiapan Peserta
        </div>
        <div class="content">
            <?= $dynamic('KESIAPAN PESERTA') ?>
        </div>
        <div class="item-title">
            4. Kesiapan Jaringan
        </div>
        <div class="content">
            <?= $dynamic('KESIAPAN JARINGAN') ?>
        </div>
        <div class="item-title">
            5. Kesiapan Listrik dan Perangkat Pendukung
        </div>
        <div class="content">
            <?= $dynamic('LISTRIK DAN PERANGKAT PENDUKUNG') ?>
        </div>
        <div class="item-title">
            6. Status Kesiapan Sekolah
        </div>
        <div class="content">
            <?= $dynamic('STATUS KESIAPAN SEKOLAH') ?>
        </div>
        <div class="item-title">
            7. Analisis Hasil
        </div>
        <div class="content">
            <?= $dynamic('ANALISIS HASIL') ?>
        </div>
        <div class="item-title">
            8. Temuan dan Tindak Lanjut
        </div>
        <div class="content">
            <?= $dynamic('TEMUAN DAN TINDAK LANJUT') ?>
        </div>
        <div class="item-title">
            9. Kesimpulan
        </div>
        <div class="content">
            <?= $dynamic('KESIMPULAN') ?>
        </div>
        <div class="item-title">
            10. Saran
        </div>
        <div class="content">
            <?= $dynamic('SARAN') ?>
        </div>
        <div class="item-title">
            11. Penutup
        </div>
        <div class="content">
            <p>Demikian laporan monitoring dan evaluasi ini disusun sebagai dokumentasi hasil pelaksanaan monitoring dan bahan tindak lanjut kesiapan sekolah dalam pelaksanaan Tes Kemampuan Akademik.</p>
        </div>
    <?php endif; ?>
    <table class="signature">
        <tr>
            <td></td>
            <td>
                Petugas Monitoring dan Evaluasi
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="signature-space"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <strong><?= $e($data['submitted_by_name'] ?? '-') ?></strong>
            </td>
        </tr>
    </table>
    <?php if ($hasLampiran): ?>
        <div class="lampiran">
            LAMPIRAN
        </div>
    <?php endif; ?>
    <div class="footer">
        Laporan Monitoring dan Evaluasi Tes Kemampuan Akademik Tahun 2026
    </div>
</body>
</html>
<?php endif; ?>