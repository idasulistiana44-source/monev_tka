<?php
namespace App\Controllers;
use App\Models\DashboardModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $model=new DashboardModel();
        $data=[
            'title'=>'Dashboard',
            'pageName'=>'dashboard/index',
            'pageView'=>'dashboard/index',
            'pageAsset'=>'dashboard',
            'pageData'=>[],
            'dashboardCss'=>true,
            'userCss'=>false,
            'schoolCss'=>false,
            'assignmentCss'=>false,
            'totalSchools'=>$model->getTotalSchools(),
            'visitedSchools'=>$model->getVisitedSchools(),
            'totalOfficers'=>$model->getTotalOfficers(),
            'totalVisits'=>$model->getTotalVisits(),
            'status'=>$model->getVisitStatus(),
            'readiness'=>$model->getInfrastructureReadiness(),
            'infrastructure'=>$model->getInfrastructureData(),
            'electricity'=>$model->getElectricityData(),
            'internet'=>$model->getInternetData(),
            'upload'=>$model->getBandwidthData(11),
            'download'=>$model->getBandwidthData(12),
            'students'=>$model->getStudentData(),
            'sessions'=>$model->getSessionData(),
            'waves'=>$model->getWaveData(),
            'visitsByRegion'=>$model->getVisitsByRegion(),
            'recentVisits'=>$model->getRecentVisits(),
            'visitsByLevel'=>$model->getVisitsByLevel()
        ];
        return view('layout/template',$data);
    }
    public function data()
    {
        $model=new DashboardModel();
        $startDate=$this->request->getGet('start_date');
        $endDate=$this->request->getGet('end_date');
        $level=$this->request->getGet('level');
        $districtId=$this->request->getGet('district_id');
        $filters=[
            'start_date'=>$startDate,
            'end_date'=>$endDate,
            'level'=>$level,
            'district_id'=>$districtId
        ];
        return $this->response->setJSON([
            'summary'=>$model->getDashboardSummary($filters),
            'infrastructure'=>$model->getInfrastructureData($filters),
            'electricity'=>$model->getElectricityData($filters),
            'internet'=>$model->getInternetData($filters),
            'upload'=>$model->getBandwidthData(11,$filters),
            'download'=>$model->getBandwidthData(12,$filters),
            'students'=>$model->getStudentData($filters),
            'sessions'=>$model->getSessionData($filters),
            'waves'=>$model->getWaveData($filters),
            'readiness'=>$model->getInfrastructureReadiness($filters)
        ]);
    }
}