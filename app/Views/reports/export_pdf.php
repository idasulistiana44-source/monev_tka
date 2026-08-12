<!DOCTYPE html>
<html>
<head>
<style>
body{font-family:sans-serif;font-size:11px}
table{width:100%;border-collapse:collapse;margin-bottom:20px}
th,td{border:1px solid #cbd5e1;padding:6px 8px;text-align:left}
th{background:#f1f5f9;font-weight:bold}
.title{font-size:16px;font-weight:bold;margin-bottom:15px;text-align:center}
.section-title{font-size:13px;font-weight:bold;margin-bottom:8px}
</style>
</head>
<body>
<div class="title">LAPORAN REKAPITULASI MONITORTING DAN EVALUASI</div>
<div class="section-title">1. REKAP KESIAPAN PER ASPEK</div>
<table>
    <thead>
        <tr>
            <th>Aspek</th>
            <th>Sangat Memadai</th>
            <th>Baik</th>
            <th>Cukup</th>
            <th>Kurang Memadai</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($aspects as $a): ?>
        <tr>
            <td><?= $a['aspect_name'] ?></td>
            <td><?= $a['sangated_count'] ?></td>
            <td><?= $a['baik_count'] ?></td>
            <td><?= $a['cukup_count'] ?></td>
            <td><?= $a['kurang_count'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="section-title">2. SEKOLAH PERLU TINDAK LANJUT</div>
<table>
    <thead>
        <tr>
            <th>Sekolah</th>
            <th>Aspek</th>
            <th>Temuan</th>
            <th>Rekomendasi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($followups as $f): ?>
        <tr>
            <td><?= $f['school_name'] ?></td>
            <td><?= $f['aspect_name'] ?></td>
            <td><?= $f['finding_text'] ?></td>
            <td><?= $f['recommendation'] ?></td>
            <td><?= $f['status'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>