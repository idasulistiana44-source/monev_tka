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
        ini_set('memory_limit','512M');
        set_time_limit(300);

        $filters=[
            'start_date'=>$this->request->getGet('start_date'),
            'end_date'=>$this->request->getGet('end_date'),
            'level'=>$this->request->getGet('level'),
            'region_id'=>$this->request->getGet('region_id'),
            'district_id'=>$this->request->getGet('district_id'),
        ];

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

     $data=[
        'title'=>'Laporan Monitoring & Evaluasi TKAP',
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
        'studentReadiness'=>$model->getStudentReadinessReportData($filters),
        'session'=>$model->getSessionReportData($filters),
        'wave'=>$model->getWaveReportData($filters),
        'infrastructureReadiness'=>$model->getInfrastructureReadinessReportData($filters),
        'backupInternetSchools'=>$backupInternetSchools,
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
        $filters = [
            'start_date'  => $this->request->getGet('start_date'),
            'end_date'    => $this->request->getGet('end_date'),
            'level'       => $this->request->getGet('level'),
            'region_id'   => $this->request->getGet('region_id'),
            'district_id' => $this->request->getGet('district_id'),
        ];

        $model = $this->dashboardModel;

        $electricity = [];
        try {
            $electricity = $model->getElectricityRecap($filters);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengambil data daya listrik: ' . $e->getMessage());
            $electricity = [];
        }

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