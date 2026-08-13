<?php
namespace App\Models;
use CodeIgniter\Model;
class DashboardModel extends Model
{
    protected $DBGroup='default';
    public function getTotalSchools()
    {
        return $this->db->table('schools')->countAllResults();
    }
    public function getTotalOfficers()
    {
        return $this->db->table('users')->where('role','petugas')->where('is_active',1)->countAllResults();
    }
    public function getTotalVisits()
    {
        return $this->db->table('visits')->countAllResults();
    }
    public function getVisitedSchools()
    {
        $result=$this->db->table('visits')->select('school_id')->groupBy('school_id')->get()->getResult();
        return count($result);
    }
    public function getVisitStatus()
    {
        $result=$this->db->table('visits')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->getResultArray();
        $data=[
            'draft'=>0,
            'berlangsung'=>0,
            'selesai'=>0
        ];
        foreach($result as $row){
            if(isset($data[$row['status']])){
                $data[$row['status']]=(int)$row['total'];
            }
        }
        return $data;
    }
    public function getInfrastructureReadiness()
    {
        $result=$this->db->table('visit_answers')->select('answer, COUNT(*) AS total')->where('question_id',18)->groupBy('answer')->get()->getResultArray();
        $data=[
            'Sangat Baik'=>0,
            'Baik'=>0,
            'Cukup'=>0,
            'Kurang Memadai'=>0
        ];
        foreach($result as $row){
            if(isset($data[$row['answer']])){
                $data[$row['answer']]=(int)$row['total'];
            }
        }
        return $data;
    }
    public function getVisitsPerMonth()
    {
        $year=date('Y');
        $result=$this->db->table('visits')->select('MONTH(visit_date) AS bulan, COUNT(*) AS total')->where('YEAR(visit_date)',$year)->groupBy('MONTH(visit_date)')->orderBy('MONTH(visit_date)','ASC')->get()->getResultArray();
        $data=array_fill(1,12,0);
        foreach($result as $row){
            $data[(int)$row['bulan']]=(int)$row['total'];
        }
        return array_values($data);
    }
    public function getVisitsByLevel()
    {
        $result=$this->db->table('visits v')
            ->select('s.level, COUNT(v.id) AS total')
            ->join('schools s','s.id=v.school_id','left')
            ->whereIn('s.level',['SMA','SMK','SLB','MA'])
            ->groupBy('s.level')
            ->get()
            ->getResultArray();
        $data=[
            'SMA'=>0,
            'SMK'=>0,
            'SLB'=>0,
            'MA'=>0
        ];
        foreach($result as $row){
            $level=strtoupper(trim($row['level']??''));
            if(isset($data[$level])){
                $data[$level]=(int)$row['total'];
            }
        }
        return $data;
    }
    public function getRecentVisits($limit=10)
    {
        return $this->db->table('visits v')
            ->select('v.id,v.visit_date,v.status,s.npsn,s.school_name,d.name AS district_name,c.name AS city_name,r.name AS region_name')
            ->join('schools s','s.id=v.school_id','left')
            ->join('district d','d.id=s.district_id','left')
            ->join('city c','c.id=s.city_id','left')
            ->join('region r','r.id=s.region_id','left')
            ->orderBy('v.visit_date','DESC')
            ->orderBy('v.id','DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}