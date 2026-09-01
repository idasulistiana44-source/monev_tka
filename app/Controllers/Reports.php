<?php
namespace App\Controllers;

use App\Models\ReportsModel;

class Reports extends BaseController
{
    protected $reportsModel;

    public function __construct()
    {
        $this->reportsModel = new ReportsModel();
    }

    public function index()
    {
        return view('layout/template', [
            'title'     => 'Laporan Monev',
            'pageView'  => 'reports/index',
            'pageAsset' => 'reports',
            'pageData'  => []
        ]);
    }

    public function regions()
    {
        try {
            $data = $this->reportsModel->getRegions();
            return $this->response->setJSON([
                'status'   => true,
                'data'     => $data,
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT REGIONS ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'   => false,
                'message'  => 'Gagal memuat wilayah.',
                'debug'    => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }

    public function data()
    {
        try {
            $keyword  = $this->request->getGet('keyword');
            $regionId = $this->request->getGet('region_id');
            $status   = $this->request->getGet('status');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo   = $this->request->getGet('date_to');

            $keyword  = is_string($keyword) ? trim($keyword) : '';
            $regionId = is_string($regionId) ? trim($regionId) : '';
            $status   = is_string($status) ? trim($status) : '';
            $dateFrom = is_string($dateFrom) ? trim($dateFrom) : '';
            $dateTo   = is_string($dateTo) ? trim($dateTo) : '';

            $userRole = strtolower((string) session()->get('role'));
            $userId   = (int) session()->get('user_id');

            $data = $this->reportsModel->getReports(
                $keyword,
                $regionId,
                $status,
                $dateFrom,
                $dateTo,
                $userRole,
                $userId
            );

            return $this->response->setJSON([
                'status'   => true,
                'data'     => $data,
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT DATA ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'   => false,
                'message'  => 'Gagal memuat data laporan Monev.',
                'debug'    => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }

    public function exportExcel()
    {
        try {
            $keyword  = $this->request->getGet('keyword');
            $regionId = $this->request->getGet('region_id');
            $status   = $this->request->getGet('status');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo   = $this->request->getGet('date_to');

            $keyword  = is_string($keyword) ? trim($keyword) : '';
            $regionId = is_string($regionId) ? trim($regionId) : '';
            $status   = is_string($status) ? trim($status) : '';
            $dateFrom = is_string($dateFrom) ? trim($dateFrom) : '';
            $dateTo   = is_string($dateTo) ? trim($dateTo) : '';

            $userRole = strtolower((string) session()->get('role'));
            $userId   = (int) session()->get('user_id');

            $data = $this->reportsModel->getReports(
                $keyword,
                $regionId,
                $status,
                $dateFrom,
                $dateTo,
                $userRole,
                $userId
            );

            return $this->response->setJSON([
                'status'   => true,
                'data'     => $data,
                'total'    => count($data),
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT EXPORT ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'   => false,
                'message'  => 'Gagal menyiapkan data export.',
                'debug'    => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK PDF TUNGGAL (1 LAPORAN SEKOLAH)
    |--------------------------------------------------------------------------
    */
    public function pdf($id)
    {
        try {
            $userRole = strtolower((string) session()->get('role'));
            $userId   = (int) session()->get('user_id');

            $data = $this->reportsModel->getReport((int) $id, $userRole, $userId);

            if (!$data) {
                throw new \RuntimeException('Data Monev tidak ditemukan.');
            }

            $data['answers']   = $data['answers'] ?? [];
            $data['documents'] = $data['documents'] ?? [];
            $data['photos']    = $data['photos'] ?? [];

            $timestamp = time();
            $outputPdf = WRITEPATH . 'uploads/laporan_monev_' . (int) $id . '_' . $timestamp . '.pdf';

            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);

            // 1. HALAMAN UTAMA
            $data['pdf_section'] = 'main';
            $html = view('reports/pdf', ['data' => $data]);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $mainPdf = WRITEPATH . 'uploads/report_main_' . (int) $id . '_' . $timestamp . '.pdf';
            file_put_contents($mainPdf, $dompdf->output());

            $this->appendPdfPages($pdf, $mainPdf);
            @unlink($mainPdf);

            // 2. DOKUMEN LAMPIRAN PDF
            $isFirstDoc = true;
            foreach ($data['documents'] as $document) {
                $url = trim((string) ($document['answer'] ?? ''));
                if ($url === '') {
                    continue;
                }

                $path     = parse_url($url, PHP_URL_PATH);
                $path     = urldecode($path ?: $url);
                $fileName = basename($path);
                $filePath = FCPATH . 'uploads/monev/berkas/' . $fileName;

                if (!is_file($filePath)) {
                    $filePath = FCPATH . ltrim($path, '/');
                }

                if (!is_file($filePath)) {
                    log_message('error', 'DOKUMEN REPORT TIDAK DITEMUKAN: ' . $url);
                    continue;
                }

                if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
                    continue;
                }

                if ($isFirstDoc) {
                    $this->appendFirstDocumentWithHeader($pdf, $filePath);
                    $isFirstDoc = false;
                } else {
                    $this->appendPdfPages($pdf, $filePath);
                }
            }

            // 3. FOTO DOKUMENTASI
            if (!empty($data['photos'])) {
                $data['pdf_section'] = 'photos';
                $photoHtml = view('reports/pdf', ['data' => $data]);

                $photoDompdf = new \Dompdf\Dompdf();
                $photoDompdf->set_option('isRemoteEnabled', true);
                $photoDompdf->loadHtml($photoHtml);
                $photoDompdf->setPaper('A4', 'portrait');
                $photoDompdf->render();

                $photoPdf = WRITEPATH . 'uploads/report_photos_' . (int) $id . '_' . $timestamp . '.pdf';
                file_put_contents($photoPdf, $photoDompdf->output());

                $this->appendPdfPages($pdf, $photoPdf);
                @unlink($photoPdf);
            }

            // 4. SIMPAN & DOWNLOAD PDF FINAL
            $pdf->Output('F', $outputPdf);

            $schoolName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $data['school_name'] ?? 'Report');

            return $this->response
                ->download($outputPdf, null)
                ->setFileName('Laporan_Monev_' . $schoolName . '.pdf');

        } catch (\Throwable $e) {
            log_message('error', 'REPORT PDF ERROR: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'   => false,
                    'message'  => 'Gagal memuat laporan PDF.',
                    'debug'    => $e->getMessage(),
                    'csrfHash' => csrf_hash()
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK BANYAK LAPORAN SEKOLAH (BULK EXPORT)
    |--------------------------------------------------------------------------
    */
    public function exportAllPdf()
    {
        try {
            $keyword  = is_string($this->request->getGet('keyword')) ? trim($this->request->getGet('keyword')) : '';
            $regionId = is_string($this->request->getGet('region_id')) ? trim($this->request->getGet('region_id')) : '';
            $status   = is_string($this->request->getGet('status')) ? trim($this->request->getGet('status')) : '';
            $dateFrom = is_string($this->request->getGet('date_from')) ? trim($this->request->getGet('date_from')) : '';
            $dateTo   = is_string($this->request->getGet('date_to')) ? trim($this->request->getGet('date_to')) : '';

            $userRole = strtolower((string) session()->get('role'));
            $userId   = (int) session()->get('user_id');

            $reports = $this->reportsModel->getReports(
                $keyword,
                $regionId,
                $status,
                $dateFrom,
                $dateTo,
                $userRole,
                $userId
            );

            if (empty($reports)) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'status'   => false,
                        'message'  => 'Tidak ada laporan Monev yang dapat diekspor.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $timestamp = time();
            $outputPdf = WRITEPATH . 'uploads/laporan_monev_all_' . $timestamp . '.pdf';

            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);

            foreach ($reports as $report) {
                $id = (int) ($report['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $data = $this->reportsModel->getReport($id, $userRole, $userId);
                if (!$data) {
                    continue;
                }

                $data['answers']   = $data['answers'] ?? [];
                $data['documents'] = $data['documents'] ?? [];
                $data['photos']    = $data['photos'] ?? [];

                // 1. LAPORAN MAIN PER SEKOLAH
                $data['pdf_section'] = 'main';
                $mainHtml = view('reports/pdf', ['data' => $data]);

                $dompdf = new \Dompdf\Dompdf();
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->loadHtml($mainHtml);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $mainPdf = WRITEPATH . 'uploads/report_all_main_' . $id . '_' . $timestamp . '.pdf';
                file_put_contents($mainPdf, $dompdf->output());

                $this->appendPdfPages($pdf, $mainPdf);
                @unlink($mainPdf);

                // 2. DOKUMEN LAMPIRAN PDF PER SEKOLAH
                $isFirstDoc = true;
                foreach ($data['documents'] as $document) {
                    $url = trim((string) ($document['answer'] ?? ''));
                    if ($url === '') {
                        continue;
                    }

                    $path     = parse_url($url, PHP_URL_PATH);
                    $path     = urldecode($path ?: $url);
                    $fileName = basename($path);
                    $filePath = FCPATH . 'uploads/monev/berkas/' . $fileName;

                    if (!is_file($filePath)) {
                        $filePath = FCPATH . ltrim($path, '/');
                    }

                    if (!is_file($filePath)) {
                        continue;
                    }

                    if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
                        continue;
                    }

                    if ($isFirstDoc) {
                        $this->appendFirstDocumentWithHeader($pdf, $filePath);
                        $isFirstDoc = false;
                    } else {
                        $this->appendPdfPages($pdf, $filePath);
                    }
                }

                // 3. FOTO DOKUMENTASI PER SEKOLAH
                if (!empty($data['photos'])) {
                    $data['pdf_section'] = 'photos';
                    $photoHtml = view('reports/pdf', ['data' => $data]);

                    $photoDompdf = new \Dompdf\Dompdf();
                    $photoDompdf->set_option('isRemoteEnabled', true);
                    $photoDompdf->loadHtml($photoHtml);
                    $photoDompdf->setPaper('A4', 'portrait');
                    $photoDompdf->render();

                    $photoPdf = WRITEPATH . 'uploads/report_all_photos_' . $id . '_' . $timestamp . '.pdf';
                    file_put_contents($photoPdf, $photoDompdf->output());

                    $this->appendPdfPages($pdf, $photoPdf);
                    @unlink($photoPdf);
                }
            }

            $pdf->Output('F', $outputPdf);
            $filename = 'Laporan_Monev_All_' . date('Ymd_His') . '.pdf';

            return $this->response
                ->download($outputPdf, null)
                ->setFileName($filename);

        } catch (\Throwable $e) {
            log_message('error', 'REPORT EXPORT ALL PDF ERROR: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'   => false,
                    'message'  => 'Gagal menyiapkan seluruh laporan PDF.',
                    'debug'    => $e->getMessage(),
                    'csrfHash' => csrf_hash()
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS MERGE PDF
    |--------------------------------------------------------------------------
    */
    protected function appendPdfPages(\setasign\Fpdi\Fpdi $pdf, string $filePath): void
    {
        if (!is_file($filePath)) {
            return;
        }

        try {
            $pageCount = $pdf->setSourceFile($filePath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size       = $pdf->getTemplateSize($templateId);

                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        } catch (\Throwable $e) {
            log_message('error', 'FPDI MERGE ERROR [' . $filePath . ']: ' . $e->getMessage());
        }
    }

    private function appendFirstDocumentWithHeader(\setasign\Fpdi\Fpdi $pdf, string $filePath): void
    {
        if (!is_file($filePath)) {
            return;
        }

        try {
            $pageCount = $pdf->setSourceFile($filePath);
            $template  = $pdf->importPage(1);
            $size      = $pdf->getTemplateSize($template);

            $pageWidth  = 210;
            $pageHeight = 297;

            $pdf->AddPage('P', [$pageWidth, $pageHeight]);

            // Header Judul Lampiran
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->SetFillColor(238, 238, 238);
            $pdf->SetXY(0, 10);
            $pdf->Cell(210, 8, '', 0, 1, 'L', true);

            $pdf->SetXY(5, 10);
            $pdf->Cell(200, 8, 'C. BERKAS YANG DIUPLOAD', 0, 1, 'L', false);

            $documentTop     = 20;
            $availableHeight = $pageHeight - $documentTop - 5;
            $availableWidth  = $pageWidth - 10;

            $scaleWidth  = $availableWidth / $size['width'];
            $scaleHeight = $availableHeight / $size['height'];
            $scale       = min($scaleWidth, $scaleHeight);

            $documentWidth  = $size['width'] * $scale;
            $documentHeight = $size['height'] * $scale;
            $documentX      = ($pageWidth - $documentWidth) / 2;

            $pdf->useTemplate($template, $documentX, $documentTop, $documentWidth, $documentHeight);

            for ($pageNo = 2; $pageNo <= $pageCount; $pageNo++) {
                $templateId  = $pdf->importPage($pageNo);
                $pageSize    = $pdf->getTemplateSize($templateId);
                $orientation = ($pageSize['width'] > $pageSize['pageSize']) ? 'L' : 'P';

                $pdf->AddPage($orientation, [$pageSize['width'], $pageSize['height']]);
                $pdf->useTemplate($templateId);
            }
        } catch (\Throwable $e) {
            log_message('error', 'FPDI HEADER MERGE ERROR [' . $filePath . ']: ' . $e->getMessage());
        }
    }
}