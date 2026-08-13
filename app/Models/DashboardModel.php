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
        return $this->db->table('visits')
            ->select('school_id')
            ->whereIn('status',['completed','verified'])
            ->groupBy('school_id')
            ->countAllResults();
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
            $status=strtolower(trim($row['status']??''));
            $total=(int)$row['total'];
            if($status==='draft'){
                $data['draft']=$total;
                $data['berlangsung']+=$total;
            }elseif($status==='in_progress'){
                $data['berlangsung']+=$total;
            }elseif($status==='completed'){
                $data['selesai']+=$total;
            }elseif($status==='verified'){
                $data['selesai']+=$total;
            }
        }
        return $data;
    }
    public function getVisitsByLevel()
    {
        $result=$this->db->table('visits v')
            ->select('s.level, COUNT(DISTINCT v.school_id) AS total')
            ->join('schools s','s.id=v.school_id','inner')
            ->whereIn('s.level',['SMA','SMK','MA'])
            ->whereIn('v.status',['completed','verified'])
            ->groupBy('s.level')
            ->get()
            ->getResultArray();
        $data=[
            'SMA'=>0,
            'SMK'=>0,
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
    public function getInfrastructureReadiness()
    {
        $result=$this->db->table('visit_answers')
            ->select('answer, COUNT(*) AS total')
            ->where('question_id',18)
            ->groupBy('answer')
            ->get()
            ->getResultArray();
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
    public function getVisitsByRegion()
    {
        $result=$this->db->table('visits v')
            ->select('r.region_code, COUNT(DISTINCT v.school_id) AS total')
            ->join('schools s','s.id=v.school_id','inner')
            ->join('region r','r.id=s.region_id','inner')
            ->whereIn('v.status',['completed','verified'])
            ->groupBy('r.id,r.region_code')
            ->orderBy('r.id','ASC')
            ->get()
            ->getResultArray();
        $data=[
            'JP1'=>0,
            'JP2'=>0,
            'JU1'=>0,
            'JU2'=>0,
            'JB1'=>0,
            'JB2'=>0,
            'JS1'=>0,
            'JS2'=>0,
            'JT1'=>0,
            'JT2'=>0,
            'KS'=>0
        ];
        foreach($result as $row){
            $code=strtoupper(trim($row['region_code']??''));
            if(isset($data[$code])){
                $data[$code]=(int)$row['total'];
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