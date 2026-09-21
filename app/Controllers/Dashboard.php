<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class Dashboard extends BaseController
{
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        $model = $this->dashboardModel;

        $data = [
            'title'         => 'Dashboard',
            'pageName'      => 'dashboard/index',
            'pageView'      => 'dashboard/index',
            'pageAsset'     => 'dashboard',
            'pageData'      => [],

            'dashboardCss'  => true,
            'userCss'       => false,
            'schoolCss'     => false,
            'assignmentCss' => false,

            // Summary
            'totalSchools'    => $model->getTotalSchools(),
            'inProgressSchools' => $model->getInProgressSchools(),
            'visitedSchools'  => $model->getVisitedSchools(),
            'readinessPercent'=> $model->getReadinessPercent(),

            // Data dashboard
            'draftSchools' => $model->getDraftSchools(),
            'totalOfficers'   => $model->getTotalOfficers(),
            'totalVisits'     => $model->getTotalVisits(),
            'status'          => $model->getVisitStatus(),
            'readiness'       => $model->getInfrastructureReadiness(),
            'infrastructure'  => $model->getInfrastructureData(),
            'electricity'     => $model->getElectricityData(),
            'internet'        => $model->getInternetData(),
            'ispUtama'          => $model->getBandwidthData(11),
            'ispCadangan'        => $model->getBandwidthData(25),
            'students'        => $model->getStudentData(),
            'sessions'        => $model->getSessionData(),
            'waves'           => $model->getWaveData(),
            'visitsByRegion'  => $model->getVisitsByRegion(),
            'recentVisits'    => $model->getRecentVisits(),
            'visitsByLevel'   => $model->getVisitsByLevel(),
        ];

        return view('layout/template', $data);
    }
    
    public function export()
    {
        $filters = [
            'start_date'  => $this->request->getGet('start_date'),
            'end_date'    => $this->request->getGet('end_date'),
            'level'       => $this->request->getGet('level'),
            'region_id'   => $this->request->getGet('region_id'),
            'district_id' => $this->request->getGet('district_id'),
        ];

        $model = $this->dashboardModel;

        // 1. Ambil Data Dasar & Summary
        $summary      = $model->getDashboardSummary($filters);
        $visitStatus  = $model->getVisitStatus();
        $readiness    = $model->getInfrastructureReadiness($filters);
        $totalOfficers= $model->getTotalOfficers();
        $totalVisits  = $model->getTotalVisits();

        // 2. Hitung Persentase Status Kunjungan untuk Chart SVG
        $totalStatusCount = array_sum($visitStatus);
        $visitStatusPercent = [
            'selesai'    => $totalStatusCount > 0 ? round(($visitStatus['selesai'] / $totalStatusCount) * 100, 1) : 0,
            'berlangsung'=> $totalStatusCount > 0 ? round(($visitStatus['berlangsung'] / $totalStatusCount) * 100, 1) : 0,
            'draft'      => $totalStatusCount > 0 ? round(($visitStatus['draft'] / $totalStatusCount) * 100, 1) : 0,
        ];

        // 3. Hitung Persentase Kesiapan Infrastruktur untuk Chart SVG
        $totalReadinessCount = array_sum($readiness);
        $readinessPercent = [];
        foreach ($readiness as $key => $val) {
            $readinessPercent[$key] = $totalReadinessCount > 0 ? round(($val / $totalReadinessCount) * 100, 1) : 0;
        }

        // 4. Susun Semua Payload Data ke View PDF
        $data = [
            'title'                  => 'Laporan Hasil Monitoring & Evaluasi Kesiapan Infrastruktur TKAP',
            'generatedAt'            => date('d F Y H:i'),
            'filters'                => $filters,
            'summary'                => $summary,
            'totalOfficers'          => $totalOfficers,
            'totalVisits'            => $totalVisits,
            
            // Status & Chart Data
            'visitStatus'            => $visitStatus,
            'visitStatusPercent'     => $visitStatusPercent,
            'visitsByLevel'          => $model->getVisitsByLevel($filters),
            'visitsByRegion'         => $model->getVisitsByRegion($filters),

            // Infrastruktur & Jaringan
            'readiness'              => $readiness,
            'readinessPercent'       => $readinessPercent,
            'readinessData'          => $model->getReadinessData($filters),
            'infrastructure'         => $model->getInfrastructureData($filters),
            'electricity'            => $model->getElectricityData($filters),
            'internet'               => $model->getInternetData($filters),
            'ispUtama'               => $model->getBandwidthData(11, $filters),
            'ispCadangan'             => $model->getBandwidthData(25, $filters),

            // Peserta & Pelaksanaan
            'students'               => $model->getStudentData($filters),
            'sessions'               => $model->getSessionData($filters),
            'waves'                  => $model->getWaveData($filters),

            // Rekapitulasi & Masalah
            'monevStatus'            => $model->getMonevStatusRecap($filters),
            'officerRecap'           => $model->getMonevOfficerRecap($filters),
            'problemRecommendations' => $model->getProblemRecommendationRecap($filters),
        ];

        // 5. Render View ke HTML
        $html = view('dashboard/export_pdf', $data);

        // 6. Konfigurasi dan Inisialisasi Dompdf
        $dompdf = new \Dompdf\Dompdf();
        
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 7. Download PDF
        $fileName = 'Laporan_Executive_Monev_TKAP_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($fileName, ['Attachment' => true]);
    }

    public function data()
    {
        $filters = [
            'start_date'  => $this->request->getGet('start_date'),
            'end_date'    => $this->request->getGet('end_date'),
            'level'       => $this->request->getGet('level'),
            'region_id'   => $this->request->getGet('region_id'),
            'district_id' => $this->request->getGet('district_id'),
        ];

        $model = $this->dashboardModel;

       return $this->response->setJSON([
            'summary' => $model->getDashboardSummary($filters),

            'infrastructure' => $model->getInfrastructureData($filters),
            'electricity'    => $model->getElectricityData($filters),
            'internet'       => $model->getInternetData($filters),
            'ispUtama'         => $model->getBandwidthData(11, $filters),
            'ispCadangan'       => $model->getBandwidthData(25, $filters),
            'students'       => $model->getStudentData($filters),
            'sessions'       => $model->getSessionData($filters),
            'waves'          => $model->getWaveData($filters),
            'readiness'      => $model->getInfrastructureReadiness($filters),

            'readinessData'  => $model->getReadinessData($filters),
            'monevStatus'=>$model->getMonevStatusRecap($filters),
            'officerRecap'=>$model->getMonevOfficerRecap($filters),
            'problemRecommendations'=>$model->getProblemRecommendationRecap($filters)
        ]);
    }
    
    public function regions()
    {
        return $this->response->setJSON([
            'regions'=>$this->dashboardModel->getRegions()
        ]);
    }

    public function districts()
    {
        $regionId=$this->request->getGet('region_id');

        return $this->response->setJSON([
            'districts'=>$this->dashboardModel->getDistricts($regionId)
        ]);
    }
}