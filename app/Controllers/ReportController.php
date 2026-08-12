<?php
namespace App\Controllers;
use App\Models\ReportModel;

class ReportController extends BaseController {
    protected $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    public function index() {
        return view('layout/template', [
            'title'     => 'Laporan Rekap Monev',
            'pageView'  => 'reports/index',
            'pageAsset' => 'reports',
            'pageData'  => [
                'filters' => $this->reportModel->getFiltersData()
            ]
        ]);
    }

    public function getData() {
        $filters = $this->request->getPost();
        $aspects = $this->reportModel->getAspectSummary($filters);
        $followups = $this->reportModel->getFollowups($filters);
        return $this->response->setJSON([
            'status'    => true,
            'aspects'   => $aspects,
            'followups' => $followups
        ]);
    }

    public function updateFollowupStatus() {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        if (!$id || !in_array($status, ['BELUM', 'PROSES', 'SELESAI'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak valid.']);
        }
        $updated = $this->reportModel->updateFollowupStatus($id, $status);
        if ($updated) {
            return $this->response->setJSON(['status' => true, 'message' => 'Status berhasil diperbarui!']);
        }
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengubah status.']);
    }

    public function exportExcel() {
        $filters = $this->request->getPost();
        $aspects = $this->reportModel->getAspectSummary($filters);
        $followups = $this->reportModel->getFollowups($filters);
        $html = view('reports/export_excel', ['aspects' => $aspects, 'followups' => $followups]);
        return $this->response->setJSON([
            'status'   => true,
            'filename' => 'Laporan_Rekap_Monev_' . date('YmdHis') . '.xls',
            'html'     => $html
        ]);
    }

    public function exportPdf() {
        $filters = $this->request->getPost();
        $data['aspects'] = $this->reportModel->getAspectSummary($filters);
        $data['followups'] = $this->reportModel->getFollowups($filters);
        $html = view('reports/export_pdf', $data);
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');
        return $this->response->setJSON([
            'status'   => true,
            'filename' => 'Laporan_Rekap_Monev_' . date('YmdHis') . '.pdf',
            'filebase64' => base64_encode($pdfContent)
        ]);
    }
}