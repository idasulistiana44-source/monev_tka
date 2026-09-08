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
        $teamRows=$this->db->table('visit_team vt')->select('vt.visit_id,vt.user_id,u.name,u.institution')->join('users u','u.id=vt.user_id','left')->whereIn('vt.visit_id',$visitIds)->orderBy('u.name','ASC')->get()->getResultArray();
        $teams=[];
        foreach($teamRows as $team){
            $visitId=(int)$team['visit_id'];
            $teams[$visitId][]=['id'=>(int)$team['user_id'],'name'=>$team['name']??'Petugas','institution'=>$team['institution']??'-'];
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
        return $this->db->table('visit_team vt')->select('vt.id,vt.user_id,u.name,u.institution')->join('users u','u.id=vt.user_id','left')->where('vt.visit_id',(int)$visitId)->orderBy('u.name','ASC')->get()->getResultArray();
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
        $metrics=$this->buildMetrics($answerMap,$visit['level']??'');
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
    protected function buildMetrics(array $a, $level = '')
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
        $gelombangText=trim((string)($a['KTA-05']??''));
        $kesiapan=$a['KTA-06']??'';
        $catatan=trim((string)($a['CAT-01']??''));

        /*
         * PENENTUAN SPESIFIKASI INFRASTRUKTUR SATUAN PENDIDIKAN
         *
         * Moda daring:
         * - SMA/MA/SMK/MAK formal: maksimal 1 komputer untuk 6 peserta
         *   (6 sesi berurutan).
         * - SD/MI/SMP/MTs formal: maksimal 1 komputer untuk 12 peserta
         *   (12 sesi berurutan).
         * - Komputer cadangan: 10% dari komputer yang dibutuhkan.
         *
         * Rumus operasional:
         * komputer utama = ceil(peserta / (sesi x gelombang))
         *
         * Jaringan daring:
         * - minimal 16 Mbps untuk 40 klien
         * - ekuivalen 0,4 Mbps/klien
         * - koneksi khusus untuk TKAP
         * - LAN CAT5E 100/1000 atau AP stabil maksimal 20 klien/AP
         *
         * Catatan:
         * Threshold "Sangat Baik/Baik/Cukup/Kurang Memadai"
         * adalah klasifikasi monitoring internal, bukan nilai threshold
         * tambahan yang tertulis sebagai angka di juknis.
         */

        $levelUpper=strtoupper((string)$level);

        $isSmaSmk=str_contains($levelUpper,'SMA')
            || str_contains($levelUpper,'SMK')
            || str_contains($levelUpper,'MAK')
            || str_contains($levelUpper,'MA');

        $isSdSmp=str_contains($levelUpper,'SD')
            || str_contains($levelUpper,'MI')
            || str_contains($levelUpper,'SMP')
            || str_contains($levelUpper,'MTS');

        $isNonFormal=str_contains($levelUpper,'PAKET')
            || str_contains($levelUpper,'NONFORMAL')
            || str_contains($levelUpper,'NON FORMAL');

        if($isNonFormal){
            $ratioPesertaPerKomputer=$isSmaSmk?3:6;
        }elseif($isSdSmp){
            $ratioPesertaPerKomputer=12;
        }else{
            // Default konservatif untuk SMA/SMK bila jenjang tidak terbaca.
            $ratioPesertaPerKomputer=6;
        }

        // Gelombang adalah nilai yang diinput pada instrumen KTA-05.
        // Ambil angka pertama dari jawaban, misalnya "2 Gelombang".
        $gelombangNumbers=[];
        if(preg_match('/\d+/', $gelombangText, $gelombangMatch)){
            $gelombang=(int)$gelombangMatch[0];
        }else{
            $gelombang=1;
        }
        $gelombang=max(1,$gelombang);

        // Sesuai pola perhitungan juknis:
        // kebutuhan komputer utama = jumlah peserta / (jumlah sesi x jumlah gelombang).
        $faktorPelaksanaan=max(1,$sesi*$gelombang);

        $kebutuhanPerSesi=(int)ceil(
            $totalSiswa/$faktorPelaksanaan
        );

        $komputerUtama=$kebutuhanPerSesi;

        // Cadangan perangkat = 10% dari kebutuhan komputer utama.
        $komputerCadangan=$komputerUtama>0
            ? (int)ceil($komputerUtama*0.10)
            : 0;

        $kebutuhanPerangkatJuknis=$komputerUtama+$komputerCadangan;

        $totalPerangkat=$pc+$laptopMilik+$laptopBukan;

        // Rasio peserta per komputer untuk kebutuhan penjelasan/report.
        $kapasitasPesertaPerKomputer = max(1, $faktorPelaksanaan);

        // Status perangkat berdasarkan pemenuhan kebutuhan utama + cadangan.
        if($totalPerangkat>=$kebutuhanPerangkatJuknis*1.10){
            $deviceStatus='Sangat Baik';
        }elseif($totalPerangkat>=$kebutuhanPerangkatJuknis){
            $deviceStatus='Baik';
        }elseif($totalPerangkat>=$komputerUtama){
            $deviceStatus='Cukup';
        }else{
            $deviceStatus='Kurang Memadai';
        }

        /*
         * Ruang TKAP:
         * maksimal 20 peserta per pengawas ruang.
         * Setiap ruang ditangani 1 proktor.
         * 1 ID proktor maksimal 40 komputer klien.
         */
        $pesertaPerSesi=$sesi>0
            ? (int)ceil($totalSiswa/$sesi)
            : $totalSiswa;

        $kebutuhanRuang=max(1,(int)ceil($pesertaPerSesi/20));
        $kebutuhanProktor=(int)ceil(max(1,$kebutuhanPerangkatJuknis)/40);
        $kebutuhanPengawas=$kebutuhanRuang;

        if($ruang>=$kebutuhanRuang){
            $roomStatus='Baik';
        }elseif($ruang>0){
            $roomStatus='Cukup';
        }else{
            $roomStatus='Kurang Memadai';
        }

        /*
         * Koneksi jaringan daring:
         * minimum 16 Mbps untuk 40 klien, ekuivalen 0,4 Mbps/klien.
         * Untuk penilaian kebutuhan aktual, tetap diberlakukan floor 16 Mbps.
         */
        $networkClients=max(1,$komputerUtama);
        $networkNeed=max(16,round($networkClients*0.4,2));
        $effectiveBandwidth=min($upload,$download);
        $networkRatio=$networkNeed>0
            ? round($effectiveBandwidth/$networkNeed,2)
            : 0;

        if($networkRatio>=1.50){
            $networkStatus='Sangat Baik';
        }elseif($networkRatio>=1.00){
            $networkStatus='Baik';
        }elseif($networkRatio>=0.75){
            $networkStatus='Cukup';
        }else{
            $networkStatus='Kurang Memadai';
        }

        $networkDedicated=trim((string)$jaringan)!=='';
        $apRequired=(int)ceil($networkClients/20);
        $apCoverage=$apRequired>0
            ? round(($accessPoint/$apRequired)*100,2)
            : 100;

        /*
         * Kelistrikan:
         * juknis menetapkan daya harus stabil dan cukup untuk seluruh perangkat,
         * tanpa memberikan angka watt minimum universal. Karena itu status
         * tidak dipaksakan dari satu angka watt tertentu.
         */
        $hasDaya=trim((string)$daya)!=='';
        if($hasDaya && $ups>0){
            $electricityStatus='Baik';
        }elseif($hasDaya){
            $electricityStatus='Cukup';
        }else{
            $electricityStatus='Kurang Memadai';
        }

        $participantConsistent=($totalSiswa===($ikut+$tidakIkut));
        $participantPercentage=$totalSiswa>0
            ? round(($ikut/$totalSiswa)*100,2)
            : 0;

        if(!$participantConsistent){
            $participantStatus='Kurang Memadai';
        }elseif($participantPercentage>=100){
            $participantStatus='Sangat Baik';
        }elseif($participantPercentage>=90){
            $participantStatus='Baik';
        }elseif($participantPercentage>0){
            $participantStatus='Cukup';
        }else{
            $participantStatus='Kurang Memadai';
        }

        // Kesiapan umum yang berasal dari jawaban instrumen.
        $kesiapanUpper=strtoupper((string)$kesiapan);
        if(str_contains($kesiapanUpper,'TIDAK')){
            $kesiapanStatus='Kurang Memadai';
        }elseif(str_contains($kesiapanUpper,'PERLU')){
            $kesiapanStatus='Cukup';
        }elseif($kesiapan!==''){
            $kesiapanStatus='Baik';
        }else{
            $kesiapanStatus='Cukup';
        }

        $findings=[];
        $recommendations=[];

        if($deviceStatus==='Kurang Memadai'){
            $findings[]='Jumlah komputer belum memenuhi kebutuhan utama sesuai jumlah peserta, sesi, gelombang, dan rasio penggunaan komputer berdasarkan juknis.';
            $recommendations[]='Menambah atau menata komputer hingga kebutuhan komputer utama dan cadangan 10% terpenuhi.';
        }elseif($deviceStatus==='Cukup'){
            $findings[]='Jumlah komputer utama telah mencukupi, namun cadangan 10% belum sepenuhnya terpenuhi.';
            $recommendations[]='Melengkapi komputer cadangan sekurang-kurangnya 10% dari kebutuhan komputer utama.';
        }

        if($roomStatus==='Kurang Memadai'){
            $findings[]='Jumlah ruang yang disiapkan belum memenuhi kebutuhan berdasarkan batas maksimal 20 peserta per pengawas ruang.';
            $recommendations[]='Menambah atau menata ruang pelaksanaan agar jumlah peserta per ruang sesuai ketentuan.';
        }elseif($roomStatus==='Cukup'){
            $findings[]='Ruang tersedia namun belum sepenuhnya memenuhi kebutuhan ideal berdasarkan jumlah peserta per sesi.';
            $recommendations[]='Melakukan penataan pembagian peserta dan ruang serta memastikan rasio pengawas maksimal 20 peserta per ruang.';
        }

        if($networkStatus==='Kurang Memadai'){
            $findings[]='Bandwidth efektif belum memenuhi kebutuhan minimal jaringan TKAP untuk jumlah klien yang dilayani.';
            $recommendations[]='MeningkaTKAPn kapasitas bandwidth hingga sekurang-kurangnya memenuhi kebutuhan minimal hasil perhitungan dan memastikan koneksi khusus untuk TKAP.';
        }elseif($networkStatus==='Cukup'){
            $findings[]='Bandwidth telah mendekati kebutuhan minimal dan masih memiliki cadangan kapasitas yang terbatas.';
            $recommendations[]='Melakukan uji kestabilan jaringan kembali dan memastikan tidak ada penggunaan jaringan lain selama pelaksanaan TKAP.';
        }

        if(!$networkDedicated){
            $findings[]='Informasi mengenai jaringan khusus pelaksanaan TKAP belum tercatat pada hasil monitoring.';
            $recommendations[]='Memastikan koneksi jaringan yang digunakan untuk TKAP dikhususkan selama pelaksanaan.';
        }

        if($accessPoint>0 && $accessPoint<$apRequired){
            $findings[]='Jumlah Access Point yang tersedia belum mencapai kebutuhan berdasarkan batas maksimal 20 klien per Access Point.';
            $recommendations[]='Menambah atau menata Access Point agar akses stabil dapat melayani jumlah klien sesuai kebutuhan.';
        }

        if($electricityStatus==='Kurang Memadai'){
            $findings[]='Data daya listrik belum menunjukkan kepastian bahwa sumber listrik tersedia dan mencukupi untuk seluruh perangkat.';
            $recommendations[]='Memastikan daya listrik stabil dan cukup untuk seluruh komputer serta perangkat jaringan sebelum pelaksanaan.';
        }elseif($electricityStatus==='Cukup'){
            $findings[]='Daya listrik tercatat, namun dukungan UPS belum tercatat.';
            $recommendations[]='Memastikan ketersediaan dan fungsi UPS/perangkat pendukung untuk mengantisipasi gangguan listrik.';
        }

        if($participantStatus==='Kurang Memadai'){
            $findings[]='Data peserta yang dicatat belum konsisten atau belum seluruhnya menunjukkan kesiapan mengikuti TKAP.';
            $recommendations[]='Melakukan verifikasi dan pembaruan data peserta sebelum pelaksanaan.';
        }elseif($participantStatus==='Cukup'){
            $findings[]='Masih terdapat peserta yang belum seluruhnya tercatat mengikuti TKAP.';
            $recommendations[]='Melakukan verifikasi peserta dan memastikan kesiapan seluruh peserta.';
        }

        $catatanUpper=strtoupper($catatan);
        if(
            (str_contains($catatanUpper,'GANGGUAN')
            ||str_contains($catatanUpper,'PADAM')
            ||str_contains($catatanUpper,'KURANG'))
            &&str_contains($catatanUpper,'LISTRIK')
        ){
            $findings[]='Catatan visitasi menunjukkan adanya kondisi kelistrikan yang perlu ditindaklanjuti.';
            $recommendations[]='Melakukan pengecekan instalasi dan kesiapan sumber listrik sebelum pelaksanaan.';
        }

        $statusScores=[
            'Sangat Baik'=>4,
            'Baik'=>3,
            'Cukup'=>2,
            'Kurang Memadai'=>1
        ];

        $componentStatuses=[
            $deviceStatus,
            $participantStatus,
            $networkStatus,
            $roomStatus,
            $electricityStatus,
            $kesiapanStatus
        ];

        $lowestScore=4;
        foreach($componentStatuses as $componentStatus){
            $lowestScore=min($lowestScore,$statusScores[$componentStatus]??1);
        }

        $averageScore=count($componentStatuses)>0
            ? array_sum(array_map(
                static function($status) use ($statusScores){
                    return $statusScores[$status]??1;
                },
                $componentStatuses
            ))/count($componentStatuses)
            : 1;

        if($lowestScore===1){
            $overallStatus='Kurang Memadai';
        }elseif($averageScore>=3.50){
            $overallStatus='Sangat Baik';
        }elseif($averageScore>=2.50){
            $overallStatus='Baik';
        }else{
            $overallStatus='Cukup';
        }

        $analysis='Hasil monitoring dinilai berdasarkan kesesuaian sarana dan prasarana dengan spesifikasi infrastruktur TKAP, meliputi kebutuhan komputer, ruang, jaringan internet, perangkat jaringan, kelistrikan, dan kesiapan peserta. Perhitungan komputer menggunakan jumlah peserta, sesi, gelombang, rasio maksimal penggunaan komputer, serta cadangan 10% sesuai ketentuan. Kebutuhan bandwidth dihitung berdasarkan minimal 16 Mbps untuk 40 klien atau ekuivalen 0,4 Mbps per klien.';

        if($overallStatus==='Sangat Baik'){
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan menunjukkan kesiapan infrastruktur yang sangat baik dan secara umum telah memenuhi kebutuhan pelaksanaan TKAP sesuai parameter yang dimonitor.';
            $suggestions='Mempertahankan kondisi kesiapan serta melakukan pengecekan akhir terhadap komputer, ruang, jaringan internet, kelistrikan, dan data peserta sebelum pelaksanaan TKAP.';
        }elseif($overallStatus==='Baik'){
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan menunjukkan kesiapan yang baik dan telah memenuhi sebagian besar kebutuhan infrastruktur pelaksanaan TKAP.';
            $suggestions='Melakukan pengecekan akhir dan menindaklanjuti aspek yang masih perlu diperkuat agar kesiapan pelaksanaan TKAP tetap optimal.';
        }elseif($overallStatus==='Cukup'){
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, satuan pendidikan telah memiliki sebagian besar komponen pendukung pelaksanaan TKAP, namun masih terdapat aspek yang perlu diperhatikan dan ditindaklanjuti.';
            $suggestions='Segera menindaklanjuti temuan terutama terkait kecukupan perangkat, kapasitas jaringan, ruang, serta dukungan kelistrikan sebelum pelaksanaan TKAP.';
        }else{
            $conclusion='Berdasarkan hasil monitoring dan evaluasi, masih terdapat komponen infrastruktur yang belum memenuhi kebutuhan pelaksanaan TKAP sehingga diperlukan tindak lanjut sebelum pelaksanaan.';
            $suggestions='Memprioritaskan pemenuhan kebutuhan komputer, jaringan, ruang, dan kelistrikan serta melakukan verifikasi ulang sebelum pelaksanaan TKAP.';
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
            'gelombang_text'=>$gelombangText,
            'kesiapan'=>$kesiapan,
            'catatan'=>$catatan,

            'kebutuhan_per_sesi'=>$kebutuhanPerSesi,
            'rasio_peserta_per_komputer'=>$kapasitasPesertaPerKomputer,
            'rasio_maksimal_juknis'=>$ratioPesertaPerKomputer,
            'kapasitas_peserta_per_komputer'=>$kapasitasPesertaPerKomputer,
            'komputer_utama'=>$komputerUtama,
            'komputer_cadangan'=>$komputerCadangan,
            'kebutuhan_perangkat_juknis'=>$kebutuhanPerangkatJuknis,

            'kebutuhan_ruang'=>$kebutuhanRuang,
            'kebutuhan_proktor'=>$kebutuhanProktor,
            'kebutuhan_pengawas'=>$kebutuhanPengawas,
            'room_status'=>$roomStatus,

            'network_clients'=>$networkClients,
            'network_need'=>$networkNeed,
            'effective_bandwidth'=>$effectiveBandwidth,
            'network_ratio'=>$networkRatio,
            'ap_required'=>$apRequired,
            'ap_coverage'=>$apCoverage,
            'network_dedicated'=>$networkDedicated,

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