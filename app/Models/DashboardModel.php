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
                'problem'=>'Ketersediaan perangkat belum memenuhi kebutuhan.',
                'schools'=>[],
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

            if(
                $kebutuhanPerangkat>0 &&
                $totalPerangkat<$kebutuhanPerangkat
            ){
                $groups['device']['schools'][$schoolName]=[
                    'total'=>$totalPerangkat,
                    'need'=>$kebutuhanPerangkat
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

            if(empty($group['schools'])){
                continue;
            }

            $schoolNames=array_keys($group['schools']);

            $problem=$group['problem'];

            if($type==='device'){
                $details=[];

                foreach($group['schools'] as $school=>$value){
                    $shortage=$value['need']-$value['total'];

                    $details[]=$school.
                        ': Tersedia '.$value['total'].
                        ' unit dari kebutuhan '.$value['need'].
                        ' unit, kekurangan '.$shortage.' unit.';
                }

                $problem.=' '.implode(' ',$details);

                $recommendation='Memenuhi kekurangan perangkat komputer atau laptop sesuai kebutuhan pelaksanaan TKAP serta memastikan seluruh perangkat siap digunakan.';
            }

            elseif($type==='main_isp'){
                $details=[];

                foreach($group['schools'] as $school=>$value){
                    $shortage=round($value['need']-$value['bandwidth'],2);

                    $details[]=$school.
                        ': ISP utama '.$value['isp'].
                        ' memiliki bandwidth '.$value['bandwidth'].
                        ' Mbps dari kebutuhan '.$value['need'].
                        ' Mbps, kekurangan '.$shortage.' Mbps.';
                }

                $problem.=' '.implode(' ',$details);

                $recommendation='Meningkatkan kapasitas bandwidth ISP utama agar memenuhi kebutuhan minimum pelaksanaan TKAP dan menjaga kestabilan jaringan.';
            }

            elseif($type==='backup_bandwidth'){
                $details=[];

                foreach($group['schools'] as $school=>$value){
                    $shortage=round($value['need']-$value['bandwidth'],2);

                    $details[]=$school.
                        ': bandwidth ISP cadangan '.$value['bandwidth'].
                        ' Mbps dari kebutuhan '.$value['need'].
                        ' Mbps, kekurangan '.$shortage.' Mbps.';
                }

                $problem.=' '.implode(' ',$details);

                $recommendation=$group['recommendation'];
            }
            else{
                $recommendation=$group['recommendation'];
            }

            $data[]=[
                'no'=>$no++,
                'problem'=>$problem,
                'total_school'=>count($schoolNames),
                'schools'=>$schoolNames,
                'recommendation'=>$recommendation
            ];
        }

        return $data;
    }
    private function getVisitMetricsForProblemRecap($visitId)
    {
        $rows=$this->db->table('visit_answers')
            ->select('question_id,answer')
            ->where('visit_id',$visitId)
            ->get()
            ->getResultArray();

        $answers=[];

        foreach($rows as $row){
            $answers[(int)$row['question_id']]=trim((string)($row['answer']??''));
        }

        return [
            'total_perangkat'=>0,
            'kebutuhan_perangkat_juknis'=>0,
            'isp_utama'=>'',
            'isp_utama_lainnya'=>'',
            'bandwidth_isp_utama'=>0,
            'isp_cadangan'=>'',
            'isp_cadangan_lainnya'=>'',
            'bandwidth_isp_cadangan'=>0,
            'network_need'=>0
        ];
    }
    
}