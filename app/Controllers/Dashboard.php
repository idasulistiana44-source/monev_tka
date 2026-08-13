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
            'visitsPerMonth'=>$model->getVisitsPerMonth(),
            'visitsByLevel'=>$model->getVisitsByLevel(),
            'recentVisits'=>$model->getRecentVisits()
        ];
        return view('layout/template',$data);
    }
}