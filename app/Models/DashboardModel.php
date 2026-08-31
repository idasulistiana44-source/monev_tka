<?php
namespace App\Models;
use CodeIgniter\Model;
class DashboardModel extends Model
{
    protected $DBGroup='default';

    private function baseVisitQuery($filters=[])
    {
        $builder=$this->db->table('visits v')
            ->join('schools s','s.id=v.school_id','inner')
            ->whereIn('v.status',['completed','verified']);
        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }
        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }
        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }
        return $builder;
    }

    public function getTotalSchools()
    {
        return $this->db->table('schools')->countAllResults();
    }

    public function getTotalOfficers()
    {
        return $this->db->table('users')
            ->where('role','petugas')
            ->where('is_active',1)
            ->countAllResults();
    }

    public function getTotalVisits()
    {
        return $this->db->table('visits')->countAllResults();
    }

    public function getVisitedSchools($filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        return $builder
            ->select('v.school_id')
            ->groupBy('v.school_id')
            ->countAllResults();
    }

    public function getDashboardSummary($filters=[])
    {
        $visited=$this->getVisitedSchools($filters);
        $totalVisits=$this->baseVisitQuery($filters)->countAllResults();
        $readiness=$this->getInfrastructureReadiness($filters);
        $totalReady=array_sum($readiness);
        $good=($readiness['Sangat Baik']??0)+($readiness['Baik']??0);
        $readinessPercent=$totalReady>0?round(($good/$totalReady)*100,1):0;
        return [
            'totalSchools'=>$visited,
            'visitedSchools'=>$visited,
            'totalVisits'=>$totalVisits,
            'readinessPercent'=>$readinessPercent
        ];
    }

    public function getVisitStatus()
    {
        $result=$this->db->table('visits')
            ->select('status,COUNT(*) AS total')
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

    public function getVisitsByLevel($filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $result=$builder
            ->select('s.level,COUNT(DISTINCT v.school_id) AS total')
            ->whereIn('s.level',['SMA','SMK','MA'])
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

    public function getInfrastructureReadiness($filters=[])
    {
        $builder=$this->db->table('visit_answers va')
            ->select('va.answer,COUNT(*) AS total')
            ->join('visits v','v.id=va.visit_id','inner')
            ->join('schools s','s.id=v.school_id','inner')
            ->where('va.question_id',18)
            ->whereIn('v.status',['completed','verified']);
        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }
        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }
        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }
        $result=$builder
            ->groupBy('va.answer')
            ->get()
            ->getResultArray();
        $data=[
            'Sangat Baik'=>0,
            'Baik'=>0,
            'Cukup'=>0,
            'Kurang Memadai'=>0
        ];
        foreach($result as $row){
            $answer=trim($row['answer']??'');
            if(isset($data[$answer])){
                $data[$answer]=(int)$row['total'];
            }
        }
        return $data;
    }

    public function getInfrastructureData($filters=[])
    {
        $questions=[
            1=>'INF-01',
            2=>'INF-02',
            3=>'INF-03',
            4=>'INF-04',
            5=>'INF-05',
            6=>'INF-06',
            7=>'INF-07',
            8=>'INF-08'
        ];
        $result=[];
        foreach($questions as $questionId=>$code){
            $builder=$this->baseVisitQuery($filters);
            $rows=$builder
                ->select('s.id,s.school_name,s.npsn,va.answer')
                ->join('visit_answers va','va.visit_id=v.id AND va.question_id='.$questionId,'left')
                ->orderBy('s.school_name','ASC')
                ->get()
                ->getResultArray();
            $data=[];
            foreach($rows as $row){
                $value=is_numeric($row['answer']??null)?(int)$row['answer']:0;
                $data[]=[
                    'school_id'=>(int)$row['id'],
                    'school_name'=>$row['school_name'],
                    'npsn'=>$row['npsn'],
                    'value'=>$value
                ];
            }
            $result[$code]=[
                'code'=>$code,
                'data'=>$data
            ];
        }
        return $result;
    }

    public function getElectricityData($filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $rows=$builder
            ->select('s.id,s.school_name,s.npsn,va.answer')
            ->join('visit_answers va','va.visit_id=v.id AND va.question_id=9','left')
            ->orderBy('s.school_name','ASC')
            ->get()
            ->getResultArray();
        $schools=[];
        $distribution=[];
        foreach($rows as $row){
            $value=trim($row['answer']??'');
            if($value===''){
                continue;
            }
            $schools[]=[
                'school_id'=>(int)$row['id'],
                'school_name'=>$row['school_name'],
                'npsn'=>$row['npsn'],
                'value'=>$value
            ];
            if(!isset($distribution[$value])){
                $distribution[$value]=0;
            }
            $distribution[$value]++;
        }
        uksort($distribution,function($a,$b){
            return $this->extractNumber($a)<=>$this->extractNumber($b);
        });
        return [
            'distribution'=>$distribution,
            'data'=>$schools
        ];
    }

    public function getInternetData($filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $rows=$builder
            ->select('s.id,s.school_name,s.npsn,va.answer')
            ->join('visit_answers va','va.visit_id=v.id AND va.question_id=10','left')
            ->orderBy('s.school_name','ASC')
            ->get()
            ->getResultArray();
        $schools=[];
        $distribution=[];
        foreach($rows as $row){
            $value=trim($row['answer']??'');
            if($value===''){
                continue;
            }
            $value=$this->normalizeInternet($value);
            $schools[]=[
                'school_id'=>(int)$row['id'],
                'school_name'=>$row['school_name'],
                'npsn'=>$row['npsn'],
                'value'=>$value
            ];
            if(!isset($distribution[$value])){
                $distribution[$value]=0;
            }
            $distribution[$value]++;
        }
        return [
            'distribution'=>$distribution,
            'data'=>$schools
        ];
    }

    public function getBandwidthData($questionId,$filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $rows=$builder
            ->select('s.id,s.school_name,s.npsn,va.answer')
            ->join('visit_answers va','va.visit_id=v.id AND va.question_id='.$questionId,'left')
            ->orderBy('s.school_name','ASC')
            ->get()
            ->getResultArray();
        $schools=[];
        $distribution=[];
        foreach($rows as $row){
            $value=trim($row['answer']??'');
            if($value===''){
                continue;
            }
            $label=$this->normalizeBandwidth($value);
            $schools[]=[
                'school_id'=>(int)$row['id'],
                'school_name'=>$row['school_name'],
                'npsn'=>$row['npsn'],
                'value'=>$label,
                'numeric_value'=>$this->extractNumber($label)
            ];
            if(!isset($distribution[$label])){
                $distribution[$label]=0;
            }
            $distribution[$label]++;
        }
        uksort($distribution,function($a,$b){
            return $this->extractNumber($a)<=>$this->extractNumber($b);
        });
        return [
            'distribution'=>$distribution,
            'data'=>$schools
        ];
    }

    public function getStudentData($filters=[])
    {
        $questions=[
            13=>'total',
            14=>'ikut',
            15=>'tidak_ikut'
        ];
        $data=[];
        foreach($questions as $questionId=>$field){
            $builder=$this->baseVisitQuery($filters);
            $rows=$builder
                ->select('s.id,s.school_name,s.npsn,va.answer')
                ->join('visit_answers va','va.visit_id=v.id AND va.question_id='.$questionId,'left')
                ->get()
                ->getResultArray();
            foreach($rows as $row){
                $id=(int)$row['id'];
                if(!isset($data[$id])){
                    $data[$id]=[
                        'school_id'=>$id,
                        'school_name'=>$row['school_name'],
                        'npsn'=>$row['npsn'],
                        'total'=>0,
                        'ikut'=>0,
                        'tidak_ikut'=>0
                    ];
                }
                $data[$id][$field]=(int)($row['answer']??0);
            }
        }
        foreach($data as &$row){
            $row['percentage']=$row['total']>0?round(($row['ikut']/$row['total'])*100,1):0;
        }
        return array_values($data);
    }

    public function getSessionData($filters=[])
    {
        return $this->getCategoricalSchoolData(16,$filters);
    }

    public function getWaveData($filters=[])
    {
        return $this->getCategoricalSchoolData(17,$filters);
    }

    private function getCategoricalSchoolData($questionId,$filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $rows=$builder
            ->select('s.id,s.school_name,s.npsn,va.answer')
            ->join('visit_answers va','va.visit_id=v.id AND va.question_id='.$questionId,'left')
            ->orderBy('s.school_name','ASC')
            ->get()
            ->getResultArray();
        $distribution=[];
        $data=[];
        foreach($rows as $row){
            $value=trim($row['answer']??'');
            if($value===''){
                continue;
            }
            $data[]=[
                'school_id'=>(int)$row['id'],
                'school_name'=>$row['school_name'],
                'npsn'=>$row['npsn'],
                'value'=>$value
            ];
            if(!isset($distribution[$value])){
                $distribution[$value]=0;
            }
            $distribution[$value]++;
        }
        return [
            'distribution'=>$distribution,
            'data'=>$data
        ];
    }

    public function getVisitsByRegion($filters=[])
    {
        $builder=$this->baseVisitQuery($filters);
        $result=$builder
            ->select('r.region_code,COUNT(DISTINCT v.school_id) AS total')
            ->join('region r','r.id=s.region_id','inner')
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

    private function normalizeInternet($value)
    {
        $value=strtoupper(trim($value));
        $value=str_replace([' ','-','_'],'',$value);
        if(strpos($value,'LAN')!==false && strpos($value,'WIFI')!==false){
            return 'LAN + WiFi';
        }
        if(strpos($value,'WIFI')!==false){
            return 'WiFi';
        }
        if(strpos($value,'LAN')!==false){
            return 'LAN';
        }
        return trim($value);
    }

    private function normalizeBandwidth($value)
    {
        $number=$this->extractNumber($value);
        if($number<=0){
            return trim($value);
        }
        return $number.' Mbps';
    }

    private function extractNumber($value)
    {
        if(preg_match('/[\d,.]+/',(string)$value,$match)){
            return (float)str_replace(',','.',$match[0]);
        }
        return 0;
    }
}