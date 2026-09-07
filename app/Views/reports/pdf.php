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
    $status = strtoupper(trim((string) $status));

    if (str_contains($status, 'SANGAT')) {
        return 'sangat-baik';
    }

    if (str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
        return 'baik';
    }

    if (str_contains($status, 'CUKUP') || str_contains($status, 'PERLU')) {
        return 'cukup';
    }

    return 'kurang-memadai';
};
$normalizeStatus = function ($status) {
    $status = strtoupper(trim((string) $status));

    if (str_contains($status, 'SANGAT')) {
        return 'SANGAT BAIK';
    }

    if (str_contains($status, 'BAIK') || str_contains($status, 'MEMADAI')) {
        return 'BAIK';
    }

    if (str_contains($status, 'CUKUP') || str_contains($status, 'PERLU')) {
        return 'CUKUP';
    }

    return 'KURANG MEMADAI';
};

$statusDescription = function ($status, $type = '') {
    $status = strtoupper(trim((string) $status));
    if ($type === 'device') {
    if (str_contains($status, 'SANGAT')) {
        return 'Jumlah perangkat komputer yang tersedia telah memenuhi kebutuhan pelaksanaan TKA dengan cadangan yang memadai.';
    }

    if (str_contains($status, 'BAIK')) {
        return 'Jumlah perangkat komputer yang tersedia telah memenuhi kebutuhan pelaksanaan TKA.';
    }

        if (str_contains($status, 'CUKUP')) {
            return 'Jumlah komputer utama telah mencukupi, namun ketersediaan perangkat cadangan 10% belum sepenuhnya terpenuhi.';
        }

        return 'Jumlah perangkat komputer yang tersedia belum memenuhi kebutuhan. Tersedia ' .
            ($metrics['total_perangkat'] ?? 0) . ' perangkat dari kebutuhan ' .
            ($metrics['kebutuhan_perangkat_juknis'] ?? 0) .
            ' perangkat, sehingga masih terdapat kekurangan ' .
            max(
                0,
                (int)($metrics['kebutuhan_perangkat_juknis'] ?? 0) -
                (int)($metrics['total_perangkat'] ?? 0)
            ) .
            ' perangkat komputer.';
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
$dynamic = function ($itemTitle) use ($metrics, $members, $data, $e, $formatDate, $statusClass, $normalizeStatus, $statusDescription, $scoreStatus, $scoreLabel) {
    $title = strtoupper(trim((string) $itemTitle));
    if (str_contains($title, 'WAKTU DAN TEMPAT')) {
        return '
        <table class="data-table">
            <tbody>
                <tr>
                    <th style="width: 31%;">Nama Sekolah</th>
                    <td>' . $e($data['school_name'] ?? '-') . '</td>
                </tr>
                <tr>
                    <th>NPSN</th>
                    <td>' . $e($data['npsn'] ?? '-') . '</td>
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
    if (str_contains($title, 'PETUGAS')) {
        $html = '
        <table class="data-table">
            <thead>
                <tr>
                    <th style="text-align:center; width:10%;">No</th>
                    <th style="text-align:left; width:50%;">Nama Petugas</th>
                    <th style="text-align:left; width:40%;">Unit Kerja</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($members as $index => $member) {
            $html .= '
                <tr>
                    <td style="text-align:center;">' . ($index + 1) . '</td>
                    <td style="text-align:left;">' . $e($member['name'] ?? 'Petugas') . '</td>
                    <td style="text-align:left;">' . $e($member['institution'] ?? '-') . '</td>
                </tr>';
        }

        if (empty($members)) {
            $html .= '
                <tr>
                    <td colspan="3" style="text-align:center;">Belum ada data petugas.</td>
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
                    <th style="width:45%;">Uraian</th>
                    <th>Hasil</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jumlah Peserta</td>
                    <td>' . $e($metrics['total_siswa'] ?? 0) . ' peserta</td>
                </tr>
                <tr>
                    <td>Jumlah Sesi</td>
                    <td>' . $e($metrics['sesi'] ?? 0) . ' sesi</td>
                </tr>
                <tr>
                    <td>Gelombang</td>
                    <td>' . $e($metrics['gelombang_text'] ?? $metrics['gelombang'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td>Peserta per Sesi</td>
                    <td>' . $e($metrics['kebutuhan_per_sesi'] ?? 0) . ' peserta</td>
                </tr>
                <tr>
                    <td>Komputer Utama yang Dibutuhkan</td>
                    <td>' . $e($metrics['komputer_utama'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Komputer Cadangan (10%)</td>
                    <td>' . $e($metrics['komputer_cadangan'] ?? 0) . ' unit</td>
                </tr>
                <tr>
                    <td>Total Kebutuhan Perangkat Juknis</td>
                    <td><strong>' . $e($metrics['kebutuhan_perangkat_juknis'] ?? 0) . ' unit</strong></td>
                </tr>
                <tr>
                    <td>Kebutuhan Bandwidth</td>
                    <td>' . $e($metrics['network_need'] ?? 0) . ' Mbps untuk ' . $e($metrics['network_clients'] ?? 0) . ' klien</td>
                </tr>
                <tr>
                    <td>Bandwidth Efektif</td>
                    <td>' . $e($metrics['effective_bandwidth'] ?? 0) . ' Mbps</td>
                </tr>
                <tr>
                    <td>Rasio Kapasitas Jaringan</td>
                    <td>' . $e($metrics['network_ratio'] ?? 0) . ' × kebutuhan minimum</td>
                </tr>
                <tr>
                    <td>Kebutuhan Access Point</td>
                    <td>' . $e($metrics['ap_required'] ?? 0) . ' unit (maks. 20 klien/Access point)</td>
                </tr>
            </tbody>
        </table>';
    }

    if (str_contains($title, 'JUMLAH RUANGAN DAN LABORATORIUM KOMPUTER')) {
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 65%;">Uraian</th>
                    <th style="width: 35%; text-align:center;">Hasil Monitoring</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jumlah Ruangan</td>
                    <td style="text-align:center;">' . $e($metrics['ruang'] ?? 0) . ' ruang</td>
                </tr>
                <tr>
                    <td>Lab.Komputer</td>
                    <td style="text-align:center;">' . $e($metrics['labkom'] ?? 0) . ' ruang</td>
                </tr>
            </tbody>
        </table>
        <div class="status-summary">
            <div class="status-header">
                <strong>Acuan Ruang TKA Berdasarkan Juknis</strong>
            </div>
            <div class="status-description">
                Ruang yang digunakan untuk pelaksanaan TKA harus aman dan layak, memiliki pencahayaan dan ventilasi yang cukup, serta terbebas dari alat peraga. 
                Ruang dilengkapi dengan denah tempat duduk peserta dengan mempertimbangkan jarak antar peserta.
                 Penataan ruang disesuaikan dengan kebutuhan pelaksanaan TKA agar kegiatan dapat berlangsung secara tertib dan lancar.
            </div>
        </div>';
    
    }
    
    if (str_contains($title, 'KETERSEDIAAN PERANGKAT')) {

        $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
        $komputerUtama = (int) ($metrics['komputer_utama'] ?? 0);
        $komputerCadangan = (int) ($metrics['komputer_cadangan'] ?? 0);
        $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

        // ==========================================
        // STATUS
        // ==========================================
        if ($totalPerangkat < $komputerUtama) {
            $deviceStatus = 'Kurang Memadai';
        } elseif ($totalPerangkat < $kebutuhanPerangkat) {
            $deviceStatus = 'Cukup';
        } elseif ($totalPerangkat >= ceil($kebutuhanPerangkat * 1.10)) {
            $deviceStatus = 'Sangat Baik';
        } else {
            $deviceStatus = 'Baik';
        }

        // ==========================================
        // KEKURANGAN
        // ==========================================
        $kekuranganPerangkat = max(
            0,
            $kebutuhanPerangkat - $totalPerangkat
        );

        return '

        <!-- ==========================================
            1. DATA HASIL MONITORING
            ========================================== -->

        <div class="subtable-title">
            Data Hasil Monitoring
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:65%;">Jenis Perangkat</th>
                    <th style="width:35%; text-align:center;">Jumlah</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>PC Milik</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['pc'] ?? 0) . ' unit
                    </td>
                </tr>

                <tr>
                    <td>Laptop Milik</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['laptop_milik'] ?? 0) . ' unit
                    </td>
                </tr>

                <tr>
                    <td>Laptop Bukan Milik</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['laptop_bukan_milik'] ?? 0) . ' unit
                    </td>
                </tr>

                <tr>
                    <td><strong>Total Perangkat Tersedia</td>
                    <td style="text-align:center;">
                        ' . $e($totalPerangkat) . ' unit</strong>
                    </td>
                </tr>

            </tbody>
        </table>


        <!-- ==========================================
            2. PERHITUNGAN KEBUTUHAN BERDASARKAN JUKNIS
            ========================================== -->

        <div class="subtable-title">
            Perhitungan Kebutuhan Berdasarkan Juknis
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:65%;">Uraian</th>
                    <th style="width:35%; text-align:center;">Hasil</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Total Peserta</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['total_siswa'] ?? 0) . ' peserta
                    </td>
                </tr>

                <tr>
                    <td>Jumlah Sesi</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['sesi'] ?? 0) . ' sesi
                    </td>
                </tr>

                <tr>
                    <td>Jumlah Gelombang</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['gelombang'] ?? 1) . ' gelombang
                    </td>
                </tr>

                <tr>
                    <td>Rumus Kebutuhan Komputer</td>
                    <td style="text-align:center;">
                        Peserta ÷ (Sesi × Gelombang)
                    </td>
                </tr>

                <tr>
                    <td>Komputer Utama Dibutuhkan</td>
                    <td style="text-align:center;">
                        <strong>' . $e($komputerUtama) . ' unit</strong>
                    </td>
                </tr>

                <tr>
                    <td>Komputer Cadangan (10%)</td>
                    <td style="text-align:center;">
                        ' . $e($komputerCadangan) . ' unit
                    </td>
                </tr>

                <tr>
                    <td><strong>Total Kebutuhan Menurut Juknis</strong></td>
                    <td style="text-align:center;">
                        <strong>' . $e($kebutuhanPerangkat) . ' unit</strong>
                    </td>
                </tr>

            </tbody>
        </table>


        <!-- ==========================================
            3. PERBANDINGAN
            ========================================== -->

        <div class="subtable-title">
            Perbandingan Hasil Monitoring dengan Kebutuhan
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40%;">Komponen</th>
                    <th style="width:20%; text-align:center;">Tersedia</th>
                    <th style="width:20%; text-align:center;">Kebutuhan</th>
                    <th style="width:20%; text-align:center;">Keterangan</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Perangkat Komputer</td>

                    <td style="text-align:center;">
                        ' . $e($totalPerangkat) . ' unit
                    </td>

                    <td style="text-align:center;">
                        ' . $e($kebutuhanPerangkat) . ' unit
                    </td>

                    <td style="text-align:center;">
                        ' .
                        (
                            $kekuranganPerangkat > 0
                            ? '<strong style="color:#dc2626;">' .
                            $e($kekuranganPerangkat) .
                            ' unit</strong>'
                            : '<span style="color:#16a34a;">Memenuhi</span>'
                        )
                        . '
                    </td>
                </tr>

            </tbody>
        </table>


        <!-- ==========================================
            4. STATUS
            ========================================== -->

        <div class="status-summary" style="' .
        (
            $deviceStatus === 'Kurang Memadai'
                ? 'background:#fef2f2;border:1px solid #fecaca;'
                : (
                    $deviceStatus === 'Cukup'
                        ? 'background:#fffbeb;border:1px solid #fde68a;'
                        : (
                            $deviceStatus === 'Baik'
                                ? 'background:#eff6ff;border:1px solid #bfdbfe;'
                                : (
                                    $deviceStatus === 'Sangat Baik'
                                        ? 'background:#f0fdf4;border:1px solid #bbf7d0;'
                                        : ''
                                )
                        )
                )
        ) .
    '">

        <div class="status-header">

            <strong>Status:</strong>

            <span class="status ' . $statusClass($deviceStatus) . '" style="' .
                (
                    $deviceStatus === 'Kurang Memadai'
                        ? 'color:#dc2626;background:#fee2e2;border-color:#fecaca;'
                        : (
                            $deviceStatus === 'Cukup'
                                ? 'color:#92400e;background:#fef3c7;border-color:#fde68a;'
                                : (
                                    $deviceStatus === 'Baik'
                                        ? 'color:#2563eb;background:#dbeafe;border-color:#bfdbfe;'
                                        : (
                                            $deviceStatus === 'Sangat Baik'
                                                ? 'color:#16a34a;background:#dcfce7;border-color:#bbf7d0;'
                                                : ''
                                        )
                                )
                        )
                ) .
            '">
                ' . $e($deviceStatus) . '
            </span>

        </div>

        <div class="status-description" style="
            color:#1e293b;
            font-weight:normal;
        ">

            ' .
            (
                $deviceStatus === 'Kurang Memadai'

                ? 'Jumlah perangkat komputer yang tersedia belum memenuhi kebutuhan. ' .
                'Tersedia ' .
                $e($totalPerangkat) .
                ' perangkat dari kebutuhan ' .
                $e($kebutuhanPerangkat) .
                ' perangkat, sehingga masih terdapat kekurangan ' .
                $e($kekuranganPerangkat) .
                ' perangkat komputer.'

                : (
                    $deviceStatus === 'Cukup'

                    ? 'Jumlah komputer utama telah mencukupi, namun ketersediaan komputer cadangan 10% belum sepenuhnya terpenuhi.'

                    : (
                        $deviceStatus === 'Baik'

                        ? 'Ketersediaan perangkat telah memenuhi kebutuhan pelaksanaan TKA-P.'

                        : (
                            $deviceStatus === 'Sangat Baik'

                            ? 'Ketersediaan perangkat sangat baik dan telah memenuhi kebutuhan pelaksanaan TKA-P.'

                            : $e($statusDescription($deviceStatus, 'device'))
                        )
                    )
                )
            )
            . '

        </div>

    </div>';
    }
    if (str_contains($title, 'KEIKUTSERTAAN') || str_contains($title, 'KESIAPAN PESERTA') || str_contains($title, 'JUMLAH SISWA') || str_contains($title, 'MENGIKUTI TKA-P')) {
        return '
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Uraian</th>
                    <th style="text-align:center;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Siswa Kelas 12</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['total_siswa'] ?? 0) . ' Siswa
                    </td>
                </tr>

                <tr>
                    <td>Mengikuti TKA-P</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['ikut'] ?? 0) . ' Siswa
                    </td>
                </tr>

                <tr>
                    <td>Tidak Mengikuti TKA-P</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['tidak_ikut'] ?? 0) . ' Siswa
                    </td>
                </tr>

                <tr>
                    <td>Total Sesi</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['sesi'] ?? 0) . ' Sesi
                    </td>
                </tr>

                <tr>
                    <td>Gelombang</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['gelombang'] ?? '-') . ' Gelombang
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="status-summary">
            <div class="status-header">
                <strong style="font-size:9pt;">Persentase Keikutsertaan:</strong>
                <span class="status baik">' . (
                    ($metrics['total_siswa'] ?? 0) > 0
                        ? number_format((($metrics['ikut'] ?? 0) / $metrics['total_siswa']) * 100, 2, ',', '.') . '%'
                        : '0%'
                ) . '</span>
            </div>
        </div>';
        
    }
    if (str_contains($title, 'JARINGAN')) {

        $upload = (float) ($metrics['upload'] ?? 0);
        $download = (float) ($metrics['download'] ?? 0);
        $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);

        $networkClients = (int) ($metrics['network_clients'] ?? 0);
        $networkNeed = (float) ($metrics['network_need'] ?? 0);

        $accessPoint = (int) ($metrics['access_point'] ?? 0);
        $apRequired = (int) ($metrics['ap_required'] ?? 0);

        // ==============================
        // PERBANDINGAN
        // ==============================
        $kekuranganBandwidth = max(
            0,
            round($networkNeed - $effectiveBandwidth, 2)
        );

        $kekuranganAP = max(
            0,
            $apRequired - $accessPoint
        );

        // ==============================
        // STATUS
        // ==============================
        if (
            ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) ||
            ($apRequired > 0 && $accessPoint < $apRequired)
        ) {
            $networkStatus = 'Kurang Memadai';

        } elseif (
            $networkNeed > 0 &&
            $effectiveBandwidth >= $networkNeed &&
            $accessPoint >= $apRequired
        ) {
            $networkStatus = 'Baik';

        } else {
            $networkStatus = 'Cukup';
        }

        // ==============================
        // WARNA STATUS
        // ==============================
        if ($networkStatus === 'Kurang Memadai') {

            $statusBoxStyle =
                'background:#fef2f2;' .
                'border:1px solid #fecaca;';

            $statusBadgeStyle =
                'color:#dc2626;' .
                'background:#fee2e2;' .
                'border:1px solid #fecaca;';

            $statusDescriptionColor = '#1e293b';

        } elseif ($networkStatus === 'Baik') {

            $statusBoxStyle =
                'background:#eff6ff;' .
                'border:1px solid #bfdbfe;';

            $statusBadgeStyle =
                'color:#2563eb;' .
                'background:#dbeafe;' .
                'border:1px solid #bfdbfe;';

            $statusDescriptionColor = '#1e293b';

        } elseif ($networkStatus === 'Sangat Baik') {

            $statusBoxStyle =
                'background:#f0fdf4;' .
                'border:1px solid #bbf7d0;';

            $statusBadgeStyle =
                'color:#16a34a;' .
                'background:#dcfce7;' .
                'border:1px solid #bbf7d0;';

            $statusDescriptionColor = '#1e293b';

        } else {

            $statusBoxStyle =
                'background:#fffbeb;' .
                'border:1px solid #fde68a;';

            $statusBadgeStyle =
                'color:#92400e;' .
                'background:#fef3c7;' .
                'border:1px solid #fde68a;';

            $statusDescriptionColor = '#1e293b';
        }

        // ==============================
        // DESKRIPSI STATUS
        // ==============================
        if ($networkStatus === 'Kurang Memadai') {

            $networkDescription =
                'Kapasitas jaringan belum memenuhi kebutuhan pelaksanaan TKA-P. ' .
                'Bandwidth tersedia ' .
                $e($effectiveBandwidth) .
                ' Mbps dari kebutuhan ' .
                $e($networkNeed) .
                ' Mbps, sehingga masih terdapat kekurangan ' .
                $e($kekuranganBandwidth) .
                ' Mbps. Access Point tersedia ' .
                $e($accessPoint) .
                ' unit dari kebutuhan ' .
                $e($apRequired) .
                ' unit, sehingga masih terdapat kekurangan ' .
                $e($kekuranganAP) .
                ' unit.';

        } elseif ($networkStatus === 'Baik') {

            $networkDescription =
                'Kapasitas jaringan telah memenuhi kebutuhan pelaksanaan TKA-P. ' .
                'Tersedia ' .
                $e($effectiveBandwidth) .
                ' Mbps dari kebutuhan ' .
                $e($networkNeed) .
                ' Mbps, serta tersedia ' .
                $e($accessPoint) .
                ' unit Access Point dari kebutuhan ' .
                $e($apRequired) .
                ' unit.';

        } elseif ($networkStatus === 'Sangat Baik') {

            $networkDescription =
                'Kapasitas jaringan sangat baik dan telah memenuhi kebutuhan pelaksanaan TKA-P.';

        } else {

            $networkDescription =
                'Kapasitas jaringan cukup tersedia, namun masih terdapat aspek yang perlu diperhatikan untuk memastikan kestabilan selama pelaksanaan TKA-P.';
        }

        return '

        <div class="subtable-title">
            Data Hasil Monitoring
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:65%;">Komponen</th>
                    <th style="width:35%; text-align:center;">Hasil</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>Jenis/Jaringan Internet</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['jaringan'] ?? '-') . '
                    </td>
                </tr>

                <tr>
                    <td>Bandwidth Upload</td>
                    <td style="text-align:center;">
                        ' . $e($upload) . ' Mbps
                    </td>
                </tr>

                <tr>
                    <td>Bandwidth Download</td>
                    <td style="text-align:center;">
                        ' . $e($download) . ' Mbps
                    </td>
                </tr>

                <tr>
                    <td>Switch Hub</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['switch'] ?? 0) . ' unit
                    </td>
                </tr>

                <tr>
                    <td>Access Point</td>
                    <td style="text-align:center;">
                        ' . $e($accessPoint) . ' unit
                    </td>
                </tr>

            </tbody>
        </table>


        <div class="subtable-title">
            Perhitungan Kebutuhan Berdasarkan Juknis
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:65%;">Uraian</th>
                    <th style="width:35%; text-align:center;">Hasil</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>Siswa Aktif per Sesi</td>
                    <td style="text-align:center;">
                        ' . $e($networkClients) . ' Siswa
                    </td>
                </tr>

                <tr>
                    <td>Rumus Kebutuhan Bandwidth</td>
                    <td style="text-align:center;">
                        0,4 Mbps × jumlah siswa
                    </td>
                </tr>

                <tr>
                    <td>Kebutuhan Bandwidth</td>
                    <td style="text-align:center;">
                        <strong>' . $e($networkNeed) . ' Mbps</strong>
                    </td>
                </tr>

                <tr>
                    <td>Rumus Kebutuhan Access Point</td>
                    <td style="text-align:center;">
                        jumlah siswa ÷ 20
                    </td>
                </tr>

                <tr>
                    <td>Kebutuhan Access Point</td>
                    <td style="text-align:center;">
                        <strong>' . $e($apRequired) . ' unit</strong>
                    </td>
                </tr>

            </tbody>
        </table>


        <div class="subtable-title">
            Perbandingan Hasil Monitoring dengan Kebutuhan
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:35%;">Komponen</th>
                    <th style="width:20%; text-align:center;">Tersedia</th>
                    <th style="width:20%; text-align:center;">Kebutuhan</th>
                    <th style="width:25%; text-align:center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>Bandwidth Efektif</td>
                    <td style="text-align:center;">
                        ' . $e($effectiveBandwidth) . ' Mbps
                    </td>
                    <td style="text-align:center;">
                        ' . $e($networkNeed) . ' Mbps
                    </td>
                    <td style="text-align:center;">
                        ' . (
                            $kekuranganBandwidth > 0
                                ? '<strong style="color:#dc2626;">' .
                                $e($kekuranganBandwidth) .
                                ' Mbps</strong>'
                                : '<span style="color:#16a34a;">Memenuhi</span>'
                        ) . '
                    </td>
                </tr>

                <tr>
                    <td>Access Point</td>
                    <td style="text-align:center;">
                        ' . $e($accessPoint) . ' unit
                    </td>
                    <td style="text-align:center;">
                        ' . $e($apRequired) . ' unit
                    </td>
                    <td style="text-align:center;">
                        ' . (
                            $kekuranganAP > 0
                                ? '<strong style="color:#dc2626;">' .
                                $e($kekuranganAP) .
                                ' unit</strong>'
                                : '<span style="color:#16a34a;">Memenuhi</span>'
                        ) . '
                    </td>
                </tr>

            </tbody>
        </table>


        <div class="status-summary" style="
            ' . $statusBoxStyle . '
        ">

            <div class="status-header">
                <strong>Status:</strong>

                <span class="status ' . $statusClass($networkStatus) . '" style="
                    font-size:8.5pt;
                    padding:3px 7px;
                    border-radius:4px;
                    ' . $statusBadgeStyle . '
                ">
                    ' . $e($networkStatus) . '
                </span>
            </div>

            <div class="status-description" style="
                color:' . $statusDescriptionColor . ';
            ">
                ' . $networkDescription . '
            </div>

        </div>';
    }
    if (str_contains($title, 'LISTRIK') || str_contains($title, 'PERANGKAT PENDUKUNG')) {
        $electricityStatus = $metrics['electricity_status'] ?? '-';
        $electricityStatus = $normalizeStatus($electricityStatus);

        return '
        <div class="subtable-title">Data Hasil Monitoring</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Komponen</th>
                    <th style="width: 60%; text-align:center;">Hasil</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Daya Listrik</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['daya'] ?? '-') . ' Watt
                    </td>
                </tr>

                <tr>
                    <td>UPS</td>
                    <td style="text-align:center;">
                        ' . $e($metrics['ups'] ?? 0) . ' unit
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="subtable-title">Kriteria Berdasarkan Juknis</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Uraian</th>
                    <th style="width: 60%; text-align:center;">Kriteria</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ketersediaan Daya</td>
                    <td style="text-align:center;">
                        Stabil dan cukup untuk seluruh perangkat
                    </td>
                </tr>
            </tbody>
        </table>';
    }
    
    if (str_contains($title, 'STATUS KESIAPAN SEKOLAH')) {

    $totalSiswa = (int) ($metrics['total_siswa'] ?? 0);
    $ikut = (int) ($metrics['ikut'] ?? 0);
    $tidakIkut = max(0, $totalSiswa - $ikut);

    // =====================================================
    // PERANGKAT
    // =====================================================
    $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
    $komputerUtama = (int) ($metrics['komputer_utama'] ?? 0);
    $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

    if ($totalPerangkat < $komputerUtama) {
        $deviceStatus = 'Kurang Memadai';
    } elseif ($totalPerangkat < $kebutuhanPerangkat) {
        $deviceStatus = 'Cukup';
    } elseif ($totalPerangkat >= ceil($kebutuhanPerangkat * 1.10)) {
        $deviceStatus = 'Sangat Baik';
    } else {
        $deviceStatus = 'Baik';
    }

    $kekuranganPerangkat = max(
        0,
        $kebutuhanPerangkat - $totalPerangkat
    );

    // =====================================================
    // JARINGAN
    // =====================================================
    $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);
    $networkNeed = (float) ($metrics['network_need'] ?? 0);

    $accessPoint = (int) ($metrics['access_point'] ?? 0);
    $apRequired = (int) ($metrics['ap_required'] ?? 0);

    if (
        ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) ||
        ($apRequired > 0 && $accessPoint < $apRequired)
    ) {
        $networkStatus = 'Kurang Memadai';
    } elseif (
        $networkNeed > 0 &&
        $effectiveBandwidth >= $networkNeed &&
        $accessPoint >= $apRequired
    ) {
        $networkStatus = 'Baik';
    } else {
        $networkStatus = 'Cukup';
    }

    $kekuranganBandwidth = max(
        0,
        round($networkNeed - $effectiveBandwidth, 2)
    );

    $kekuranganAP = max(
        0,
        $apRequired - $accessPoint
    );

    // =====================================================
    // STATUS FINAL KESIAPAN SEKOLAH
    // PRIORITAS:
    // KURANG MEMADAI > CUKUP > BAIK > SANGAT BAIK
    // =====================================================
    if (
        $deviceStatus === 'Kurang Memadai' ||
        $networkStatus === 'Kurang Memadai'
    ) {
        $overallStatus = 'Kurang Memadai';

    } elseif (
        $deviceStatus === 'Cukup' ||
        $networkStatus === 'Cukup'
    ) {
        $overallStatus = 'Cukup';

    } else {
        $overallStatus = $normalizeStatus(
            $metrics['overall_status'] ?? 'Baik'
        );
    }

    // =====================================================
    // PERSENTASE KEIKUTSERTAAN
    // =====================================================
    $persenPeserta = $totalSiswa > 0
        ? round(($ikut / $totalSiswa) * 100)
        : 0;

    return '

    <div class="readiness-title" style="
        margin:8px 0 10px;
        color:#1e3a5f;
        border-bottom:2px solid #1e3a5f;
        padding-bottom:6px;
    ">
    </div>

    <table style="
        width:100%;
        border-collapse:separate;
        border-spacing:8px;
        margin:0 -8px 8px;
    ">
        <tr>

            <!-- =====================================================
                 1. JUMLAH RUANGAN DAN LABORATORIUM KOMPUTER
                 ===================================================== -->
            <td style="
                width:33.33%;
                border:1px solid #cbd5e1;
                background:#f8fbff;
                padding:12px;
                vertical-align:top;
                text-align:center;
            ">

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                    color:#1e3a5f;
                    margin-bottom:8px;
                ">
                    JUMLAH RUANGAN DAN<br>LABORATORIUM KOMPUTER
                </div>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>

                        <td style="
                            width:50%;
                            text-align:center;
                            border-right:1px solid #cbd5e1;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($metrics['ruang'] ?? 0) . '
                            </div>

                            <div style="font-size:9.5pt;">
                                Ruang
                            </div>

                            <div style="
                                font-size:8.5pt;
                                color:#64748b;
                                margin-top:2px;
                            ">
                                Jumlah Ruangan
                            </div>

                        </td>

                        <td style="
                            width:50%;
                            text-align:center;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($metrics['labkom'] ?? 0) . '
                            </div>

                            <div style="font-size:9.5pt;">
                                Lab
                            </div>

                            <div style="
                                font-size:8.5pt;
                                color:#64748b;
                                margin-top:2px;
                            ">
                                Laboratorium Komputer
                            </div>

                        </td>

                    </tr>
                </table>

                <div style="
                    margin-top:8px;
                    font-size:8.5pt;
                    color:#64748b;
                    line-height:1.35;
                ">
                    <strong>Keterangan:</strong>
                    Tersedia ' . $e($metrics['ruang'] ?? 0) .
                    ' ruang dan ' . $e($metrics['labkom'] ?? 0) . ' lab.
                </div>

            </td>


            <!-- =====================================================
                 2. KETERSEDIAAN PERANGKAT
                 ===================================================== -->
            <td style="
                width:33.33%;
                border:1px solid #cbd5e1;
                background:#f8fafc;
                padding:12px;
                vertical-align:top;
                text-align:center;
            ">

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                    color:#1e3a5f;
                    margin-bottom:8px;
                ">
                    KETERSEDIAAN PERANGKAT
                </div>

                <div style="
                    font-size:20pt;
                    font-weight:bold;
                    color:#1e293b;
                ">
                    ' . $e($kebutuhanPerangkat) . ' / ' .
                    $e($totalPerangkat) . '
                </div>

                <div style="
                    font-size:9.5pt;
                    margin-bottom:8px;
                ">
                    Kebutuhan / Tersedia
                </div>

                <div class="status-summary" style="
                    margin:0;
                    padding:6px 8px;
                    ' . (
                        $deviceStatus === 'Kurang Memadai'
                            ? 'background:#fef2f2;border:1px solid #fecaca;'
                            : (
                                $deviceStatus === 'Cukup'
                                    ? 'background:#fffbeb;border:1px solid #fde68a;'
                                    : ''
                            )
                    ) . '
                ">

                    <div class="status-header" style="
                        justify-content:center;
                    ">

                        <strong style="font-size:10pt;">
                            Status:
                        </strong>

                        <span class="status ' . $statusClass($deviceStatus) . '" style="
                            font-size:8.5pt;
                            padding:3px 7px;
                            border-radius:4px;
                            ' .
                            ($deviceStatus === 'Kurang Memadai'
                                ? 'color:#dc2626;background:#fee2e2;border-color:#fecaca;'
                                : (
                                    $deviceStatus === 'Baik'
                                        ? 'color:#2563eb;background:#dbeafe;border-color:#bfdbfe;'
                                        : (
                                            $deviceStatus === 'Sangat Baik'
                                                ? 'color:#16a34a;background:#dcfce7;border-color:#bbf7d0;'
                                                : 'color:#92400e;background:#fef3c7;border-color:#fde68a;'
                                        )
                                )
                            ) .
                        '">
                            ' . $e($deviceStatus) . '
                        </span>

                    </div>

                </div>

                <div style="
                    margin-top:8px;
                    font-size:8.5pt;
                    color:#64748b;
                    line-height:1.35;
                ">
                    <strong>Keterangan:</strong>
                    ' . (
                        $kekuranganPerangkat > 0
                            ? 'Kekurangan ' . $e($kekuranganPerangkat) . ' perangkat.'
                            : 'Kebutuhan perangkat telah terpenuhi.'
                    ) . '
                </div>

            </td>


            <!-- =====================================================
                 3. KEIKUTSERTAAN SISWA
                 ===================================================== -->
            <td style="
                width:33.33%;
                border:1px solid #cbd5e1;
                background:#fffaf5;
                padding:12px;
                vertical-align:top;
                text-align:center;
            ">

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                    color:#1e3a5f;
                    margin-bottom:8px;
                ">
                    KEIKUTSERTAAN SISWA<br>DALAM TKA
                </div>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>

                        <td style="
                            width:50%;
                            text-align:center;
                            border-right:1px solid #cbd5e1;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($totalSiswa) . '
                            </div>

                            <div style="font-size:9.5pt;">
                                Jumlah Siswa
                            </div>

                        </td>

                        <td style="
                            width:50%;
                            text-align:center;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($ikut) . '
                            </div>

                            <div style="font-size:9.5pt;">
                                Mengikuti TKA-P
                            </div>

                        </td>

                    </tr>
                </table>

                <div class="status-summary" style="
                    margin:8px 0 0;
                    padding:6px 8px;
                ">

                    <div class="status-header" style="
                        justify-content:center;
                    ">

                        <strong style="font-size:10pt;">
                            Persentase Keikutsertaan:
                        </strong>

                        <span class="status sangat-baik" style="
                            font-size:8.5pt;
                            padding:3px 7px;
                            border-radius:4px;
                        ">
                            ' . $e($persenPeserta) . '%
                        </span>

                    </div>

                </div>

                <div style="
                    margin-top:8px;
                    font-size:8.5pt;
                    color:#64748b;
                    line-height:1.35;
                ">
                    <strong>Keterangan:</strong>
                    ' . (
                        $tidakIkut <= 0
                            ? 'Seluruh siswa mengikuti TKA-P.'
                            : 'Terdapat ' . $e($tidakIkut) . ' siswa belum mengikuti TKA-P.'
                    ) . '
                </div>

            </td>

        </tr>


        <tr>

            <!-- =====================================================
                 4. KESIAPAN JARINGAN
                 ===================================================== -->
            <td colspan="2" style="
                width:66.66%;
                border:1px solid #cbd5e1;
                background:#f8fafc;
                padding:12px;
                vertical-align:top;
                text-align:center;
            ">

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                    color:#1e3a5f;
                    margin-bottom:10px;
                    text-align:left;
                ">
                    KESIAPAN JARINGAN
                </div>

                <table style="
                    width:100%;
                    border-collapse:collapse;
                ">
                    <tr>

                        <td style="
                            width:50%;
                            text-align:center;
                            border-right:1px solid #cbd5e1;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($networkNeed) . ' Mbps
                            </div>

                            <div style="font-size:9.5pt;">
                                Kebutuhan Bandwidth
                            </div>

                        </td>

                        <td style="
                            width:50%;
                            text-align:center;
                        ">

                            <div style="
                                font-size:20pt;
                                font-weight:bold;
                                color:#1e293b;
                            ">
                                ' . $e($effectiveBandwidth) . ' Mbps
                            </div>

                            <div style="font-size:9.5pt;">
                                Bandwidth Efektif
                            </div>

                        </td>

                    </tr>
                </table>

                <div class="status-summary" style="
                    margin:8px 0 0;
                    padding:6px 8px;
                    ' . (
                        $networkStatus === 'Kurang Memadai'
                            ? 'background:#fef2f2;border:1px solid #fecaca;'
                            : (
                                $networkStatus === 'Cukup'
                                    ? 'background:#fffbeb;border:1px solid #fde68a;'
                                    : ''
                            )
                    ) . '
                ">

                    <div class="status-header" style="
                        justify-content:center;
                    ">

                        <strong style="font-size:10pt;">
                            Status:
                        </strong>

                        <span class="status ' . $statusClass($networkStatus) . '" style="
                            font-size:8.5pt;
                            padding:3px 7px;
                            border-radius:4px;
                            ' .
                            ($networkStatus === 'Kurang Memadai'
                                ? 'color:#dc2626;background:#fee2e2;border-color:#fecaca;'
                                : (
                                    $networkStatus === 'Baik'
                                        ? 'color:#2563eb;background:#dbeafe;border-color:#bfdbfe;'
                                        : (
                                            $networkStatus === 'Sangat Baik'
                                                ? 'color:#16a34a;background:#dcfce7;border-color:#bbf7d0;'
                                                : 'color:#92400e;background:#fef3c7;border-color:#fde68a;'
                                        )
                                )
                            ) .
                        '">
                            ' . $e($networkStatus) . '
                        </span>

                    </div>

                </div>

                <div style="
                    margin-top:8px;
                    font-size:8.5pt;
                    color:#64748b;
                    line-height:1.35;
                ">
                    <strong>Keterangan:</strong>
                    ' . (
                        ($kekuranganBandwidth > 0 && $kekuranganAP > 0)
                            ? 'Kekurangan ' . $e($kekuranganBandwidth) . ' Mbps dan ' .
                              $e($kekuranganAP) . ' AP.'

                            : (
                                $kekuranganBandwidth > 0
                                    ? 'Kekurangan ' . $e($kekuranganBandwidth) . ' Mbps.'

                                    : (
                                        $kekuranganAP > 0
                                            ? 'Kekurangan ' . $e($kekuranganAP) . ' AP.'
                                            : 'Kebutuhan jaringan telah terpenuhi.'
                                    )
                            )
                    ) . '
                </div>

            </td>


            <!-- =====================================================
                 5. KESIAPAN LISTRIK
                 ===================================================== -->
            <td style="
                width:33.33%;
                border:1px solid #cbd5e1;
                background:#f8fafc;
                padding:12px;
                vertical-align:top;
                text-align:center;
            ">

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                    color:#1e3a5f;
                    margin-bottom:8px;
                ">
                    KESIAPAN LISTRIK
                </div>

                <div style="
                    font-size:13pt;
                    color:#1e293b;
                    margin-top:10px;
                ">
                    ' . $e($metrics['daya'] ?? '-') . '
                </div>

                <div style="
                    font-size:11pt;
                    font-weight:bold;
                ">
                    Watt
                </div>

                <div style="
                    margin-top:10px;
                    padding-top:8px;
                    border-top:1px solid #cbd5e1;
                    font-size:11pt;
                ">
                    <strong>UPS</strong><br>
                    ' . $e($metrics['ups'] ?? 0) . ' unit
                </div>

                <div style="
                    margin-top:8px;
                    font-size:8.5pt;
                    color:#64748b;
                    line-height:1.35;
                ">
                    <strong>Keterangan:</strong>
                    ' . (
                        ((int) ($metrics['ups'] ?? 0) > 0)
                            ? 'Daya listrik dan UPS tersedia.'
                            : 'Daya listrik tersedia, UPS belum tersedia.'
                    ) . '
                </div>

            </td>

        </tr>
    </table>


    <!-- =====================================================
         STATUS KESIAPAN SEKOLAH
         ===================================================== -->

    <div style="
        border:1px solid #cbd5e1;
        background:#f8fafc;
        margin-top:8px;
        padding:14px;
    ">

        <table style="
            width:100%;
            border-collapse:collapse;
        ">
            <tr>

                <td style="
                    width:42%;
                    text-align:center;
                    vertical-align:middle;
                    border-right:1px solid #cbd5e1;
                    padding:8px 15px;
                ">

                    <div style="
                        font-size:9pt;
                        font-weight:bold;
                        color:#1e3a5f;
                        margin-bottom:8px;
                    ">
                        STATUS KESIAPAN SEKOLAH
                    </div>

                    <div style="
                        text-align:center;
                    ">
                        <span class="' . $statusClass($overallStatus) . '" style="
                            display:inline-block;
                            font-size:10pt;
                            font-weight:700;
                            padding:5px 10px;
                            border-radius:5px;
                            ' .
                            ($overallStatus === 'Kurang Memadai'
                                ? 'color:#dc2626;background:#fee2e2;border:1px solid #fecaca;'
                                : ($overallStatus === 'Baik'
                                    ? 'color:#2563eb;background:#dbeafe;border:1px solid #bfdbfe;'
                                    : ($overallStatus === 'Sangat Baik'
                                        ? 'color:#16a34a;background:#dcfce7;border:1px solid #bbf7d0;'
                                        : 'color:#92400e;background:#fef3c7;border:1px solid #fde68a;'
                                    )
                                )
                            ) .
                        '">
                            ' . $e($overallStatus) . '
                        </span>
                    </div>

                </td>


                <td style="
                    width:58%;
                    vertical-align:middle;
                    padding:8px 15px;
                ">

                    <div style="
                        font-size:9pt;
                        font-weight:bold;
                        color:#1e3a5f;
                        margin-bottom:2px;
                    ">
                        Analisis Hasil Monitoring
                    </div>

                    <div style="
                        font-size:9pt;
                        line-height:1.4;
                        text-align:justify;
                    ">
                        ' . (
                            $overallStatus === 'Kurang Memadai'

                            ? 'Masih terdapat komponen yang belum memenuhi kebutuhan pelaksanaan TKA-P.'

                            : (
                                $overallStatus === 'Cukup'

                                    ? 'Kesiapan pelaksanaan cukup, namun masih diperlukan penguatan pada beberapa komponen.'

                                    : (
                                        $overallStatus === 'Baik'

                                            ? 'Kesiapan pelaksanaan TKA-P dalam kondisi baik.'

                                            : (
                                                $overallStatus === 'Sangat Baik'
                                                    ? 'Kesiapan pelaksanaan TKA-P dalam kondisi sangat baik.'
                                                    : $e($statusDescription($overallStatus))
                                            )
                                    )
                            )
                        ) . '
                    </div>

                </td>

            </tr>
        </table>

    </div>';

}
   
    // =====================================================
    // TEMUAN UTAMA
    // =====================================================
    if (
        str_contains($title, 'TEMUAN UTAMA') ||
        (
            str_contains($title, 'TEMUAN') &&
            !str_contains($title, 'TINDAK LANJUT') &&
            !str_contains($title, 'CATATAN')
        )
    ) {
        $findings = [];

        // PERANGKAT
        $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
        $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

        if ($kebutuhanPerangkat > 0 && $totalPerangkat < $kebutuhanPerangkat) {
            $kekurangan = $kebutuhanPerangkat - $totalPerangkat;

            $findings[] =
                'Ketersediaan perangkat belum memenuhi kebutuhan. Tersedia ' .
                $totalPerangkat . ' unit dari kebutuhan ' .
                $kebutuhanPerangkat . ' unit, sehingga masih terdapat kekurangan ' .
                $kekurangan . ' unit.';
        }

        // JARINGAN
        $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);
        $networkNeed = (float) ($metrics['network_need'] ?? 0);

        if ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) {
            $kekuranganBandwidth = round(
                $networkNeed - $effectiveBandwidth,
                2
            );

            $findings[] =
                'Kapasitas bandwidth efektif belum memenuhi kebutuhan. Bandwidth efektif sebesar ' .
                $effectiveBandwidth . ' Mbps, sedangkan kebutuhan sebesar ' .
                $networkNeed . ' Mbps, sehingga terdapat kekurangan sebesar ' .
                $kekuranganBandwidth . ' Mbps.';
        }

        if (empty($findings)) {
            return '
            <div class="status-summary">
                <div class="status-header">
                    <strong>Temuan Utama:</strong>
                    <span class="status sangat-baik">Tidak Ada</span>
                </div>
                <div class="status-description">
                    Berdasarkan hasil monitoring dan evaluasi, tidak terdapat temuan
                    yang memerlukan perhatian khusus pada aspek yang memiliki
                    parameter pembanding.
                </div>
            </div>';
        }

        $html = '
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:8%; text-align:center;">No</th>
                    <th style="width:92%;">Temuan Utama</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($findings as $index => $finding) {
            $html .= '
            <tr>
                <td style="text-align:center !important; vertical-align:middle;">
                ' . ($index + 1) . '
            </td>
                <td>' . $e($finding) . '</td>
            </tr>';
        }

        return $html . '
            </tbody>
        </table>';
    }


    // =====================================================
    // TINDAK LANJUT
    // =====================================================
    if (
        str_contains($title, 'TINDAK LANJUT') ||
        str_contains($title, 'TINDAKLANJUT')
    ) {
        $recommendations = [];

        // PERANGKAT
        $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
        $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

        if ($kebutuhanPerangkat > 0 && $totalPerangkat < $kebutuhanPerangkat) {
            $kekurangan = $kebutuhanPerangkat - $totalPerangkat;

            $recommendations[] =
                'Menambah atau menyiapkan ' .
                $kekurangan .
                ' unit perangkat serta memastikan seluruh perangkat siap digunakan.';
        }

        // JARINGAN
        $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);
        $networkNeed = (float) ($metrics['network_need'] ?? 0);

        if ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) {
            $recommendations[] =
                'Meningkatkan kapasitas bandwidth agar memenuhi kebutuhan ' .
                'pelaksanaan TKA-P dan memastikan kestabilan jaringan selama pelaksanaan.';
        }

        if (empty($recommendations)) {
            return '
            <div class="status-summary">
                <div class="status-header">
                    <strong>Tindak Lanjut:</strong>
                    <span class="status sangat-baik">Tidak Ada</span>
                </div>
                <div class="status-description">
                    Berdasarkan hasil monitoring dan evaluasi, tidak terdapat
                    tindak lanjut khusus yang diperlukan.
                </div>
            </div>';
        }

        $html = '
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:8%; text-align:center;">No</th>
                    <th style="width:92%;">Tindak Lanjut</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($recommendations as $index => $recommendation) {
            $html .= '
            <tr>
                <td style="text-align:center !important; vertical-align:middle;">
                    ' . ($index + 1) . '
                </td>
                <td>' . $e($recommendation) . '</td>
            </tr>';
        }

        return $html . '
            </tbody>
        </table>';
    }

    if (str_contains($title, 'KESIMPULAN')) {

        $totalSiswa = (int) ($metrics['total_siswa'] ?? 0);
        $ikut = (int) ($metrics['ikut'] ?? 0);

        $ruang = (int) ($metrics['ruang'] ?? 0);
        $labkom = (int) ($metrics['labkom'] ?? 0);

        $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
        $komputerUtama = (int) ($metrics['komputer_utama'] ?? 0);
        $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

        $upload = (float) ($metrics['upload'] ?? 0);
        $download = (float) ($metrics['download'] ?? 0);
        $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);
        $networkNeed = (float) ($metrics['network_need'] ?? 0);

        $accessPoint = (int) ($metrics['access_point'] ?? 0);
        $apRequired = (int) ($metrics['ap_required'] ?? 0);

        $daya = $metrics['daya'] ?? '-';
        $ups = (int) ($metrics['ups'] ?? 0);

        $persenPeserta = $totalSiswa > 0
            ? round(($ikut / $totalSiswa) * 100)
            : 0;

        // =====================================================
        // STATUS PERANGKAT
        // =====================================================
        if ($totalPerangkat < $komputerUtama) {

            $deviceStatus = 'Kurang Memadai';

        } elseif ($totalPerangkat < $kebutuhanPerangkat) {

            $deviceStatus = 'Cukup';

        } elseif ($totalPerangkat >= ceil($kebutuhanPerangkat * 1.10)) {

            $deviceStatus = 'Sangat Baik';

        } else {

            $deviceStatus = 'Baik';
        }

        // =====================================================
        // STATUS JARINGAN
        // =====================================================
        if (
            ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) ||
            ($apRequired > 0 && $accessPoint < $apRequired)
        ) {

            $networkStatus = 'Kurang Memadai';

        } elseif (
            $networkNeed > 0 &&
            $effectiveBandwidth >= $networkNeed &&
            $accessPoint >= $apRequired
        ) {

            $networkStatus = 'Baik';

        } else {

            $networkStatus = 'Cukup';
        }

        // =====================================================
        // STATUS FINAL KESIAPAN SEKOLAH
        // PRIORITAS:
        // KURANG MEMADAI > CUKUP > BAIK > SANGAT BAIK
        // =====================================================
        if (
            $deviceStatus === 'Kurang Memadai' ||
            $networkStatus === 'Kurang Memadai'
        ) {

            $overallStatus = 'Kurang Memadai';

        } elseif (
            $deviceStatus === 'Cukup' ||
            $networkStatus === 'Cukup'
        ) {

            $overallStatus = 'Cukup';

        } else {

            $overallStatus = $normalizeStatus(
                $metrics['overall_status'] ?? 'Baik'
            );
        }

        // =====================================================
        // KESIMPULAN DINAMIS
        // =====================================================
        if ($overallStatus === 'Kurang Memadai') {

            $kesimpulan =
                'Berdasarkan hasil Monitoring dan Evaluasi, kesiapan satuan pendidikan masih memerlukan penguatan pada beberapa komponen pendukung pelaksanaan TKA. ' .
                'Ketersediaan perangkat dan kapasitas jaringan belum sepenuhnya memenuhi kebutuhan, sehingga diperlukan tindak lanjut untuk memenuhi kekurangan yang masih terdapat sebelum pelaksanaan TKA.';

        } elseif ($overallStatus === 'Cukup') {

            $kesimpulan =
                'Berdasarkan hasil Monitoring dan Evaluasi, kesiapan satuan pendidikan secara umum telah tersedia untuk mendukung pelaksanaan TKA, namun masih terdapat beberapa komponen yang perlu diperkuat agar seluruh kebutuhan pelaksanaan dapat terpenuhi secara optimal.';

        } elseif ($overallStatus === 'Baik') {

            $kesimpulan =
                'Berdasarkan hasil Monitoring dan Evaluasi, kesiapan satuan pendidikan secara umum telah memenuhi kebutuhan pelaksanaan TKA. ' .
                'Sarana, perangkat, keikutsertaan siswa, jaringan, dan kelistrikan telah tersedia dan mendukung pelaksanaan TKA dengan baik.';

        } elseif ($overallStatus === 'Sangat Baik') {

            $kesimpulan =
                'Berdasarkan hasil Monitoring dan Evaluasi, kesiapan satuan pendidikan secara umum telah sangat memenuhi kebutuhan pelaksanaan TKA. ' .
                'Seluruh komponen pendukung tersedia dalam kondisi sangat baik dan siap mendukung pelaksanaan TKA secara optimal.';

        } else {

            $kesimpulan =
                'Berdasarkan hasil Monitoring dan Evaluasi, kesiapan satuan pendidikan telah dinilai berdasarkan kondisi aktual dan kebutuhan pelaksanaan TKA.';
        }

        // =====================================================
        // TABEL KESIMPULAN
        // =====================================================
        return '

        <table class="data-table">

            <thead>
                <tr>
                    <th style="width:40%;">Komponen</th>
                    <th style="width:60%; text-align:center;">Hasil</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Ruangan</td>
                    <td style="text-align:center;">
                        ' . $e($ruang) . ' ruang
                    </td>
                </tr>

                <tr>
                    <td>Laboratorium Komputer</td>
                    <td style="text-align:center;">
                        ' . $e($labkom) . ' lab
                    </td>
                </tr>

                <tr>
                    <td>Perangkat</td>
                    <td style="text-align:center;">
                        ' . $e($totalPerangkat) . ' tersedia /
                        ' . $e($kebutuhanPerangkat) . ' kebutuhan
                    </td>
                </tr>

                <tr>
                    <td>Keikutsertaan</td>
                    <td style="text-align:center;">
                        ' . $e($ikut) . ' dari
                        ' . $e($totalSiswa) . ' siswa
                        (' . $e($persenPeserta) . '%)
                    </td>
                </tr>

                <tr>
                    <td>Bandwidth</td>
                    <td style="text-align:center;">
                        ' . $e($effectiveBandwidth) . ' Mbps tersedia /
                        ' . $e($networkNeed) . ' Mbps kebutuhan
                    </td>
                </tr>

                <tr>
                    <td>Access Point</td>
                    <td style="text-align:center;">
                        ' . $e($accessPoint) . ' tersedia /
                        ' . $e($apRequired) . ' kebutuhan
                    </td>
                </tr>

                <tr>
                    <td>Daya Listrik</td>
                    <td style="text-align:center;">
                        ' . $e($daya) . ' Watt
                    </td>
                </tr>

                <tr>
                    <td>UPS</td>
                    <td style="text-align:center;">
                        ' . $e($ups) . ' unit
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Status Kesiapan Sekolah</strong>
                    </td>

                    <td style="text-align:center;">

                        <span class="status ' . $statusClass($overallStatus) . '" style="
                            display:inline-block;
                            font-size:9pt;
                            padding:4px 8px;
                            border-radius:4px;
                            ' .
                            ($overallStatus === 'Kurang Memadai'
                                ? 'color:#dc2626;background:#fee2e2;border:1px solid #fecaca;'
                                : (
                                    $overallStatus === 'Baik'
                                        ? 'color:#2563eb;background:#dbeafe;border:1px solid #bfdbfe;'
                                        : (
                                            $overallStatus === 'Sangat Baik'
                                                ? 'color:#16a34a;background:#dcfce7;border:1px solid #bbf7d0;'
                                                : 'color:#92400e;background:#fef3c7;border:1px solid #fde68a;'
                                        )
                                )
                            ) . '
                        ">
                            ' . $e($overallStatus) . '
                        </span>

                    </td>
                </tr>

            </tbody>
        </table>

        <div class="analysis-box">
            <p style="
                font-size:9pt;
                margin:0;
                text-align:justify;
                font-weight:normal;
                color:#1e293b;
            ">
                ' . $kesimpulan . '
            </p>
        </div>

        ';
    }
    if (str_contains($title, 'SARAN')) {

    // =====================================================
    // DATA
    // =====================================================
    $totalPerangkat = (int) ($metrics['total_perangkat'] ?? 0);
    $komputerUtama = (int) ($metrics['komputer_utama'] ?? 0);
    $kebutuhanPerangkat = (int) ($metrics['kebutuhan_perangkat_juknis'] ?? 0);

    $effectiveBandwidth = (float) ($metrics['effective_bandwidth'] ?? 0);
    $networkNeed = (float) ($metrics['network_need'] ?? 0);

    $accessPoint = (int) ($metrics['access_point'] ?? 0);
    $apRequired = (int) ($metrics['ap_required'] ?? 0);

    // =====================================================
    // STATUS PERANGKAT
    // =====================================================
    if ($totalPerangkat < $komputerUtama) {

        $deviceStatus = 'Kurang Memadai';

    } elseif ($totalPerangkat < $kebutuhanPerangkat) {

        $deviceStatus = 'Cukup';

    } elseif ($totalPerangkat >= ceil($kebutuhanPerangkat * 1.10)) {

        $deviceStatus = 'Sangat Baik';

    } else {

        $deviceStatus = 'Baik';
    }

    // =====================================================
    // STATUS JARINGAN
    // =====================================================
    if (
        ($networkNeed > 0 && $effectiveBandwidth < $networkNeed) ||
        ($apRequired > 0 && $accessPoint < $apRequired)
    ) {

        $networkStatus = 'Kurang Memadai';

    } elseif (
        $networkNeed > 0 &&
        $effectiveBandwidth >= $networkNeed &&
        $accessPoint >= $apRequired
    ) {

        $networkStatus = 'Baik';

    } else {

        $networkStatus = 'Cukup';
    }

    // =====================================================
    // STATUS FINAL
    // =====================================================
    if (
        $deviceStatus === 'Kurang Memadai' ||
        $networkStatus === 'Kurang Memadai'
    ) {

        $overallStatus = 'Kurang Memadai';

    } elseif (
        $deviceStatus === 'Cukup' ||
        $networkStatus === 'Cukup'
    ) {

        $overallStatus = 'Cukup';

    } else {

        $overallStatus = $normalizeStatus(
            $metrics['overall_status'] ?? 'Baik'
        );
    }

    // =====================================================
    // KEKURANGAN
    // =====================================================
    $kekuranganPerangkat = max(
        0,
        $kebutuhanPerangkat - $totalPerangkat
    );

    $kekuranganBandwidth = max(
        0,
        round($networkNeed - $effectiveBandwidth, 2)
    );

    $kekuranganAP = max(
        0,
        $apRequired - $accessPoint
    );

    // =====================================================
    // SARAN DINAMIS
    // =====================================================

    if ($overallStatus === 'Kurang Memadai') {

        $saranItems = [];

        if ($kekuranganPerangkat > 0) {
            $saranItems[] =
                'Memenuhi kekurangan <strong>' .
                $e($kekuranganPerangkat) .
                ' perangkat komputer</strong> sesuai kebutuhan pelaksanaan.';
        }

        if ($kekuranganBandwidth > 0) {
            $saranItems[] =
                'Meningkatkan kapasitas bandwidth dari <strong>' .
                $e($effectiveBandwidth) .
                ' Mbps</strong> menjadi minimal <strong>' .
                $e($networkNeed) .
                ' Mbps</strong>.';
        }

        if ($kekuranganAP > 0) {
            $saranItems[] =
                'Menambah <strong>' .
                $e($kekuranganAP) .
                ' unit Access Point</strong> sesuai kebutuhan.';
        }

        $saranItems[] =
            'Memastikan kesiapan jaringan dan perangkat pendukung sebelum pelaksanaan TKA.';

        $saran = '<ol>';

        foreach ($saranItems as $item) {
            $saran .= '<li>' . $item . '</li>';
        }

        $saran .= '</ol>';

    } elseif ($overallStatus === 'Cukup') {

        $saran =
            'Memperkuat komponen yang masih belum sepenuhnya memenuhi kebutuhan serta melakukan pengecekan berkala terhadap perangkat dan jaringan sebelum pelaksanaan TKA.';

    } elseif ($overallStatus === 'Baik') {

        $saran =
            'Mempertahankan kondisi sarana, perangkat, jaringan, dan kelistrikan serta melakukan pengecekan berkala sebelum pelaksanaan TKA.';

    } elseif ($overallStatus === 'Sangat Baik') {

        $saran =
            'Mempertahankan kesiapan seluruh komponen dan melakukan pemeriksaan akhir sebelum pelaksanaan TKA.';

    } else {

        $saran =
            'Melakukan pemeriksaan terhadap seluruh komponen pendukung sebelum pelaksanaan TKA.';
    }

    // =====================================================
    // TAMPILAN
    // =====================================================
    return '

    <div class="analysis-box">
        <div style="
            font-size:10.5pt;
            line-height:1.5;
            color:#1e293b;
            font-weight:normal;
        ">
            ' . $saran . '
        </div>
    </div>

    ';
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
            font-family: Arial, sans-serif;
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
        .status-cell {
            vertical-align: middle !important;
            padding: 8px 10px !important;
        }
        .status-cell .status {
            display: block;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.4;
            margin: 0 0 4px 0;
            text-align: left;
        }
        .status-cell .status-description {
            display: block;
            font-size: 12pt;
            font-weight: normal;
            line-height: 1.5;
            margin: 0;
            text-align: justify;
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
            font-family: Arial, sans-serif;
            font-size: 12pt;
            color: #1f2937;
            margin: 0;
        }
        .header {
            text-align: left;
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
            background: #fff;
            font-weight: bold;
            text-align: left;
            vertical-align: middle;
        }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
        .status.sangat-baik,
        .overall-status.sangat-baik {
            background: #dcfce7;
            color: #166534;
        }
        .status.baik,
        .overall-status.baik {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .status.cukup,
        .overall-status.cukup {
            background: #fef3c7;
            color: #92400e;
        }
        .status.kurang-memadai,
        .overall-status.kurang-memadai {
            background: #fee2e2;
            color: #991b1b;
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
        .subtable-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 8px 0 5px;
        }
       .status-summary {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 7px 10px;
            margin: 8px 0 12px;
            line-height: 1.4;
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-header strong {
            font-size: 11pt;
        }

        .status-header .status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 10.5pt;
            line-height: 1.2;
            font-weight: bold;
        }

        .status-summary .status-description {
            margin-top: 0px;
            font-size: 10pt;
            line-height: 1.4;
            text-align: justify;
        }
        .status-summary .status {
            position: relative;
            top: 4px;
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
        .header-table{width:auto;margin:0 auto;border-collapse:collapse}
        .header-logo{width:90px;text-align:center;vertical-align:middle;padding:0 10px 0 0}
        .header-logo img{width:70px;height:auto;display:block;margin:0 auto}
        .header-text{text-align:left;vertical-align:middle;padding:0}
        .header-main{font-size:18px;font-weight:700;line-height:1.3}
        .header-sub{font-size:17px;font-weight:700;line-height:1.3}
        .header-year{font-size:15px;line-height:1.3;margin-top:3px}
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
            1. Jumlah Ruangan dan Laboratorium Komputer
        </div>
        <div class="content">
            <?= $dynamic('JUMLAH RUANGAN DAN LABORATORIUM KOMPUTER') ?>
        </div>
        <div class="item-title">
            2. Ketersediaan Perangkat
        </div>
        <div class="content">
            <?= $dynamic('KETERSEDIAAN PERANGKAT') ?>
        </div>
        <div class="item-title">
            3. Keikutsertaan Siswa dalam TKA-P
        </div>
        <div class="content">
            <?= $dynamic('KEIKUTSERTAAN SISWA DALAM TKA-P') ?>
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
   <?php
    $petugasMonev = $data['members'] ?? [];

    $tanggalMonev = '';
    if (!empty($data['visit_date'])) {
        $tanggalMonev = $formatDate($data['visit_date']);
    }
    ?>

    <table style="
        width:100%;
        border-collapse:collapse;
        margin-top:30px;
        page-break-inside:avoid;
    ">

        <!-- TANGGAL DAN JUDUL -->
        <tr>
            <td colspan="<?= max(1, count($petugasMonev)) ?>" style="
                text-align:center;
                border:none;
                font-size:11pt;
                padding-bottom:4px;
            ">
                Jakarta, <?= $e($tanggalMonev) ?>
            </td>
        </tr>

        <tr>
            <td colspan="<?= max(1, count($petugasMonev)) ?>" style="
                text-align:center;
                border:none;
                font-size:11pt;
                font-weight:bold;
                padding-bottom:12px;
            ">
                Petugas Monitoring dan Evaluasi
            </td>
        </tr>

        <!-- NAMA PETUGAS -->
        <tr>
            <?php foreach ($petugasMonev as $petugas): ?>
                <td style="
                    width:<?= 100 / max(1, count($petugasMonev)) ?>%;
                    text-align:center;
                    vertical-align:top;
                    border:none;
                    padding:0 8px;
                ">
                    <div style="height:65px;"></div>

                    <div style="
                        font-size:10.5pt;
                        font-weight:bold;
                        text-decoration:underline;
                    ">
                        <?= $e($petugas['name'] ?? 'Petugas') ?>
                    </div>
                </td>
            <?php endforeach; ?>
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