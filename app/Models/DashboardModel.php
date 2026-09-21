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
        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }
        return $builder;
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
    
    public function getDraftSchools($filters=[])
    {
        $builder=$this->db->table('visits v')
            ->join('schools s','s.id=v.school_id','inner')
            ->where('v.status','DRAFT');

        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }

        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }

        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }

        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        return $builder->countAllResults();
    }

    public function getTotalSchools($filters=[])
    {
        $builder=$this->db->table('schools s')
            ->select('COUNT(*) AS total');

        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        $result=$builder->get()->getRowArray();

        return (int)($result['total']??0);
    }

   public function getInProgressSchools($filters=[])
    {
        $builder=$this->db->table('visits v')
            ->join('schools s','s.id=v.school_id','inner')
            ->where('v.status','in_progress');

        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }

        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }

        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        $result=$builder
            ->select('COUNT(DISTINCT v.school_id) AS total')
            ->get()
            ->getRowArray();

        return (int)($result['total']??0);
    }
    public function getVisitedSchools($filters=[])
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

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }
        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        $result=$builder
            ->select('COUNT(DISTINCT v.school_id) AS total')
            ->get()
            ->getRowArray();

        return (int)($result['total']??0);
    }
    

    public function getReadinessPercent($filters=[])
    {
        $readiness=$this->getInfrastructureReadiness($filters);

        $total=array_sum($readiness);

        if($total<=0){
            return 0;
        }

        $good=($readiness['Sangat Baik']??0)+
            ($readiness['Baik']??0);

        return round(($good/$total)*100,1);
    }

    public function getDashboardSummary($filters=[])
    {
        return [
            'totalSchools'=>$this->getTotalSchools(),
            'draftSchools'=>$this->getDraftSchools($filters),
            'inProgressSchools'=>$this->getInProgressSchools($filters),
            'visitedSchools'=>$this->getVisitedSchools($filters),
            'readinessPercent'=>$this->getReadinessPercent($filters)
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
        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
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

    public function getReadinessData($filters = [])
    {
        $builder = $this->db->table('visit_answers va')
            ->select('s.id, s.school_name, s.npsn, va.answer')
            ->join('visits v', 'v.id = va.visit_id', 'inner')
            ->join('schools s', 's.id = v.school_id', 'inner')
            ->where('va.question_id', 18)
            ->whereIn('v.status', ['completed', 'verified']);

        if (!empty($filters['start_date'])) {
            $builder->where('DATE(v.visit_date) >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('DATE(v.visit_date) <=', $filters['end_date']);
        }

        if (!empty($filters['level'])) {
            $builder->where('s.level', $filters['level']);
        }

        if (!empty($filters['district_id'])) {
            $builder->where('s.district_id', $filters['district_id']);
        }

        $rows = $builder
            ->orderBy('s.school_name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [];

        foreach ($rows as $row) {
            $value = trim($row['answer'] ?? '');

            if ($value === '') {
                continue;
            }

            // Hanya kategori kesiapan yang valid
            if (!in_array($value, [
                'Sangat Baik',
                'Baik',
                'Cukup',
                'Kurang Memadai'
            ], true)) {
                continue;
            }

            $data[] = [
                'school_id'   => (int) $row['id'],
                'school_name' => $row['school_name'],
                'npsn'        => $row['npsn'],
                'value'       => $value
            ];
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
    public function getRegions()
    {
        return $this->db->table('region')
            ->select('id,region_code,name')
            ->orderBy('name','ASC')
            ->get()
            ->getResultArray();
    }

    public function getDistricts($regionId=null)
    {
        $builder=$this->db->table('district')
            ->select('id,name,region_id');

        if(!empty($regionId)){
            $builder->where('region_id',$regionId);
        }

        return $builder
            ->orderBy('name','ASC')
            ->get()
            ->getResultArray();
    }
   
    
    public function getMonevStatusRecap($filters = [])
    {
        $builder = $this->db->table('visits v')
            ->join('schools s', 's.id = v.school_id', 'inner')
            ->join('region r', 'r.id = s.region_id', 'left')
            ->select("
                r.id AS region_id,
                r.name AS region_name,
                COUNT(DISTINCT CASE WHEN v.status IN ('COMPLETED','completed','verified') THEN v.school_id END) AS sudah_monev,
                COUNT(DISTINCT CASE WHEN v.status = 'in_progress' THEN v.school_id END) AS sedang_berlangsung,
                COUNT(DISTINCT CASE WHEN v.status IN ('DRAFT','draft') THEN v.school_id END) AS draft_monev
            ")
            ->groupBy('r.id, r.name')
            ->orderBy('r.name', 'ASC');

        if (!empty($filters['start_date'])) {
            $builder->where('v.visit_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('v.visit_date <=', $filters['end_date']);
        }

        if (!empty($filters['level'])) {
            $builder->where('s.level', $filters['level']);
        }

        if (!empty($filters['region_id'])) {
            $builder->where('s.region_id', $filters['region_id']);
        }

        if (!empty($filters['district_id'])) {
            $builder->where('s.district_id', $filters['district_id']);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $sudah = (int)$row['sudah_monev'];
            $berlangsung = (int)$row['sedang_berlangsung'];
            $draft = (int)$row['draft_monev'];

            $total = $sudah + $berlangsung + $draft;

            $row['sudah_monev'] = $sudah;
            $row['sedang_berlangsung'] = $berlangsung;
            $row['draft_monev'] = $draft;
            $row['persentase'] = $total > 0
                ? round(($sudah / $total) * 100, 1)
                : 0;
        }

        unset($row);

        return $rows;
    }
   public function getMonevOfficerRecap($filters=[])
    {
        $builder=$this->db->table('visit_team vt')
            ->join('visits v','v.id=vt.visit_id','inner')
            ->join('schools s','s.id=v.school_id','inner')
            ->join('users u','u.id=vt.user_id','inner')
            ->join('region r','r.id=s.region_id','left')
            ->select("
                u.id AS officer_id,
                u.name AS officer_name,
                r.name AS region_name,
                COUNT(DISTINCT v.school_id) AS jumlah_sasaran,
                COUNT(DISTINCT CASE WHEN LOWER(v.status) IN ('completed','verified') THEN v.school_id END) AS sudah_monev,
                COUNT(DISTINCT CASE WHEN LOWER(v.status)='in_progress' THEN v.school_id END) AS sedang_berlangsung,
                COUNT(DISTINCT CASE WHEN LOWER(v.status)='draft' THEN v.school_id END) AS belum_monev
            ")
            ->where('u.role','petugas');

        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }

        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }

        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }

        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        $builder->groupBy('u.id,u.name,r.id,r.name');
        $builder->orderBy('u.name','ASC');

        $rows=$builder->get()->getResultArray();

        $data=[];
        $total=[
            'jumlah_sasaran'=>0,
            'sudah_monev'=>0,
            'sedang_berlangsung'=>0,
            'belum_monev'=>0
        ];

        foreach($rows as $row){
            $sasaran=(int)$row['jumlah_sasaran'];
            $sudah=(int)$row['sudah_monev'];
            $berlangsung=(int)$row['sedang_berlangsung'];
            $belum=(int)$row['belum_monev'];

            if($berlangsung>0&&$belum>0){
                $keterangan=$berlangsung.' sedang berlangsung, '.$belum.' draft';
            }elseif($berlangsung>0){
                $keterangan=$berlangsung.' sedang berlangsung';
            }elseif($belum>0){
                $keterangan=$belum.' draft';
            }else{
                $keterangan='Selesai';
            }

            $data[]=[
                'officer_id'=>(int)$row['officer_id'],
                'officer_name'=>$row['officer_name']??'-',
                'region_name'=>$row['region_name']??'-',
                'jumlah_sasaran'=>$sasaran,
                'sudah_monev'=>$sudah,
                'sedang_berlangsung'=>$berlangsung,
                'belum_monev'=>$belum,
                'persentase'=>$sasaran>0?round(($sudah/$sasaran)*100,1):0,
                'keterangan'=>$keterangan
            ];

            $total['jumlah_sasaran']+=$sasaran;
            $total['sudah_monev']+=$sudah;
            $total['sedang_berlangsung']+=$berlangsung;
            $total['belum_monev']+=$belum;
        }

        $total['persentase']=$total['jumlah_sasaran']>0
            ?round(($total['sudah_monev']/$total['jumlah_sasaran'])*100,1)
            :0;

        return [
            'data'=>$data,
            'total'=>$total
        ];
    }

    public function getProblemRecommendationRecap($filters=[])
    {
        $builder=$this->db->table('visits v')
            ->join('schools s','s.id=v.school_id','inner')
            ->whereIn('v.status',['completed','verified'])
            ->select('v.id,v.school_id,s.school_name');

        if(!empty($filters['start_date'])){
            $builder->where('DATE(v.visit_date)>=',$filters['start_date']);
        }

        if(!empty($filters['end_date'])){
            $builder->where('DATE(v.visit_date)<=',$filters['end_date']);
        }

        if(!empty($filters['level'])){
            $builder->where('s.level',$filters['level']);
        }

        if(!empty($filters['region_id'])){
            $builder->where('s.region_id',$filters['region_id']);
        }

        if(!empty($filters['district_id'])){
            $builder->where('s.district_id',$filters['district_id']);
        }

        $visits=$builder->get()->getResultArray();

        $groups=[
            'device'=>[
                'problem'=>'Ketersediaan perangkat komputer/laptop belum memenuhi kebutuhan.',
                'schools'=>[],
                'details'=>[],
                'recommendation'=>''
            ],
            'main_isp'=>[
                'problem'=>'Kapasitas bandwidth ISP utama belum memenuhi kebutuhan.',
                'schools'=>[],
                'recommendation'=>''
            ],
            'backup_missing'=>[
                'problem'=>'Sekolah belum memiliki ISP atau jaringan internet cadangan. Konektivitas internet masih bergantung pada ISP utama sehingga berpotensi menghambat pelaksanaan TKAP apabila terjadi gangguan pada jaringan utama.',
                'schools'=>[],
                'recommendation'=>'Menyediakan ISP atau jaringan internet cadangan untuk meningkatkan keandalan dan kesinambungan koneksi selama pelaksanaan TKAP.'
            ],
            'backup_bandwidth'=>[
                'problem'=>'Bandwidth ISP cadangan belum memenuhi kebutuhan minimum.',
                'schools'=>[],
                'recommendation'=>'Meningkatkan kapasitas bandwidth ISP cadangan agar mampu memenuhi kebutuhan minimum jaringan berdasarkan Juknis.'
            ]
        ];

        foreach($visits as $visit){

            $metrics=$this->getVisitMetricsForProblemRecap($visit['id']);
                if(empty($metrics)){
                    continue;
                }

                $schoolName=$visit['school_name'];

                $totalPerangkat=(int)($metrics['total_perangkat']??0);
                $kebutuhanPerangkat=(int)($metrics['kebutuhan_perangkat_juknis']??0);

                $bandwidthIspUtama=(float)($metrics['bandwidth_isp_utama']??0);
                $bandwidthIspCadangan=(float)($metrics['bandwidth_isp_cadangan']??0);
                $networkNeed=(float)($metrics['network_need']??0);

                $ispCadangan=trim((string)($metrics['isp_cadangan']??''));

                $adaBackup=(
                    $ispCadangan!=='' &&
                    strcasecmp($ispCadangan,'Tidak Ada')!==0
                );

               $kebutuhanUtama=(int)($metrics['komputer_utama']??0);
                $kebutuhanCadangan=(int)($metrics['komputer_cadangan']??0);
                $totalKebutuhan=$kebutuhanUtama+$kebutuhanCadangan;

                if($totalKebutuhan>0 && $totalPerangkat<$totalKebutuhan){

                    $groups['device']['schools'][$schoolName]=[
                        'total'=>$totalPerangkat,
                        'need'=>$totalKebutuhan,
                        'peserta'=>(int)($metrics['siswa_ikut']??0),
                        'sesi'=>(int)($metrics['jumlah_sesi']??1),
                        'gelombang'=>(int)($metrics['jumlah_gelombang']??1),
                        'komputer_utama'=>$kebutuhanUtama,
                        'komputer_cadangan'=>$kebutuhanCadangan,
                        'total_kebutuhan'=>$totalKebutuhan
                    ];
                }

                if(
                    $networkNeed>0 &&
                    $bandwidthIspUtama<$networkNeed
                ){
                    $groups['main_isp']['schools'][$schoolName]=[
                        'bandwidth'=>$bandwidthIspUtama,
                        'need'=>$networkNeed,
                        'isp'=>$metrics['isp_utama']??'-'
                    ];
                }

                if(!$adaBackup){
                    $groups['backup_missing']['schools'][$schoolName]=true;
                }

                if(
                    $adaBackup &&
                    $networkNeed>0 &&
                    $bandwidthIspCadangan<$networkNeed
                ){
                    $groups['backup_bandwidth']['schools'][$schoolName]=[
                        'bandwidth'=>$bandwidthIspCadangan,
                        'need'=>$networkNeed
                    ];
                }
            }

            $data=[];
            $no=1;
            foreach($groups as $type=>$group){
                $schoolNames=array_keys($group['schools']);
                $totalSchool=count($schoolNames);

                $problem=$group['problem'];
                $recommendation=$group['recommendation'];
                $details=[];

                if($type==='device' && $totalSchool>0){

                        foreach($group['schools'] as $school=>$value){

                            $details[]=[
                                'school'=>$school,
                                'peserta'=>$value['peserta'],
                                'sesi'=>$value['sesi'],
                                'gelombang'=>$value['gelombang'],
                                'kebutuhan_utama'=>$value['komputer_utama'],
                                'kebutuhan_cadangan'=>$value['komputer_cadangan'],
                                'total_kebutuhan'=>$value['total_kebutuhan'],
                                'available'=>$value['total']
                            ];
                        }

                        $recommendation='Menambah atau menyiapkan perangkat komputer/laptop sesuai kebutuhan pelaksanaan TKAP serta memastikan seluruh perangkat siap digunakan.';
                    }

                elseif($type==='main_isp' && $totalSchool>0){
                    foreach($group['schools'] as $school=>$value){
                        $shortage=max(0,round($value['need']-$value['bandwidth'],2));

                        $details[]=[
                            'school'=>$school,
                            'isp'=>$value['isp']?:'-',
                            'bandwidth'=>$value['bandwidth'],
                            'need'=>$value['need'],
                            'shortage'=>$shortage
                        ];
                    }

                    $recommendation='Meningkatkan kapasitas bandwidth ISP utama agar memenuhi kebutuhan minimum pelaksanaan TKAP dan menjaga kestabilan jaringan.';
                }

                elseif($type==='backup_missing' && $totalSchool>0){
                    foreach($schoolNames as $school){
                        $details[]=[
                            'school'=>$school
                        ];
                    }

                    $recommendation=$group['recommendation'];
                }

                elseif($type==='backup_bandwidth' && $totalSchool>0){
                    foreach($group['schools'] as $school=>$value){
                        $shortage=max(0,round($value['need']-$value['bandwidth'],2));

                        $details[]=[
                            'school'=>$school,
                            'bandwidth'=>$value['bandwidth'],
                            'need'=>$value['need'],
                            'shortage'=>$shortage
                        ];
                    }

                    $recommendation=$group['recommendation'];
                }

                $data[]=[
                    'no'=>$no++,
                    'type'=>$type,
                    'problem'=>$problem,
                    'total_school'=>$totalSchool,
                    'recommendation'=>$totalSchool>0?$recommendation:'Tidak ada',
                    'details'=>$details
                ];
            }

            return $data;
        return $data;
    }

   private function getVisitMetricsForProblemRecap($visitId)
    {
        $rows=$this->db->table('visit_answers')
            ->select('question_id,answer')
            ->where('visit_id',$visitId)
            ->get()
            ->getResultArray();

        if(empty($rows)){
            return [];
        }

        $answers=[];

        foreach($rows as $row){
            $questionId=(int)($row['question_id']??0);
            $answer=trim((string)($row['answer']??''));

            if($questionId>0){
                $answers[$questionId]=$answer;
            }
        }

        // ==============================
        // PERANGKAT TERSEDIA
        // ==============================
        $pc=(int)($answers[1]??0);
        $laptopMilik=(int)($answers[2]??0);
        $laptopBukanMilik=(int)($answers[3]??0);

        $totalPerangkat=
            $pc+
            $laptopMilik+
            $laptopBukanMilik;

        // ==============================
        // PESERTA
        // ==============================
        $totalSiswa=(int)($answers[13]??0);
        $siswaIkut=(int)($answers[14]??0);

        // ==============================
        // SESI DAN GELOMBANG
        // ==============================
        $jumlahSesi=(int)($answers[16]??1);
        $jumlahGelombang=(int)($answers[17]??1);

        if($jumlahSesi<1)$jumlahSesi=1;
        if($jumlahGelombang<1)$jumlahGelombang=1;

        $komputerUtama=(int)ceil(
            $siswaIkut/($jumlahSesi*$jumlahGelombang)
        );

        $komputerCadangan=(int)ceil($komputerUtama*0.10);

        $kebutuhanPerangkat=$komputerUtama+$komputerCadangan;

        // ==============================
        // KEBUTUHAN PERANGKAT JUKNIS
        // Peserta / (Sesi x Gelombang)
        // ==============================
        $komputerUtama=ceil(
            $siswaIkut/($jumlahSesi*$jumlahGelombang)
        );

        // Cadangan 10%
        $komputerCadangan=ceil($komputerUtama*0.10);

        $kebutuhanPerangkat=
            $komputerUtama+
            $komputerCadangan;

        // ==============================
        // ISP UTAMA
        // ==============================
        $bandwidthIspUtama=$this->extractNumber(
            $answers[11]??0
        );

        $ispUtama=trim(
            (string)($answers[22]??'')
        );

        $ispUtamaLainnya=trim(
            (string)($answers[23]??'')
        );

        if(
            strcasecmp($ispUtama,'Lainnya')===0 &&
            $ispUtamaLainnya!==''
        ){
            $ispUtama=$ispUtamaLainnya;
        }

        // ==============================
        // ISP CADANGAN
        // ==============================
        $ispCadangan=trim(
            (string)($answers[12]??'')
        );

        $ispCadanganLainnya=trim(
            (string)($answers[24]??'')
        );

        if(
            strcasecmp($ispCadangan,'Lainnya')===0 &&
            $ispCadanganLainnya!==''
        ){
            $ispCadangan=$ispCadanganLainnya;
        }

        $bandwidthIspCadangan=$this->extractNumber(
            $answers[25]??0
        );

        // ==============================
        // BANDWIDTH
        // ==============================
        $networkClients=$komputerUtama;

        $networkNeed=$networkClients>0
            ? $networkClients*0.4
            : 0;

        // ==============================
        // RETURN
        // ==============================
        return [
            'pc'=>$pc,
            'laptop_milik'=>$laptopMilik,
            'laptop_bukan_milik'=>$laptopBukanMilik,
            'total_perangkat'=>$totalPerangkat,

            'total_siswa'=>$totalSiswa,
            'siswa_ikut'=>$siswaIkut,

            'jumlah_sesi'=>$jumlahSesi,
            'jumlah_gelombang'=>$jumlahGelombang,

            'komputer_utama'=>$komputerUtama,
            'komputer_cadangan'=>$komputerCadangan,
            'kebutuhan_perangkat_juknis'=>$kebutuhanPerangkat,

            'isp_utama'=>$ispUtama,
            'isp_utama_lainnya'=>$ispUtamaLainnya,
            'bandwidth_isp_utama'=>$bandwidthIspUtama,

            'isp_cadangan'=>$ispCadangan,
            'isp_cadangan_lainnya'=>$ispCadanganLainnya,
            'bandwidth_isp_cadangan'=>$bandwidthIspCadangan,

            'network_clients'=>$networkClients,
            'network_need'=>$networkNeed,
            'effective_bandwidth'=>$bandwidthIspUtama
        ];
    }
}