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
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        // Inisialisasi model sebelum digunakan
        $model = $this->dashboardModel;

        $filters = [
            'start_date'  => $this->request->getGet('start_date'),
            'end_date'    => $this->request->getGet('end_date'),
            'level'       => $this->request->getGet('level'),
            'region_id'   => $this->request->getGet('region_id'),
            'district_id' => $this->request->getGet('district_id'),
            'school_id'   => $this->request->getGet('school_id')
        ];

        $filterWilayahName = 'SELURUH WILAYAH DKI JAKARTA';
        $filterSchoolName  = '';

        if (!empty($filters['region_id'])) {
            $regions = $model->getRegions();

            foreach ($regions as $region) {
                if (
                    (string) ($region['id'] ?? '') ===
                    (string) $filters['region_id']
                ) {
                    $filterWilayahName = $region['name']
                        ?? $region['region_name']
                        ?? 'SELURUH WILAYAH DKI JAKARTA';

                    break;
                }
            }
        }

        $model=$this->dashboardModel;

        try{
            $summary=$model->getDashboardSummary($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil summary: '.$e->getMessage());
            $summary=[];
        }

        try{
            $monevStatus=$model->getMonevStatusRecap($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil status Monev: '.$e->getMessage());
            $monevStatus=[];
        }

        try{
            $officerRecap=$model->getMonevOfficerRecap($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil rekap pelaksana: '.$e->getMessage());
            $officerRecap=[];
        }

        try{
            $problemRecommendations=$model->getProblemRecommendationRecap($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil permasalahan dan rekomendasi: '.$e->getMessage());
            $problemRecommendations=[];
        }

        try{
            $visitsByLevel=$model->getVisitsByLevel($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil data jenjang: '.$e->getMessage());
            $visitsByLevel=[];
        }

        try{
            $infrastructure=$model->getInfrastructureData($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil data infrastruktur: '.$e->getMessage());
            $infrastructure=[];
        }

        try{
            $electricity=$model->getElectricityData($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil data daya listrik: '.$e->getMessage());
            $electricity=[];
        }

        try{
            $backupInternetSchools=$model->getSchoolsWithoutBackupInternet($filters);
        }catch(\Throwable $e){
            log_message('error','Gagal mengambil data sekolah tanpa ISP cadangan: '.$e->getMessage());
            $backupInternetSchools=[];
        }
  
        // Ambil daftar sekolah yang belum memiliki Draft Monev
        try {
            $schoolsWithoutDraft = $model->getSchoolsWithoutDraft($filters);

            // Normalisasi field agar konsisten dengan template PDF
            $schoolsWithoutDraft = array_map(static function ($row) {
                $row['petugas'] = $row['petugas']
                    ?? $row['nama_petugas']
                    ?? $row['officer_name']
                    ?? '-';

                $row['created_at'] = $row['created_at']
                    ?? $row['tanggal_dibuat']
                    ?? $row['assignment_date']
                    ?? null;

                return $row;
            }, $schoolsWithoutDraft);

        } catch (\Throwable $e) {
            log_message(
                'error',
                'Gagal mengambil daftar Draft Monev: ' . $e->getMessage()
            );

            $schoolsWithoutDraft = [];
        }
        // Ambil daftar Monev yang sedang berlangsung
        try {
            $inProgressVisits = $model->getInProgressVisits($filters);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Gagal mengambil daftar Monev sedang berlangsung: ' . $e->getMessage()
            );
            $inProgressVisits = [];
        }

 
     $data=[
        'title'=>'Laporan Monitoring & Evaluasi TKAP',
        'schoolsWithoutDraft' => $schoolsWithoutDraft,
        'inProgressVisits'    => $inProgressVisits,
        'generatedAt'=>date('d F Y H:i'),
        'filters'=>$filters,
        'summary'=>$summary,
        'monevStatus'=>$monevStatus,
        'officerRecap'=>$officerRecap,
        'problemRecommendations'=>$problemRecommendations,
        'visitsByLevel'=>$visitsByLevel,
        'infrastructure'=>$model->getInfrastructureData($filters),
        'electricity'=>$electricity,
        'internet'=>$model->getInternetData($filters),
        'mainBandwidth'=>$model->getBandwidthData(11,$filters),
        'backupBandwidth'=>$model->getBandwidthData(25,$filters),
        'ispUtama' => $model->getIspDashboardData('ispUtama', $filters),
        'ispCadangan' => $model->getIspDashboardData('ispCadangan', $filters),
        'studentReadiness'=>$model->getStudentReadinessReportData($filters),
        'session'=>$model->getSessionReportData($filters),
        'wave'=>$model->getWaveReportData($filters),
        'infrastructureReadiness'=>$model->getInfrastructureReadinessReportData($filters),
        'backupInternetSchools'=>$backupInternetSchools,
        'filterWilayahName' => $filterWilayahName,
        'filterSchoolName'  => $filterSchoolName,

    ];

        $html=view('dashboard/export_pdf',$data);

        unset(
            $summary,
            $monevStatus,
            $officerRecap,
            $problemRecommendations,
            $visitsByLevel,
            $infrastructure,
            $electricity,
            $backupInternetSchools
        );

        $dompdf=new \Dompdf\Dompdf();

        $options=$dompdf->getOptions();
        $options->set('isRemoteEnabled',false);
        $options->set('isHtml5ParserEnabled',true);
        $options->set('defaultFont','Arial');
        $options->set('isPhpEnabled',false);
        $options->set('isFontSubsettingEnabled',true);
        $options->set('dpi',96);

        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        unset($html);

        $fileName='Laporan_Monev_TKAP_'.date('Ymd_His').'.pdf';

        $dompdf->stream($fileName,[
            'Attachment'=>false
        ]);

        exit;
    }

public function data()
{
    // Inisialisasi model terlebih dahulu
    $model = $this->dashboardModel;

    $filters = [
        'start_date'  => $this->request->getGet('start_date'),
        'end_date'    => $this->request->getGet('end_date'),
        'level'       => $this->request->getGet('level'),
        'region_id'   => $this->request->getGet('region_id'),
        'district_id' => $this->request->getGet('district_id'),
        'school_id'   => $this->request->getGet('school_id')
    ];

    $filterWilayahName = 'SELURUH WILAYAH DKI JAKARTA';
    $filterSchoolName  = '';

    // Ambil nama wilayah berdasarkan filter
    if (!empty($filters['region_id'])) {
        $regions = $model->getRegions();

        foreach ($regions as $region) {
            if (
                (string) ($region['id'] ?? '') ===
                (string) $filters['region_id']
            ) {
                $filterWilayahName = $region['name']
                    ?? $region['region_name']
                    ?? 'SELURUH WILAYAH DKI JAKARTA';

                break;
            }
        }
    }

    // Filter sekolah (belum ada logika tambahan di bagian ini)
    if (!empty($filters['school_id'])) {
        // Tambahkan logika nama sekolah jika diperlukan.
    }

    // Rekap daya listrik
    $electricity = [];

    try {
        $electricity = $model->getElectricityRecap($filters);
    } catch (\Throwable $e) {
        log_message(
            'error',
            'Gagal mengambil data daya listrik: ' . $e->getMessage()
        );

        $electricity = [];
    }

    // Data kunjungan/sekolah berstatus Draft
    $schoolsWithoutDraft = [];

    
    try {
        $schoolsWithoutDraft = $model->getSchoolsWithoutDraft($filters);

        $schoolsWithoutDraft = array_map(static function ($row) {
            $row['petugas'] = $row['petugas'] ?? '-';
            $row['tim_monev'] = $row['tim_monev'] ?? '-';
            $row['created_at'] = $row['created_at'] ?? null;

            return $row;
        }, $schoolsWithoutDraft);

    } catch (\Throwable $e) {
        log_message(
            'error',
            'Gagal mengambil daftar Draft Monev: ' . $e->getMessage()
        );

        $schoolsWithoutDraft = [];
    }


    try {
        $inProgressVisits = $model->getInProgressVisits($filters);
    } catch (\Throwable $e) {
        log_message(
            'error',
            'Gagal mengambil kunjungan Monev berlangsung: ' . $e->getMessage()
        );

        $inProgressVisits = [];
    }
    return $this->response->setJSON([
        'schoolsWithoutDraft' => $schoolsWithoutDraft,
        'inProgressVisits' => $inProgressVisits,
        'summary' => $model->getDashboardSummary($filters),
        'infrastructure' => $model->getInfrastructureData($filters),
        'electricity' => $model->getElectricityData($filters),
        'internet' => $model->getInternetData($filters),
        'ispUtama' => $model->getIspDashboardData('ispUtama', $filters),
        'ispCadangan' => $model->getIspDashboardData('ispCadangan', $filters),
        'students' => $model->getStudentData($filters),
        'sessions' => $model->getSessionData($filters),
        'waves' => $model->getWaveData($filters),
        'readiness' => $model->getInfrastructureReadiness($filters),
        'readinessData' => $model->getReadinessData($filters),
        'monevStatus' => $model->getMonevStatusRecap($filters),
        'officerRecap' => $model->getMonevOfficerRecap($filters),
        'problemRecommendations' => $model->getProblemRecommendationRecap($filters)
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
    public function schools()
    {
        $keyword = trim((string)$this->request->getGet('q'));

        if ($keyword === '') {
            return $this->response->setJSON([
                'schools' => []
            ]);
        }

        return $this->response->setJSON([
            'schools' => $this->dashboardModel->searchSchools($keyword)
        ]);
    }
}