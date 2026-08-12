<table border="1">
    <thead>
        <tr style="background:#f1f5f9;font-weight:bold;">
            <th colspan="6">REKAP KESIAPAN PER ASPEK</th>
        </tr>
        <tr>
            <th>Aspek</th>
            <th>Sangat Memadai</th>
            <th>Baik</th>
            <th>Cukup</th>
            <th>Kurang Memadai</th>
            <th>Total Jawaban</th>
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
            <td><?= $a['total_answers'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br>
<table border="1">
    <thead>
        <tr style="background:#f1f5f9;font-weight:bold;">
            <th colspan="5">SEKOLAH YANG PERLU TINDAK LANJUT</th>
        </tr>
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