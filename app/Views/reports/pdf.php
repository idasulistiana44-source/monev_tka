<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monev</title>
    <style>
        @page{margin:25px}
        body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;color:#222}
        h1{font-size:18px;text-align:center;margin:0 0 5px}
        h2{font-size:13px;background:#eee;padding:7px;margin:18px 0 8px}
        .header{text-align:center;border-bottom:2px solid #222;padding-bottom:12px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #999;padding:6px;vertical-align:top}
        th{background:#eee}
        .info td:first-child{width:150px;font-weight:bold}
        .answer-table th:first-child,.answer-table td:first-child{width:40px;text-align:center}
        .answer-table th:last-child,.answer-table td:last-child{width:140px}
        .photo-container{width:100%;text-align:center}
        .photo-item{width:100%;margin-bottom:15px;text-align:center}
        .photo{width:100%;height:800px;object-fit:contain}
        .section-title {font-size: 13px;font-weight: bold;background: #eeeeee;padding: 7px;margin: 18px 0 8px;box-sizing: border-box;}
    </style>
</head>
<body>
<?php
$reportFormatAnswer=function($question,$answer){
    $question=strtoupper(trim($question));
    $answer=trim((string)$answer);
    if($answer===''){
        return '-';
    }
    if(str_contains($question,'KOMPUTER/PC')){
        return $answer.' komputer';
    }
    if(str_contains($question,'LAPTOP')){
        return $answer.' laptop';
    }
    if(str_contains($question,'LABKOM')){
        return $answer.' labkom';
    }
    if(str_contains($question,'RUANG')){
        return $answer.' ruang';
    }
    if(str_contains($question,'SWITCH HUB')){
        return $answer.' switch hub';
    }
    if(str_contains($question,'ACCESPOINT')){
        return $answer.' access point';
    }
    if(str_contains($question,'UPS')){
        return $answer.' UPS';
    }
    return $answer;
};
$reportGetImageDataUri=function($url){
    if(empty($url)){
        return '';
    }
    $path=parse_url($url,PHP_URL_PATH);
    $path=urldecode($path?:$url);
    $path=ltrim($path,'/');
    $fileName=basename($path);
    $filePath=FCPATH.'uploads/monev/foto/'.$fileName;
    if(!is_file($filePath)){
        $filePath=FCPATH.ltrim($path,'/');
    }
    if(!is_file($filePath)){
        return '';
    }
    $mime=mime_content_type($filePath);
    if(!$mime){
        $extension=strtolower(pathinfo($filePath,PATHINFO_EXTENSION));
        $mime=$extension==='png'?'image/png':($extension==='gif'?'image/gif':'image/jpeg');
    }
    $content=file_get_contents($filePath);
    if($content===false){
        return '';
    }
    return 'data:'.$mime.';base64,'.base64_encode($content);
};
?>
<?php if(($data['pdf_section']??'main')==='main'): ?>
    <div class="header">
        <h1>LAPORAN HASIL MONITORING DAN EVALUASI</h1>
        <div>MONITORING DAN EVALUASI TKA-PROVINSI</div>
    </div>
    <h2>A. IDENTITAS KEGIATAN</h2>
    <table class="info">
        <tr>
            <td>Wilayah</td>
            <td><?= esc($data['region_name']??'-') ?></td>
        </tr>
        <tr>
            <td>Sekolah</td>
            <td><?= esc($data['school_name']??'-') ?></td>
        </tr>
        <tr>
            <td>NPSN</td>
            <td><?= esc($data['npsn']??'-') ?></td>
        </tr>
        <tr>
            <td>Jenjang</td>
            <td><?= esc($data['level']??'-') ?></td>
        </tr>
        <tr>
            <td>Tanggal Monev</td>
            <td><?= esc($data['visit_date']??'-') ?></td>
        </tr>
        <tr>
            <td>Petugas</td>
            <td><?= esc($data['member_names']??'-') ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td><?= esc($data['status']??'-') ?></td>
        </tr>
    </table>
    <h2>B. HASIL PENGISIAN FORM MONEV</h2>
    <?php if(!empty($data['answers'])): ?>
        <table class="answer-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pertanyaan</th>
                    <th>Hasil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['answers'] as $i=>$answer): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= esc($answer['question']??'-') ?></td>
                        <td><?= esc($reportFormatAnswer($answer['question']??'',$answer['answer']??'')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak terdapat hasil pengisian form.</p>
    <?php endif; ?>
<?php elseif(($data['pdf_section'] ?? '') === 'documents'): ?>
    <div class="section-title">
        C. BERKAS YANG DIUPLOAD
    </div>
    <?php if (!empty($data['documents'])): ?>
        <p>Berkas yang dikumpulkan terlampir pada halaman berikutnya.</p>
    <?php else: ?>
        <p>Tidak terdapat berkas yang diupload.</p>
    <?php endif; ?>
<?php elseif(($data['pdf_section']??'')==='photos'): ?>
    <h2>D. FOTO DOKUMENTASI</h2>
    <?php if(!empty($data['photos'])): ?>
        <div class="photo-container">
            <?php foreach($data['photos'] as $photo): ?>
                <?php
                $url=$photo['answer']??'';
                $imageData=$reportGetImageDataUri($url);
                ?>
                <?php if($imageData): ?>
                    <div class="photo-item">
                        <img class="photo" src="<?= $imageData ?>">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Tidak terdapat foto dokumentasi.</p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>