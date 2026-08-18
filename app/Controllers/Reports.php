<?php
namespace App\Controllers;
use App\Models\ReportsModel;
class Reports extends BaseController
{
    protected $reportsModel;
    public function __construct()
    {
        $this->reportsModel=new ReportsModel();
    }
    public function index()
    {
        return view('layout/template',[
            'title'=>'Laporan Monev',
            'pageView'=>'reports/index',
            'pageAsset'=>'reports',
            'pageData'=>[]
        ]);
    }
    public function regions()
    {
        try{
            $data=$this->reportsModel->getRegions();
            return $this->response->setJSON([
                'status'=>true,
                'data'=>$data,
                'csrfHash'=>csrf_hash()
            ]);
        }catch(\Throwable $e){
            log_message('error','REPORT REGIONS ERROR: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>'Gagal memuat wilayah.',
                'debug'=>$e->getMessage(),
                'csrfHash'=>csrf_hash()
            ]);
        }
    }
    public function data()
    {
        try{
            $keyword=$this->request->getGet('keyword');
            $regionId=$this->request->getGet('region_id');
            $status=$this->request->getGet('status');
            $dateFrom=$this->request->getGet('date_from');
            $dateTo=$this->request->getGet('date_to');
            $keyword=is_string($keyword)?trim($keyword):'';
            $regionId=is_string($regionId)?trim($regionId):'';
            $status=is_string($status)?trim($status):'';
            $dateFrom=is_string($dateFrom)?trim($dateFrom):'';
            $dateTo=is_string($dateTo)?trim($dateTo):'';
            $data=$this->reportsModel->getReports($keyword,$regionId,$status,$dateFrom,$dateTo);
            return $this->response->setJSON([
                'status'=>true,
                'data'=>$data,
                'csrfHash'=>csrf_hash()
            ]);
        }catch(\Throwable $e){
            log_message('error','REPORT DATA ERROR: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>'Gagal memuat data laporan Monev.',
                'debug'=>$e->getMessage(),
                'csrfHash'=>csrf_hash()
            ]);
        }
    }
    public function exportExcel()
    {
        try{
            $keyword=$this->request->getGet('keyword');
            $regionId=$this->request->getGet('region_id');
            $status=$this->request->getGet('status');
            $dateFrom=$this->request->getGet('date_from');
            $dateTo=$this->request->getGet('date_to');
            $keyword=is_string($keyword)?trim($keyword):'';
            $regionId=is_string($regionId)?trim($regionId):'';
            $status=is_string($status)?trim($status):'';
            $dateFrom=is_string($dateFrom)?trim($dateFrom):'';
            $dateTo=is_string($dateTo)?trim($dateTo):'';
            $data=$this->reportsModel->getReports($keyword,$regionId,$status,$dateFrom,$dateTo);
            return $this->response->setJSON([
                'status'=>true,
                'data'=>$data,
                'csrfHash'=>csrf_hash()
            ]);
        }catch(\Throwable $e){
            log_message('error','REPORT EXPORT ERROR: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>'Gagal menyiapkan data export.',
                'debug'=>$e->getMessage(),
                'csrfHash'=>csrf_hash()
            ]);
        }
    }
 
  public function pdf($id)
{
    try {
        $data = $this->reportsModel->getReport((int) $id);

        if (!$data) {
            throw new \RuntimeException('Data Monev tidak ditemukan.');
        }

        $data['answers']   = $data['answers'] ?? [];
        $data['documents'] = $data['documents'] ?? [];
        $data['photos']    = $data['photos'] ?? [];

        $timestamp = time();

        $outputPdf = WRITEPATH
            . 'uploads/laporan_monev_'
            . (int) $id
            . '_'
            . $timestamp
            . '.pdf';

        $pdf = new \setasign\Fpdi\Fpdi();

        $pdf->SetAutoPageBreak(false);

        /*
        |--------------------------------------------------------------------------
        | 1. HALAMAN UTAMA
        |--------------------------------------------------------------------------
        */

        $data['pdf_section'] = 'main';

        $html = view('reports/pdf', [
            'data' => $data
        ]);

        $dompdf = new \Dompdf\Dompdf();

        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $mainPdf = WRITEPATH
            . 'uploads/report_main_'
            . (int) $id
            . '_'
            . $timestamp
            . '.pdf';

        file_put_contents(
            $mainPdf,
            $dompdf->output()
        );

        $this->appendPdfPages(
            $pdf,
            $mainPdf
        );


        /*
        |--------------------------------------------------------------------------
        | 2. BERKAS YANG DIUPLOAD
        |--------------------------------------------------------------------------
        */

        $documentIndex = 0;

        foreach ($data['documents'] as $document) {

            $url = trim(
                (string) ($document['answer'] ?? '')
            );

            if ($url === '') {
                continue;
            }

            $path = parse_url(
                $url,
                PHP_URL_PATH
            );

            $path = urldecode(
                $path ?: $url
            );

            $fileName = basename($path);

            /*
            |--------------------------------------------------------------------------
            | Lokasi berkas
            |--------------------------------------------------------------------------
            */

            $filePath = FCPATH
                . 'uploads/monev/berkas/'
                . $fileName;

            if (!is_file($filePath)) {

                $filePath = FCPATH
                    . ltrim($path, '/');
            }

            if (!is_file($filePath)) {

                log_message(
                    'error',
                    'BERKAS TIDAK DITEMUKAN: ' . $url
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Hanya PDF
            |--------------------------------------------------------------------------
            */

            $extension = strtolower(
                pathinfo(
                    $filePath,
                    PATHINFO_EXTENSION
                )
            );

            if ($extension !== 'pdf') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Berkas pertama
            | Tempelkan header C pada halaman pertama
            |--------------------------------------------------------------------------
            */

            if ($documentIndex === 0) {

                $this->appendFirstDocumentWithHeader(
                    $pdf,
                    $filePath
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Berkas berikutnya langsung append
                |--------------------------------------------------------------------------
                */

                $this->appendPdfPages(
                    $pdf,
                    $filePath
                );
            }

            $documentIndex++;
        }


        /*
        |--------------------------------------------------------------------------
        | 3. FOTO DOKUMENTASI
        |--------------------------------------------------------------------------
        */

        if (!empty($data['photos'])) {

            $data['pdf_section'] = 'photos';

            $photoHtml = view('reports/pdf', [
                'data' => $data
            ]);

            $photoDompdf = new \Dompdf\Dompdf();

            $photoDompdf->set_option(
                'isRemoteEnabled',
                true
            );

            $photoDompdf->loadHtml(
                $photoHtml
            );

            $photoDompdf->setPaper(
                'A4',
                'portrait'
            );

            $photoDompdf->render();

            $photoPdf = WRITEPATH
                . 'uploads/report_photos_'
                . (int) $id
                . '_'
                . $timestamp
                . '.pdf';

            file_put_contents(
                $photoPdf,
                $photoDompdf->output()
            );

            $this->appendPdfPages(
                $pdf,
                $photoPdf
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. SIMPAN PDF FINAL
        |--------------------------------------------------------------------------
        */

        $pdf->Output(
            'F',
            $outputPdf
        );


        /*
        |--------------------------------------------------------------------------
        | 5. HAPUS FILE SEMENTARA
        |--------------------------------------------------------------------------
        */

        @unlink($mainPdf);

        if (isset($photoPdf)) {
            @unlink($photoPdf);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. DOWNLOAD
        |--------------------------------------------------------------------------
        */

        return $this->response
            ->download(
                $outputPdf,
                null
            )
            ->setFileName(
                'Laporan_Monev_'
                . ($data['school_name'] ?? $id)
                . '.pdf'
            );

    } catch (\Throwable $e) {

        log_message(
            'error',
            'REPORT PDF ERROR: '
            . $e->getMessage()
        );

        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'status' => false,
                'message' => 'Gagal memuat laporan PDF.',
                'debug' => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
    }
}


/*
|--------------------------------------------------------------------------
| APPEND PDF BIASA
|--------------------------------------------------------------------------
*/

private function appendPdfPages(
    $pdf,
    $filePath
) {
    $pageCount = $pdf->setSourceFile(
        $filePath
    );

    for (
        $pageNo = 1;
        $pageNo <= $pageCount;
        $pageNo++
    ) {

        $template = $pdf->importPage(
            $pageNo
        );

        $size = $pdf->getTemplateSize(
            $template
        );

        $orientation =
            $size['width'] > $size['height']
                ? 'L'
                : 'P';

        $pdf->AddPage(
            $orientation,
            [
                $size['width'],
                $size['height']
            ]
        );

        $pdf->useTemplate(
            $template
        );
    }
}


/*
|--------------------------------------------------------------------------
| HALAMAN PERTAMA BERKAS + HEADER C
|--------------------------------------------------------------------------
*/

private function appendFirstDocumentWithHeader(
    $pdf,
    $filePath
) {
    $pageCount = $pdf->setSourceFile(
        $filePath
    );

    /*
    |--------------------------------------------------------------------------
    | Ambil halaman pertama
    |--------------------------------------------------------------------------
    */

    $template = $pdf->importPage(1);

    $size = $pdf->getTemplateSize(
        $template
    );

    /*
    |--------------------------------------------------------------------------
    | Gunakan ukuran A4
    |--------------------------------------------------------------------------
    */

    $pageWidth = 210;
    $pageHeight = 297;

    $pdf->AddPage(
        'P',
        [
            $pageWidth,
            $pageHeight
        ]
    );

    /*
|--------------------------------------------------------------------------
| Header C. BERKAS YANG DIUPLOAD
|--------------------------------------------------------------------------
*/

$pdf->SetFont(
    'Arial',
    'B',
    13
);

$pdf->SetFillColor(
    238,
    238,
    238
);

/*
|--------------------------------------------------------------------------
| Background full lebar halaman A4
|--------------------------------------------------------------------------
*/

$pdf->SetXY(
    0,
    10
);

$pdf->Cell(
    210,
    8,
    '',
    0,
    1,
    'L',
    true
);

/*
|--------------------------------------------------------------------------
| Tulisan header
|--------------------------------------------------------------------------
*/

$pdf->SetXY(
    5,
    10
);

$pdf->Cell(
    200,
    8,
    'C. BERKAS YANG DIUPLOAD',
    0,
    1,
    'L',
    false
);


    /*
    |--------------------------------------------------------------------------
    | Posisi dokumen di bawah header
    |--------------------------------------------------------------------------
    */

    $documentTop = 7;

    $availableHeight =
        $pageHeight
        - $documentTop
        - 2;

    $availableWidth =
        $pageWidth
        - 4;

    /*
    |--------------------------------------------------------------------------
    | Hitung skala supaya dokumen masuk
    |--------------------------------------------------------------------------
    */

    $scaleWidth =
        $availableWidth
        / $size['width'];

    $scaleHeight =
        $availableHeight
        / $size['height'];

    $scale = min(
        $scaleWidth,
        $scaleHeight
    );

    $documentWidth =
        $size['width']
        * $scale;

    $documentHeight =
        $size['height']
        * $scale;

    $documentX =
        ($pageWidth - $documentWidth)
        / 2;

    /*
    |--------------------------------------------------------------------------
    | Masukkan halaman pertama PDF berkas
    |--------------------------------------------------------------------------
    */

    $pdf->useTemplate(
        $template,
        $documentX,
        $documentTop,
        $documentWidth,
        $documentHeight
    );


    /*
    |--------------------------------------------------------------------------
    | Halaman kedua dan seterusnya
    |--------------------------------------------------------------------------
    */

    for (
        $pageNo = 2;
        $pageNo <= $pageCount;
        $pageNo++
    ) {

        $template = $pdf->importPage(
            $pageNo
        );

        $size = $pdf->getTemplateSize(
            $template
        );

        $orientation =
            $size['width'] > $size['height']
                ? 'L'
                : 'P';

        $pdf->AddPage(
            $orientation,
            [
                $size['width'],
                $size['height']
            ]
        );

        $pdf->useTemplate(
            $template
        );
    }
} 

}