<?php
namespace App\Models;
use CodeIgniter\Model;
class ReportsModel extends Model
{
    protected $table='visits';
    protected $primaryKey='id';
    protected $returnType='array';
    protected $allowedFields=['school_id','visit_date','officer_id','status','completed_at','created_by','submitted_by','created_at','updated_at'];
    protected $useTimestamps=true;
    protected $createdField='created_at';
    protected $updatedField='updated_at';
    protected $db;
    public function __construct()
    {
        parent::__construct();
        $this->db=\Config\Database::connect();
    }
    public function getRegions()
    {
        return $this->db->table('region')->select('id,name')->orderBy('name','ASC')->get()->getResultArray();
    }
    public function getReports($keyword='',$regionId='',$status='',$dateFrom='',$dateTo='',$userRole='',$userId=0)
    {
        $builder=$this->db->table('visits v');
        $builder->select('v.id,v.school_id,v.visit_date,v.status,v.created_at,v.updated_at,v.created_by,v.submitted_by,s.npsn,s.school_name,s.level,s.region_id,r.name AS region_name,creator.name AS created_by_name,submitter.name AS submitted_by_name');
        $builder->join('schools s','s.id=v.school_id','left');
        $builder->join('region r','r.id=s.region_id','left');
        $builder->join('users creator','creator.id=v.created_by','left');
        $builder->join('users submitter','submitter.id=v.submitted_by','left');
        if($userRole!=='admin'&&$userId>0){
            $builder->join('visit_team vt_filter','vt_filter.visit_id=v.id','inner');
            $builder->where('vt_filter.user_id',$userId);
        }
        if($keyword!==''){
            $builder->groupStart();
            $builder->like('s.school_name',$keyword);
            $builder->orLike('s.npsn',$keyword);
            $builder->orLike('r.name',$keyword);
            $builder->groupEnd();
        }
        if($regionId!==''){
            $builder->where('s.region_id',(int)$regionId);
        }
        if($status!==''){
            $builder->where('v.status',$status);
        }else{
            $builder->where('v.status','COMPLETED');
        }
        if($dateFrom!==''){
            $builder->where('v.visit_date >=',$dateFrom);
        }
        if($dateTo!==''){
            $builder->where('v.visit_date <=',$dateTo);
        }
        if($userRole!=='admin'&&$userId>0){
            $builder->groupBy('v.id');
        }
        $builder->orderBy('v.visit_date','DESC');
        $builder->orderBy('v.id','DESC');
        $rows=$builder->get()->getResultArray();
        if(empty($rows)){
            return [];
        }
        $visitIds=array_map('intval',array_column($rows,'id'));
        $teamRows=$this->db->table('visit_team vt')->select('vt.visit_id,vt.user_id,u.name')->join('users u','u.id=vt.user_id','left')->whereIn('vt.visit_id',$visitIds)->orderBy('u.name','ASC')->get()->getResultArray();
        $teams=[];
        foreach($teamRows as $team){
            $visitId=(int)$team['visit_id'];
            $teams[$visitId][]=['id'=>(int)$team['user_id'],'name'=>$team['name']??'Petugas'];
        }
        foreach($rows as &$row){
            $row['members']=$teams[(int)$row['id']]??[];
            $names=[];
            foreach($row['members'] as $member){
                $names[]=$member['name'];
            }
            $row['member_names']=implode(', ',$names);
        }
        return $rows;
    }
    public function getMembers($visitId)
    {
        return $this->db->table('visit_team vt')->select('vt.id,vt.user_id,u.name')->join('users u','u.id=vt.user_id','left')->where('vt.visit_id',(int)$visitId)->orderBy('u.name','ASC')->get()->getResultArray();
    }
    public function getReport($id,$userRole='',$userId=0)
    {
        $builder=$this->db->table('visits v');
        $builder->select('v.id,v.school_id,v.visit_date,v.status,v.created_at,v.updated_at,v.created_by,v.submitted_by,s.npsn,s.school_name,s.level,s.region_id,r.name AS region_name,creator.name AS created_by_name,submitter.name AS submitted_by_name');
        $builder->join('schools s','s.id=v.school_id','left');
        $builder->join('region r','r.id=s.region_id','left');
        $builder->join('users creator','creator.id=v.created_by','left');
        $builder->join('users submitter','submitter.id=v.submitted_by','left');
        $builder->where('v.id',(int)$id);
        $builder->where('v.status','COMPLETED');
        if($userRole!=='admin'&&$userId>0){
            $builder->join('visit_team vt_access','vt_access.visit_id=v.id','inner');
            $builder->where('vt_access.user_id',$userId);
        }
        $visit=$builder->get()->getRowArray();
        if(!$visit){
            return null;
        }
        $members=$this->getMembers($id);
        $answerRows=$this->db->table('visit_answers va')->select('va.question_id,va.answer,i.code,i.question,i.answer_type')->join('instruments i','i.id=va.question_id','left')->where('va.visit_id',(int)$id)->orderBy('i.sort_order','ASC')->get()->getResultArray();
        $answers=[];
        $answerMap=[];
        $documents=[];
        $photos=[];
        foreach($answerRows as $row){
            $code=strtoupper(trim((string)($row['code']??'')));
            $answer=$row['answer']??'';
            $item=[
                'question_id'=>(int)$row['question_id'],
                'code'=>$code,
                'question'=>$row['question']??'',
                'answer_type'=>$row['answer_type']??'',
                'answer'=>$answer
            ];
            $answers[]=$item;
            if($code!==''){
                $answerMap[$code]=$answer;
            }
            if($code==='UPM-01'){
                foreach($this->decodeUploadValues($answer) as $value){
                    $documents[]=['answer'=>$value];
                }
            }
            if($code==='UFD-01'){
                foreach($this->decodeUploadValues($answer) as $value){
                    $photos[]=['answer'=>$value];
                }
            }
        }
        $metrics=$this->buildMetrics($answerMap);
        return [
            'visit'=>$visit,
            'id'=>(int)$visit['id'],
            'school_id'=>(int)$visit['school_id'],
            'school_name'=>$visit['school_name']??'',
            'npsn'=>$visit['npsn']??'',
            'level'=>$visit['level']??'',
            'region_name'=>$visit['region_name']??'',
            'visit_date'=>$visit['visit_date']??'',
            'status'=>$visit['status']??'',
            'created_at'=>$visit['created_at']??'',
            'updated_at'=>$visit['updated_at']??'',
            'created_by_name'=>$visit['created_by_name']??'',
            'submitted_by_name'=>$visit['submitted_by_name']??'',
            'members'=>$members,
            'member_names'=>implode(', ',array_column($members,'name')),
            'answers'=>$answers,
            'answer_map'=>$answerMap,
            'metrics'=>$metrics,
            'template'=>$this->getReportTemplate(),
            'documents'=>$documents,
            'photos'=>$photos
        ];
    }
    public function getReportTemplate()
    {
        return $this->db->table('report_template_sections')->select('id,section_title,item_title,content,sort_order')->orderBy('sort_order','ASC')->orderBy('id','ASC')->get()->getResultArray();
    }
    protected function decodeUploadValues($value)
    {
        if(is_array($value)){
            return array_values(array_filter(array_map('trim',$value)));
        }
        $value=trim((string)$value);
        if($value===''){
            return [];
        }
        $decoded=json_decode($value,true);
        if(is_array($decoded)){
            $values=[];
            foreach($decoded as $item){
                if(is_string($item)){
                    $item=trim($item);
                    if($item!==''){
                        $values[]=$item;
                    }
                }elseif(is_array($item)){
                    foreach(['url','path','file','name'] as $key){
                        if(isset($item[$key])&&trim((string)$item[$key])!==''){
                            $values[]=trim((string)$item[$key]);
                            break;
                        }
                    }
                }
            }
            return array_values(array_unique($values));
        }
        return [$value];
    }
    protected function numberFrom($value)
    {
        if(is_numeric($value)){
            return (float)$value;
        }
        if(preg_match('/-?\d+(?:[.,]\d+)?/',(string)$value,$match)){
            return (float)str_replace(',','.',$match[0]);
        }
        return 0;
    }
    protected function buildMetrics(array $a)
    {
        $pc=(int)$this->numberFrom($a['INF-01']??0);
        $laptopMilik=(int)$this->numberFrom($a['INF-02']??0);
        $laptopBukan=(int)$this->numberFrom($a['INF-03']??0);
        $labkom=(int)$this->numberFrom($a['INF-04']??0);
        $ruang=(int)$this->numberFrom($a['INF-05']??0);
        $switch=(int)$this->numberFrom($a['INF-06']??0);
        $ups=(int)$this->numberFrom($a['INF-07']??0);
        $accessPoint=(int)$this->numberFrom($a['INF-08']??0);
        $daya=$a['INF-09']??'';
        $jaringan=$a['INF-10']??'';
        $upload=(float)$this->numberFrom($a['INF-11']??0);
        $download=(float)$this->numberFrom($a['INF-12']??0);
        $totalSiswa=(int)$this->numberFrom($a['KTA-01']??0);
        $ikut=(int)$this->numberFrom($a['KTA-02']??0);
        $tidakIkut=(int)$this->numberFrom($a['KTA-03']??0);
        $sesi=max(1,(int)$this->numberFrom($a['KTA-04']??1));
        $gelombang=$a['KTA-05']??'';
        $kesiapan=$a['KTA-06']??'';
        $catatan=trim((string)($a['CAT-01']??''));
        $totalPerangkat=$pc+$laptopMilik+$laptopBukan;
        $kebutuhanPerSesi=$sesi>0?ceil($totalSiswa/$sesi):$totalSiswa;
        if($totalPerangkat>=$kebutuhanPerSesi){
            $deviceStatus='Memadai';
        }else{
            $deviceStatus='Tidak Memadai';
        }
        $participantConsistent=($totalSiswa===($ikut+$tidakIkut));
        $participantPercentage=$totalSiswa>0?round(($ikut/$totalSiswa)*100,2):0;
        if(!$participantConsistent){
            $participantStatus='Perlu Verifikasi';
        }elseif($participantPercentage>=100){
            $participantStatus='Memadai';
        }elseif($participantPercentage>=90){
            $participantStatus='Perlu Perhatian';
        }else{
            $participantStatus='Tidak Memadai';
        }
        if($upload>=100&&$download>=100){
            $networkStatus='Memadai';
        }elseif($upload>=50&&$download>=50){
            $networkStatus='Perlu Perhatian';
        }else{
            $networkStatus='Tidak Memadai';
        }
        $electricityStatus=$ups>0&&trim((string)$daya)!==''?'Memadai':'Perlu Perhatian';
        $kesiapanUpper=strtoupper((string)$kesiapan);
        if(str_contains($kesiapanUpper,'TIDAK')){
            $kesiapanStatus='Tidak Memadai';
        }elseif(str_contains($kesiapanUpper,'PERLU')){
            $kesiapanStatus='Perlu Perhatian';
        }else{
            $kesiapanStatus='Memadai';
        }
        $findings=[];
        $recommendations=[];
        if($deviceStatus==='Tidak Memadai'){
            $findings[]='Ketersediaan perangkat belum memenuhi kebutuhan peserta per sesi.';
            $recommendations[]='Melakukan penambahan atau penataan perangkat agar kebutuhan peserta pada setiap sesi dapat terpenuhi.';
        }
        if($participantStatus==='Perlu Verifikasi'){
            $findings[]='Data jumlah siswa kelas 12, peserta TKA-P, dan siswa yang tidak mengikuti TKA belum konsisten.';
            $recommendations[]='Melakukan verifikasi dan pembaruan data peserta TKA-P.';
        }elseif($participantStatus==='Tidak Memadai'){
            $findings[]='Masih terdapat peserta kelas 12 yang belum mengikuti TKA-P.';
            $recommendations[]='Melakukan koordinasi untuk memastikan peserta yang belum mengikuti TKA-P mendapatkan tindak lanjut sesuai ketentuan.';
        }
        if($networkStatus==='Tidak Memadai'){
            $findings[]='Bandwidth jaringan belum memenuhi kebutuhan minimal monitoring internal.';
            $recommendations[]='Melakukan peningkatan kapasitas jaringan internet sebelum pelaksanaan TKA-P.';
        }elseif($networkStatus==='Perlu Perhatian'){
            $findings[]='Bandwidth jaringan perlu diperhatikan dan diuji kembali untuk memastikan kestabilan koneksi.';
            $recommendations[]='Melakukan pengujian kestabilan jaringan dan menyiapkan dukungan koneksi tambahan bila diperlukan.';
        }
        if($electricityStatus==='Perlu Perhatian'){
            $findings[]='Dukungan listrik atau UPS perlu dipastikan kembali untuk menjaga keberlangsungan pelaksanaan.';
            $recommendations[]='Memastikan daya listrik mencukupi dan UPS/perangkat pendukung berfungsi dengan baik.';
        }
        if($kesiapanStatus==='Tidak Memadai'){
            $findings[]='Sekolah belum dinyatakan siap secara infrastruktur berdasarkan hasil monitoring.';
            $recommendations[]='Melakukan tindak lanjut terhadap aspek infrastruktur yang belum siap sebelum pelaksanaan.';
        }elseif($kesiapanStatus==='Perlu Perhatian'){
            $findings[]='Terdapat aspek infrastruktur yang masih memerlukan perhatian sebelum pelaksanaan.';
            $recommendations[]='Melakukan pengecekan ulang terhadap aspek yang masih memerlukan perhatian.';
        }
        $catatanUpper=strtoupper($catatan);
        if((str_contains($catatanUpper,'GANGGUAN')||str_contains($catatanUpper,'PADAM')||str_contains($catatanUpper,'KURANG'))&&str_contains($catatanUpper,'LISTRIK')){
            $findings[]='Catatan visitasi menunjukkan adanya kondisi listrik yang perlu ditindaklanjuti.';
            $recommendations[]='Melakukan pengecekan instalasi dan kesiapan sumber listrik sebelum pelaksanaan.';
        }
        if(in_array('Tidak Memadai',[$deviceStatus,$participantStatus,$networkStatus,$kesiapanStatus],true)){
            $overallStatus='TIDAK MEMADAI';
        }elseif(in_array('Perlu Perhatian',[$participantStatus,$networkStatus,$electricityStatus,$kesiapanStatus],true)||$participantStatus==='Perlu Verifikasi'){
            $overallStatus='PERLU PERHATIAN';
        }else{
            $overallStatus='SANGAT BAIK';
        }
        if($overallStatus==='SANGAT BAIK'){
            $analysis='Berdasarkan hasil monitoring dan evaluasi, ketersediaan perangkat telah memenuhi kebutuhan peserta per sesi. Seluruh peserta telah dinyatakan mengikuti TKA. Jaringan internet berada dalam kondisi memadai. Dukungan listrik dan UPS berada dalam kondisi memadai. Secara keseluruhan satuan pendidikan menunjukkan kesiapan yang baik.';
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan dinyatakan siap mendukung pelaksanaan TKA. Ketersediaan sarana dan prasarana, perangkat, peserta, jaringan internet, serta dukungan listrik secara umum berada dalam kondisi yang mendukung pelaksanaan TKA.';
            $suggestions='Satuan pendidikan disarankan mempertahankan kondisi kesiapan yang telah tersedia dan melakukan pengecekan akhir terhadap perangkat, jaringan internet, kelistrikan, ruang pelaksanaan, serta data peserta sebelum pelaksanaan TKA.';
        }elseif($overallStatus==='PERLU PERHATIAN'){
            $analysis='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan secara umum telah memiliki komponen pendukung pelaksanaan TKA, namun masih terdapat beberapa aspek yang memerlukan perhatian dan pengecekan lebih lanjut.';
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan pada dasarnya telah memiliki kesiapan untuk mendukung pelaksanaan TKA, namun masih terdapat beberapa kondisi yang perlu ditindaklanjuti agar pelaksanaan TKA dapat berjalan secara optimal.';
            $suggestions='Satuan pendidikan disarankan segera menindaklanjuti aspek yang masih memerlukan perhatian, melakukan pengecekan ulang, serta memastikan seluruh komponen pendukung pelaksanaan TKA berada dalam kondisi siap sebelum hari pelaksanaan.';
        }else{
            $analysis='Berdasarkan hasil monitoring dan evaluasi, masih terdapat beberapa komponen pendukung pelaksanaan TKA yang belum memenuhi kondisi yang diperlukan sehingga memerlukan tindak lanjut sebelum pelaksanaan.';
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan belum sepenuhnya siap mendukung pelaksanaan TKA karena masih terdapat komponen yang perlu diperbaiki atau dipenuhi terlebih dahulu.';
            $suggestions='Satuan pendidikan perlu memprioritaskan penyelesaian seluruh temuan hasil monitoring, memastikan ketersediaan perangkat dan jaringan, kesiapan peserta, dukungan listrik, serta melakukan verifikasi ulang sebelum pelaksanaan TKA.';
        }
        return [
            'pc'=>$pc,
            'laptop_milik'=>$laptopMilik,
            'laptop_bukan_milik'=>$laptopBukan,
            'total_perangkat'=>$totalPerangkat,
            'labkom'=>$labkom,
            'ruang'=>$ruang,
            'switch'=>$switch,
            'ups'=>$ups,
            'access_point'=>$accessPoint,
            'daya'=>$daya,
            'jaringan'=>$jaringan,
            'upload'=>$upload,
            'download'=>$download,
            'total_siswa'=>$totalSiswa,
            'ikut'=>$ikut,
            'tidak_ikut'=>$tidakIkut,
            'sesi'=>$sesi,
            'gelombang'=>$gelombang,
            'kesiapan'=>$kesiapan,
            'catatan'=>$catatan,
            'kebutuhan_per_sesi'=>$kebutuhanPerSesi,
            'participant_percentage'=>$participantPercentage,
            'device_status'=>$deviceStatus,
            'participant_status'=>$participantStatus,
            'network_status'=>$networkStatus,
            'electricity_status'=>$electricityStatus,
            'kesiapan_status'=>$kesiapanStatus,
            'overall_status'=>$overallStatus,
            'findings'=>$findings,
            'recommendations'=>array_values($recommendations),
            'analysis'=>$analysis,
            'conclusion'=>$conclusion,
            'suggestions'=>$suggestions
        ];
    }
}