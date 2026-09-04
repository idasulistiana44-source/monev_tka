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
            'title' => 'Laporan Monev',
            'pageView' => 'reports/index',
            'pageAsset' => 'reports',
            'pageData' => []
        ]);
    }
    public function regions()
    {
        try {
            $data = $this->reportsModel->getRegions();
            return $this->response->setJSON([
                'status' => true,
                'data' => $data,
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT REGIONS ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Gagal memuat wilayah.',
                'debug' => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }
    public function data()
    {
        try {
            $keyword = $this->request->getGet('keyword');
            $regionId = $this->request->getGet('region_id');
            $status = $this->request->getGet('status');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $keyword = is_string($keyword) ? trim($keyword) : '';
            $regionId = is_string($regionId) ? trim($regionId) : '';
            $status = is_string($status) ? trim($status) : '';
            $dateFrom = is_string($dateFrom) ? trim($dateFrom) : '';
            $dateTo = is_string($dateTo) ? trim($dateTo) : '';
            $userRole = strtolower((string) session()->get('role'));
            $userId = (int) session()->get('user_id');
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
                'status' => true,
                'data' => $data,
                'total' => count($data),
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT DATA ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Gagal memuat data laporan Monev.',
                'debug' => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }
    public function exportExcel()
    {
        try {
            $keyword = $this->request->getGet('keyword');
            $regionId = $this->request->getGet('region_id');
            $status = $this->request->getGet('status');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $keyword = is_string($keyword) ? trim($keyword) : '';
            $regionId = is_string($regionId) ? trim($regionId) : '';
            $status = is_string($status) ? trim($status) : '';
            $dateFrom = is_string($dateFrom) ? trim($dateFrom) : '';
            $dateTo = is_string($dateTo) ? trim($dateTo) : '';
            $userRole = strtolower((string) session()->get('role'));
            $userId = (int) session()->get('user_id');
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
                'status' => true,
                'data' => $data,
                'total' => count($data),
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT EXPORT ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Gagal menyiapkan data export.',
                'debug' => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }
    public function pdf($id)
    {
        try {
            $userRole = strtolower((string) session()->get('role'));
            $userId = (int) session()->get('user_id');
            $data = $this->reportsModel->getReport((int) $id, $userRole, $userId);
            if (!$data) {
                throw new \RuntimeException('Data Monev tidak ditemukan.');
            }
            $data['answers'] = $data['answers'] ?? [];
            $data['documents'] = $data['documents'] ?? [];
            $data['photos'] = $data['photos'] ?? [];
            $data['pdf_section'] = 'main';
            $html = view('reports/pdf', ['data' => $data]);
            if (trim($html) === '') {
                throw new \RuntimeException('Template PDF kosong.');
            }
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('defaultFont', 'DejaVu Sans');
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $mainPdfContent = $dompdf->output();
            if (empty($mainPdfContent)) {
                throw new \RuntimeException('PDF utama gagal dibuat.');
            }
            if (empty($data['documents']) && empty($data['photos'])) {
                $schoolName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $data['school_name'] ?? 'Report');
                return $this->response
                    ->setStatusCode(200)
                    ->setContentType('application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="Laporan_Monev_' . $schoolName . '.pdf"')
                    ->setBody($mainPdfContent);
            }
            $dir = WRITEPATH . 'uploads/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $timestamp = date('YmdHis');
            $mainPdfPath = $dir . 'monev_main_' . (int) $id . '_' . $timestamp . '.pdf';
            file_put_contents($mainPdfPath, $mainPdfContent);
            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);
            $this->appendPdfPages($pdf, $mainPdfPath);
            @unlink($mainPdfPath);
            foreach ($data['documents'] as $document) {
                $filePath = $this->resolveUploadPath($document['answer'] ?? '', 'berkas');
                if (!$filePath) {
                    continue;
                }
                if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
                    continue;
                }
                $this->appendPdfPages($pdf, $filePath);
            }
            if (!empty($data['photos'])) {
                $data['pdf_section'] = 'photos';
                $photoHtml = view('reports/pdf', ['data' => $data]);
                $photoDompdf = new \Dompdf\Dompdf();
                $photoDompdf->set_option('isRemoteEnabled', true);
                $photoDompdf->set_option('isHtml5ParserEnabled', true);
                $photoDompdf->set_option('defaultFont', 'DejaVu Sans');
                $photoDompdf->loadHtml($photoHtml, 'UTF-8');
                $photoDompdf->setPaper('A4', 'portrait');
                $photoDompdf->render();
                $photoPdfPath = $dir . 'monev_photo_' . (int) $id . '_' . $timestamp . '.pdf';
                file_put_contents($photoPdfPath, $photoDompdf->output());
                $this->appendPdfPages($pdf, $photoPdfPath);
                @unlink($photoPdfPath);
            }
            $output = $pdf->Output('S');
            if (empty($output)) {
                throw new \RuntimeException('PDF final gagal dibuat.');
            }
            $schoolName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $data['school_name'] ?? 'Report');
            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="Laporan_Monev_' . $schoolName . '.pdf"')
                ->setBody($output);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT PDF ERROR: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setContentType('text/plain')
                ->setBody('Gagal membuat laporan PDF: ' . $e->getMessage());
        }
    }
    public function exportAllPdf()
    {
        try {
            $keyword = is_string($this->request->getGet('keyword')) ? trim($this->request->getGet('keyword')) : '';
            $regionId = is_string($this->request->getGet('region_id')) ? trim($this->request->getGet('region_id')) : '';
            $status = is_string($this->request->getGet('status')) ? trim($this->request->getGet('status')) : '';
            $dateFrom = is_string($this->request->getGet('date_from')) ? trim($this->request->getGet('date_from')) : '';
            $dateTo = is_string($this->request->getGet('date_to')) ? trim($this->request->getGet('date_to')) : '';
            $userRole = strtolower((string) session()->get('role'));
            $userId = (int) session()->get('user_id');
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
                    ->setContentType('text/plain')
                    ->setBody('Tidak ada laporan Monev yang dapat diekspor.');
            }
            $dir = WRITEPATH . 'uploads/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $timestamp = date('YmdHis');
            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);
            foreach ($reports as $report) {
                $reportId = (int) ($report['id'] ?? 0);
                if ($reportId <= 0) {
                    continue;
                }
                $data = $this->reportsModel->getReport($reportId, $userRole, $userId);
                if (!$data) {
                    continue;
                }
                $data['answers'] = $data['answers'] ?? [];
                $data['documents'] = $data['documents'] ?? [];
                $data['photos'] = $data['photos'] ?? [];
                $data['pdf_section'] = 'main';
                $html = view('reports/pdf', ['data' => $data]);
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->set_option('defaultFont', 'DejaVu Sans');
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $mainPdfPath = $dir . 'bulk_main_' . $reportId . '_' . $timestamp . '.pdf';
                file_put_contents($mainPdfPath, $dompdf->output());
                $this->appendPdfPages($pdf, $mainPdfPath);
                @unlink($mainPdfPath);
                foreach ($data['documents'] as $document) {
                    $filePath = $this->resolveUploadPath($document['answer'] ?? '', 'berkas');
                    if (!$filePath) {
                        continue;
                    }
                    if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
                        continue;
                    }
                    $this->appendPdfPages($pdf, $filePath);
                }
                if (!empty($data['photos'])) {
                    $data['pdf_section'] = 'photos';
                    $photoHtml = view('reports/pdf', ['data' => $data]);
                    $photoDompdf = new \Dompdf\Dompdf();
                    $photoDompdf->set_option('isRemoteEnabled', true);
                    $photoDompdf->set_option('isHtml5ParserEnabled', true);
                    $photoDompdf->set_option('defaultFont', 'DejaVu Sans');
                    $photoDompdf->loadHtml($photoHtml, 'UTF-8');
                    $photoDompdf->setPaper('A4', 'portrait');
                    $photoDompdf->render();
                    $photoPdfPath = $dir . 'bulk_photo_' . $reportId . '_' . $timestamp . '.pdf';
                    file_put_contents($photoPdfPath, $photoDompdf->output());
                    $this->appendPdfPages($pdf, $photoPdfPath);
                    @unlink($photoPdfPath);
                }
            }
            $output = $pdf->Output('S');
            if (empty($output)) {
                throw new \RuntimeException('PDF gabungan gagal dibuat.');
            }
            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="Laporan_Monev_All_' . $timestamp . '.pdf"')
                ->setBody($output);
        } catch (\Throwable $e) {
            log_message('error', 'REPORT EXPORT ALL PDF ERROR: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setContentType('text/plain')
                ->setBody('Gagal membuat seluruh laporan PDF: ' . $e->getMessage());
        }
    }
    protected function resolveUploadPath($value, $type = 'berkas')
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $path = parse_url($value, PHP_URL_PATH);
        $path = urldecode($path ?: $value);
        $fileName = basename($path);
        $candidates = [];
        if ($type === 'berkas') {
            $candidates[] = FCPATH . 'uploads/monev/berkas/' . $fileName;
        } else {
            $candidates[] = FCPATH . 'uploads/monev/foto/' . $fileName;
            $candidates[] = FCPATH . 'uploads/monev/photos/' . $fileName;
            $candidates[] = FCPATH . 'uploads/monev/dokumentasi/' . $fileName;
        }
        $candidates[] = FCPATH . ltrim($path, '/');
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }
        return null;
    }
    protected function appendPdfPages(\setasign\Fpdi\Fpdi $pdf, string $filePath): void
    {
        if (!is_file($filePath)) {
            return;
        }
        try {
            $pageCount = $pdf->setSourceFile($filePath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        } catch (\Throwable $e) {
            log_message('error', 'FPDI MERGE ERROR [' . $filePath . ']: ' . $e->getMessage());
        }
    }
}