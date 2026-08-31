<?php
namespace App\Controllers;

use App\Models\VisitModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Visits extends BaseController
{
    protected $visitModel;
    protected $db;

    public function __construct()
    {
        $this->visitModel = new VisitModel();
        $this->db         = \Config\Database::connect();
    }

    public function index()
    {
        return view('layout/template', [
            'title'     => 'Kegiatan Monev',
            'pageView'  => 'visits/index',
            'pageAsset' => 'visits',
            'pageCss'   => 'visits',
            'pageData'  => []
        ]);
    }

    public function data()
    {
        try {
            $keyword  = trim((string)$this->request->getGet('keyword'));
            $status   = trim((string)$this->request->getGet('status'));
            
            $userRole = strtolower((string)session()->get('role'));
            $userId   = (int)session()->get('user_id');

            if ($userRole === 'petugas') {
                $data = $this->visitModel->getList($keyword, $status, $userId);
            } else {
                $data = $this->visitModel->getList($keyword, $status);
            }

            return $this->response->setJSON([
                'status' => true,
                'data'   => $data
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VISITS DATA ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function schools()
{
    try {
        $regionId=(int)$this->request->getGet('region_id');
        $editVisitId=(int)$this->request->getGet('edit_visit_id');

        $builder=$this->db->table('schools')
            ->select('id,npsn,school_name,level,city_id,district_id,region_id');

        if($regionId>0 && $regionId!==10){
            $builder->where('region_id',$regionId);
        }

        $existingVisits=$this->db->table('visits')
            ->select('school_id')
            ->where('id !=',$editVisitId)
            ->get()
            ->getResultArray();

        $usedSchoolIds=array_filter(array_column($existingVisits,'school_id'));

        if(!empty($usedSchoolIds)){
            $builder->whereNotIn('id',$usedSchoolIds);
        }

        $rows=$builder
            ->orderBy('school_name','ASC')
            ->get()
            ->getResultArray();

        foreach($rows as &$row){
            $row['name']=$row['school_name'];
        }
        unset($row);

        return $this->response->setJSON([
            'status'=>true,
            'data'=>$rows,
            'csrfHash'=>csrf_hash()
        ]);
    }catch(\Throwable $e){
        log_message('error','VISITS SCHOOLS ERROR: '.$e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,
            'message'=>'Gagal mengambil data sekolah: '.$e->getMessage(),
            'csrfHash'=>csrf_hash()
        ]);
    }
}

   public function officers()
    {
        try {
            $rows=$this->db->table('users')
                ->select('id,name')
                ->where('role','petugas')
                ->where('is_active',1)
                ->orderBy('name','ASC')
                ->get()
                ->getResultArray();
            return $this->response->setJSON([
                'status'=>true,
                'data'=>$rows,
                'csrfHash'=>csrf_hash()
            ]);
        } catch(\Throwable $e){
            log_message('error','VISITS OFFICERS ERROR: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>'Gagal mengambil data petugas.',
                'csrfHash'=>csrf_hash()
            ]);
        }
    }

    public function create()
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(400)->setJSON([
                'status'=>false,
                'message'=>'Request tidak valid.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        $regionId=(int)$this->request->getPost('region_id');
        $schoolId=(int)$this->request->getPost('school_id');
        $visitDate=trim((string)$this->request->getPost('visit_date'));
        $userIds=$this->request->getPost('user_ids')??$this->request->getPost('user_ids[]');
        
        if(empty($userIds)){
            $json=$this->request->getJSON(true);
            $userIds=$json['user_ids']??[];
        }
        if(!is_array($userIds)){
            $userIds=$userIds?[$userIds]:[];
        }
        $userIds=array_values(array_unique(array_filter(array_map('intval',$userIds))));
        if($regionId<=0){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Wilayah wajib dipilih.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        if($schoolId<=0){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Sekolah wajib dipilih.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        $school=$this->db->table('schools')
            ->select('id,npsn,school_name,level,region_id')
            ->where('id',$schoolId)
            ->get()
            ->getRowArray();
        if(!$school){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Sekolah tidak ditemukan.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        if($regionId!==10&&(int)$school['region_id']!==$regionId){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Sekolah yang dipilih tidak berada pada wilayah yang dipilih.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        if($visitDate===''){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Tanggal Monev wajib diisi.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        if(!$this->isValidDate($visitDate)){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Format tanggal Monev tidak valid.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        if(count($userIds)<1){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Minimal satu petugas harus dipilih.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        $validUsers=$this->db->table('users')
            ->select('id')
            ->whereIn('id',$userIds)
            ->where('role','petugas')
            ->where('is_active',1)
            ->get()
            ->getResultArray();
        $validUserIds=array_map('intval',array_column($validUsers,'id'));
        sort($validUserIds);
        $checkUserIds=$userIds;
        sort($checkUserIds);
        if($validUserIds!==$checkUserIds){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'Ada petugas yang tidak valid, tidak aktif, atau bukan petugas.',
                'csrfHash'=>csrf_hash()
            ]);
        }
        try{
            $visitId=$this->visitModel->createVisit($schoolId,$visitDate,$userIds);
            return $this->response->setJSON([
                'status'=>true,
                'message'=>'Kegiatan Monev berhasil dibuat.',
                'id'=>$visitId,
                'redirect'=>site_url('visits/form/'.$visitId),
                'csrfHash'=>csrf_hash()
            ]);
        }catch(\Throwable $e){
            log_message('error','VISITS CREATE ERROR: '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>$e->getMessage(),
                'csrfHash'=>csrf_hash()
            ]);
        }
    }

    public function delete($id)
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(400)->setJSON([
                'status'=>false,
                'message'=>'Request tidak valid.'
            ]);
        }

        $id=(int)$id;

        if($id<=0){
            return $this->response->setJSON([
                'status'=>false,
                'message'=>'ID kegiatan Monev tidak valid.'
            ]);
        }

        try{
            $userRole=strtolower((string)session()->get('role'));

            if($userRole!=='admin'){
                return $this->response->setStatusCode(403)->setJSON([
                    'status'=>false,
                    'message'=>'Anda tidak memiliki hak untuk menghapus kegiatan Monev.'
                ]);
            }

            $visit=$this->visitModel->find($id);

            if(!$visit){
                return $this->response->setJSON([
                    'status'=>false,
                    'message'=>'Kegiatan Monev tidak ditemukan.'
                ]);
            }

            $this->visitModel->deleteVisit($id);

            return $this->response->setJSON([
                'status'=>true,
                'message'=>'Kegiatan Monev beserta seluruh data terkait berhasil dihapus.'
            ]);

        }catch(\Throwable $e){

            log_message(
                'error',
                'VISITS DELETE ERROR: '.$e->getMessage()
            );

            return $this->response->setStatusCode(500)->setJSON([
                'status'=>false,
                'message'=>$e->getMessage()
            ]);
        }
    }

    public function form($id)
    {
        $id = (int)$id;

        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound('Kegiatan Monev tidak ditemukan.');
        }

        $visit = $this->visitModel->getDetail($id);

        if (!$visit) {
            throw PageNotFoundException::forPageNotFound('Kegiatan Monev tidak ditemukan.');
        }

        // Cek Keamanan Akses
        if (!$this->isUserAuthorizedForVisit($visit)) {
            throw PageNotFoundException::forPageNotFound('Anda tidak berhak mengakses kegiatan Monev ini.');
        }

        return view('layout/template', [
            'title'     => 'Mulai Monev',
            'pageView'  => 'visits/form',
            'pageAsset' => 'visits-form',
            'pageCss'   => 'visits-form',
            'pageData'  => [
                'visit' => $visit
            ]
        ]);
    }

    public function instruments($id)
    {
        try {
            $id = (int)$id;

            if ($id <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => false,
                    'message' => 'ID kegiatan Monev tidak valid.'
                ]);
            }

            $visit = $this->visitModel->getDetail($id);

            if (!$visit) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.'
                ]);
            }

            // Cek Keamanan Akses
            if (!$this->isUserAuthorizedForVisit($visit)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => false,
                    'message' => 'Anda tidak berhak mengakses instrumen kegiatan ini.'
                ]);
            }

            $sections = $this->visitModel->getInstrumentData($id);

            return $this->response->setJSON([
                'status' => true,
                'visit'  => [
                    'id'     => $visit['id'],
                    'status' => $visit['status']
                ],
                'sections' => $sections
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'INSTRUMENT ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function instrumentData($id)
    {
        return $this->instruments($id);
    }

    public function start($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Request tidak valid.'
            ]);
        }

        try {
            $id    = (int)$id;
            $visit = $this->visitModel->getDetail($id);

            if (!$visit) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.'
                ]);
            }

            // Cek Keamanan Akses
            if (!$this->isUserAuthorizedForVisit($visit)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => false,
                    'message' => 'Anda tidak berhak memulai kegiatan Monev ini.'
                ]);
            }

            if (($visit['status'] ?? '') === 'COMPLETED') {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev sudah selesai.'
                ]);
            }

            if (($visit['status'] ?? '') === 'DRAFT') {
                $this->visitModel->updateStatus($id, 'IN_PROGRESS');
            }

            return $this->response->setJSON([
                'status'   => true,
                'message'  => 'Monev dimulai.',
                'redirect' => site_url('visits/form/' . $id)
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VISITS START ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }

  public function complete($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'ID Visit tidak valid.',
                    'csrf_hash' => csrf_hash()
                ]);
            }
            $visit = $this->visitModel->getDetail($id);
            if (!$visit) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.',
                    'csrf_hash' => csrf_hash()
                ]);
            }
            if (!$this->isUserAuthorizedForVisit($visit)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => false,
                    'message' => 'Anda tidak berhak menyelesaikan kegiatan Monev ini.',
                    'csrf_hash' => csrf_hash()
                ]);
            }
            $result = $this->visitModel->completeVisit((int) $id);
            log_message('error', 'COMPLETE RESULT: ' . json_encode($result));
            if (!$result['status']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => $result['message'] ?? 'Kegiatan Monev belum dapat diselesaikan.',
                    'missing' => $result['missing'] ?? [],
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return $this->response->setJSON([
                'status' => true,
                'message' => $result['message'] ?? 'Kegiatan Monev berhasil diselesaikan.',
                'visit_status' => 'COMPLETED',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Complete Monev: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error Server: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    public function saveAnswers($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'ID Visitasi tidak valid.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $visit = $this->visitModel->getDetail($id);

            if (!$visit) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $answers = $this->request->getPost('answers');

            if (!is_array($answers)) {
                $answers = [];
            }

            $uploadedFiles = [];
            $oldFilesToDelete = [];

            $files = $this->request->getFiles();

            if (!empty($files['files']) && is_array($files['files'])) {

                foreach ($files['files'] as $questionId => $file) {

                    $questionId = (int) $questionId;

                    if ($questionId <= 0) {
                        continue;
                    }

                    /*
                    * Ambil informasi instrumen SEBELUM
                    * mengecek error upload.
                    */
                    $instrument = $this->visitModel->db
                        ->table('instruments')
                        ->select('id, answer_type')
                        ->where('id', $questionId)
                        ->get()
                        ->getRowArray();

                    if (!$instrument) {
                        throw new \RuntimeException(
                            'Instrumen tidak ditemukan untuk file yang diupload.'
                        );
                    }

                    $type = strtolower(trim($instrument['answer_type'] ?? ''));

                    /*
                    * Tentukan batas berdasarkan tipe instrumen.
                    */
                    if ($type === 'pdf') {
                        $maxSize = 3 * 1024 * 1024;
                        $fileSize = $file->getSize();
                          log_message(
                                'error',
                                'PDF SIZE CHECK: '.$fileSize.' bytes | MAX: '.$maxSize.' bytes'
                            );
                            if ($fileSize > $maxSize) {
                                $sizeMB = round($fileSize / 1024 / 1024, 2);

                                throw new \RuntimeException(
                                    'Ukuran PDF terlalu besar. Ukuran file: ' .
                                    $sizeMB .
                                    ' MB. Maksimal 3 MB.'
                                );
                            }
                        $typeLabel = 'PDF';
                        $maxLabel = '3 MB';
                    } elseif ($type === 'photo') {
                        $maxSize = 2 * 1024 * 1024;
                        $typeLabel = 'foto';
                        $maxLabel = '2 MB';
                    } else {
                        throw new \RuntimeException(
                            'Instrumen tersebut bukan instrumen upload file.'
                        );
                    }

                    /*
                    * FILE TIDAK VALID
                    */
                    if (!$file || !$file->isValid()) {

                        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {

                            /*
                            * PHP menolak file karena upload_max_filesize.
                            *
                            * Tetap gunakan tipe instrumen supaya
                            * pesan tidak salah menjadi PDF.
                            */
                            if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                                throw new \RuntimeException(
                                    'Ukuran ' . $typeLabel .
                                    ' terlalu besar. Maksimal ' .
                                    $maxLabel . '.'
                                );
                            }

                            if ($file->getError() === UPLOAD_ERR_FORM_SIZE) {
                                throw new \RuntimeException(
                                    'Ukuran ' . $typeLabel .
                                    ' terlalu besar. Maksimal ' .
                                    $maxLabel . '.'
                                );
                            }

                            throw new \RuntimeException(
                                'File ' . $typeLabel .
                                ' gagal diunggah. Silakan coba kembali.'
                            );
                        }

                        continue;
                    }

                    /*
                    * VALIDASI UKURAN FILE
                    */
                    if ($file->getSize() > $maxSize) {

                        throw new \RuntimeException(
                            'Ukuran ' . $typeLabel .
                            ' terlalu besar. Maksimal ' .
                            $maxLabel . '.'
                        );
                    }

                    /*
                    * NAMA SEKOLAH
                    */
                    $schoolName = $visit['school_name'] ?? 'SEKOLAH';
                    $schoolName = strtoupper(trim($schoolName));
                    $schoolName = preg_replace(
                        '/[^A-Z0-9]+/i',
                        '_',
                        $schoolName
                    );
                    $schoolName = trim($schoolName, '_');

                    /*
                    * AMBIL FILE LAMA
                    */
                    $oldAnswer = $this->visitModel->db
                        ->table('visit_answers')
                        ->select('answer')
                        ->where('visit_id', (int) $id)
                        ->where('question_id', $questionId)
                        ->get()
                        ->getRowArray();

                    /*
                    * PDF
                    */
                    if ($type === 'pdf') {

                        if ($file->getMimeType() !== 'application/pdf') {
                            throw new \RuntimeException(
                                'File harus berupa PDF.'
                            );
                        }

                        $extension = strtolower(
                            $file->guessExtension()
                        );

                        if ($extension !== 'pdf') {
                            throw new \RuntimeException(
                                'Ekstensi file harus .pdf.'
                            );
                        }

                        $uploadPath = FCPATH . 'uploads/monev/berkas/';
                        $baseUrl = base_url('uploads/monev/berkas/');
                        $baseName = $schoolName . '_BERKAS_';
                    }

                    /*
                    * FOTO
                    */
                    elseif ($type === 'photo') {

                        $mime = $file->getMimeType();

                        $allowedMime = [
                            'image/jpeg',
                            'image/png'
                        ];

                        if (!in_array($mime, $allowedMime, true)) {
                            throw new \RuntimeException(
                                'Foto harus berformat JPG, JPEG, atau PNG.'
                            );
                        }

                        $extension = strtolower(
                            $file->guessExtension()
                        );

                        if (!in_array(
                            $extension,
                            ['jpg', 'jpeg', 'png'],
                            true
                        )) {
                            throw new \RuntimeException(
                                'Ekstensi foto tidak diperbolehkan.'
                            );
                        }

                        if ($extension === 'jpeg') {
                            $extension = 'jpg';
                        }

                        $uploadPath = FCPATH . 'uploads/monev/foto/';
                        $baseUrl = base_url('uploads/monev/foto/');
                        $baseName = $schoolName . '_FOTO_';
                    }

                    /*
                    * BUAT FOLDER
                    */
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0775, true);
                    }

                    /*
                    * CARI NOMOR FILE BERIKUTNYA
                    */
                    $counter = 1;

                    $existingFiles = glob(
                        $uploadPath .
                        $baseName .
                        '*.' .
                        $extension
                    );

                    if (!empty($existingFiles)) {

                        $numbers = [];

                        foreach ($existingFiles as $existingFile) {

                            $existingName = basename($existingFile);

                            if (preg_match(
                                '/_(\d+)\.[^.]+$/',
                                $existingName,
                                $matches
                            )) {
                                $numbers[] = (int) $matches[1];
                            }
                        }

                        if (!empty($numbers)) {
                            $counter = max($numbers) + 1;
                        }
                    }

                    /*
                    * PERHATIKAN FILE LAMA
                    */
                    if ($oldAnswer && !empty($oldAnswer['answer'])) {

                        $oldPath = parse_url(
                            $oldAnswer['answer'],
                            PHP_URL_PATH
                        );

                        if ($oldPath) {

                            $oldFileName = basename($oldPath);

                            if (preg_match(
                                '/_(\d+)\.[^.]+$/',
                                $oldFileName,
                                $matches
                            )) {

                                $oldNumber = (int) $matches[1];

                                if ($oldNumber >= $counter) {
                                    $counter = $oldNumber + 1;
                                }
                            }
                        }
                    }

                    /*
                    * NAMA FILE BARU
                    */
                    $newName =
                        $baseName .
                        $counter .
                        '.' .
                        $extension;

                    $newFilePath =
                        $uploadPath .
                        $newName;

                    while (is_file($newFilePath)) {

                        $counter++;

                        $newName =
                            $baseName .
                            $counter .
                            '.' .
                            $extension;

                        $newFilePath =
                            $uploadPath .
                            $newName;
                    }

                    /*
                    * PINDAHKAN FILE
                    */
                    $file->move(
                        $uploadPath,
                        $newName
                    );

                    $fileUrl =
                        $baseUrl .
                        $newName;

                    $uploadedFiles[$questionId] =
                        $fileUrl;

                    /*
                    * HAPUS FILE LAMA NANTI
                    */
                    if ($oldAnswer && !empty($oldAnswer['answer'])) {

                        $oldPath = parse_url(
                            $oldAnswer['answer'],
                            PHP_URL_PATH
                        );

                        if ($oldPath) {

                            $oldFileName =
                                basename($oldPath);

                            $oldFilePath =
                                FCPATH .
                                ltrim(
                                    $oldPath,
                                    '/'
                                );

                            if (
                                is_file($oldFilePath) &&
                                realpath($oldFilePath) !==
                                realpath($newFilePath)
                            ) {
                                $oldFilesToDelete[] =
                                    $oldFilePath;
                            }
                        }
                    }
                }
            }

            /*
            * MASUKKAN URL FILE KE ANSWERS
            */
            foreach (
                $uploadedFiles as $questionId => $fileUrl
            ) {
                $answers[$questionId] =
                    $fileUrl;
            }

            /*
            * SIMPAN JAWABAN
            */
            $result =
                $this->visitModel->saveAnswers(
                    $id,
                    $answers
                );

            /*
            * HAPUS FILE LAMA
            */
            foreach (
                $oldFilesToDelete as $oldFilePath
            ) {
                if (is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Draft jawaban berhasil disimpan.',
                'visit_status' =>
                    $result['status'] ?? 'IN_PROGRESS',
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Throwable $th) {

            log_message(
                'error',
                'Error saveAnswers: ' .
                $th->getMessage()
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'message' => $th->getMessage(),
                    'csrf_hash' => csrf_hash()
                ]);
        }
    }
    
    protected function isValidDate($date)
    {
        $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateObject !== false && $dateObject->format('Y-m-d') === $date;
    }

    private function isUserAuthorizedForVisit(array $visit): bool
    {
        $role   = strtolower((string)session()->get('role'));
        $userId = (int)session()->get('user_id');

        // Admin selalu diizinkan
        if ($role === 'admin') {
            return true;
        }

        // Cek struktur array anggota tim dari getDetail() ($visit['members'])
        if (isset($visit['members']) && is_array($visit['members'])) {
            $memberUserIds = array_column($visit['members'], 'user_id');
            if (in_array($userId, array_map('intval', $memberUserIds))) {
                return true;
            }
        }

        // Cek struktur array anggota tim dari getList() ($visit['members'] dengan key 'id')
        if (isset($visit['members']) && is_array($visit['members'])) {
            $memberIds = array_column($visit['members'], 'id');
            if (in_array($userId, array_map('intval', $memberIds))) {
                return true;
            }
        }

        // Fallback jika berupa query mentah dari tabel visit_team
        $isTeamMember = $this->db->table('visit_team')
            ->where('visit_id', (int)($visit['id'] ?? 0))
            ->where('user_id', $userId)
            ->countAllResults();

        return $isTeamMember > 0;
    }
    public function edit($id = null)
    {
        try {
            $id = (int) $id;

            if ($id <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'ID kegiatan Monev tidak valid.',
                    'csrfHash' => csrf_hash()
                ]);
            }

            $visit = $this->db->table('visits v')
                ->select('
                    v.id,
                    v.school_id,
                    v.visit_date,
                    v.status,
                    s.region_id
                ')
                ->join('schools s', 's.id = v.school_id', 'left')
                ->where('v.id', $id)
                ->get()
                ->getRowArray();

            if (!$visit) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.',
                    'csrfHash' => csrf_hash()
                ]);
            }

            // EDIT HANYA BOLEH SAAT DRAFT
            if ($visit['status'] !== 'DRAFT') {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Kegiatan Monev hanya dapat diedit saat status DRAFT.',
                    'csrfHash' => csrf_hash()
                ]);
            }

            $members = $this->db->table('visit_team')
                ->select('user_id')
                ->where('visit_id', $id)
                ->get()
                ->getResultArray();

            $userIds = array_map(
                'intval',
                array_column($members, 'user_id')
            );

            return $this->response->setJSON([
                'status' => true,
                'data' => [
                    'id'         => (int) $visit['id'],
                    'region_id'  => (int) $visit['region_id'],
                    'school_id'  => (int) $visit['school_id'],
                    'visit_date' => $visit['visit_date'],
                    'user_ids'   => $userIds
                ],
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {

            log_message(
                'error',
                'VISITS EDIT ERROR: ' . $e->getMessage()
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'message' => 'Gagal mengambil data kegiatan Monev.',
                    'csrfHash' => csrf_hash()
                ]);
        }
    }

    public function update()
    {
        if(!$this->request->isAJAX()){
            return $this->response->setStatusCode(400)->setJSON([
                'status'=>false,
                'message'=>'Request tidak valid.',
                'csrfHash'=>csrf_hash()
            ]);
        }

        try{
            $rawPost=$this->request->getPost();

            log_message('error','=== UPDATE VISIT START ===');
            log_message('error','RAW POST: '.json_encode($rawPost));

            $visitId=(int)$this->request->getPost('visit_id');
            $regionId=(int)$this->request->getPost('region_id');
            $schoolId=(int)$this->request->getPost('school_id');
            $visitDate=trim((string)$this->request->getPost('visit_date'));

            $userIds=$this->request->getPost('user_ids');

            if(!is_array($userIds)){
                $userIds=$userIds ? [$userIds] : [];
            }

            $userIds=array_values(array_unique(array_filter(array_map('intval',$userIds))));

            log_message('error','PARSED DATA: '.json_encode([
                'visit_id'=>$visitId,
                'region_id'=>$regionId,
                'school_id'=>$schoolId,
                'visit_date'=>$visitDate,
                'user_ids'=>$userIds
            ]));

            if($visitId<=0){
                throw new \RuntimeException('ID kegiatan Monev tidak valid.');
            }

            if($regionId<=0){
                throw new \RuntimeException('Wilayah wajib dipilih.');
            }

            if($schoolId<=0){
                throw new \RuntimeException('Sekolah wajib dipilih.');
            }

            if($visitDate===''){
                throw new \RuntimeException('Tanggal Monev wajib diisi.');
            }

            if(!$this->isValidDate($visitDate)){
                throw new \RuntimeException('Format tanggal Monev tidak valid.');
            }

            if(count($userIds)<1){
                throw new \RuntimeException('Minimal satu petugas harus dipilih.');
            }

            $visit=$this->db->table('visits')
                ->select('id,status,school_id,visit_date')
                ->where('id',$visitId)
                ->get()
                ->getRowArray();

            log_message('error','VISIT LAMA: '.json_encode($visit));

            if(!$visit){
                throw new \RuntimeException('Kegiatan Monev tidak ditemukan.');
            }

            if($visit['status']!=='DRAFT'){
                throw new \RuntimeException('Kegiatan Monev hanya dapat diedit saat status DRAFT.');
            }

            $school=$this->db->table('schools')
                ->select('id,region_id,school_name')
                ->where('id',$schoolId)
                ->get()
                ->getRowArray();

            log_message('error','SCHOOL: '.json_encode($school));

            if(!$school){
                throw new \RuntimeException('Sekolah tidak ditemukan.');
            }

            if($regionId!==10 && (int)$school['region_id']!==$regionId){
                throw new \RuntimeException('Sekolah yang dipilih tidak berada pada wilayah yang dipilih.');
            }

            $validUsers=$this->db->table('users')
                ->select('id')
                ->whereIn('id',$userIds)
                ->where('role','petugas')
                ->where('is_active',1)
                ->get()
                ->getResultArray();

            $validUserIds=array_map('intval',array_column($validUsers,'id'));
            sort($validUserIds);

            $checkUserIds=$userIds;
            sort($checkUserIds);

            log_message('error','VALID USERS: '.json_encode($validUserIds));
            log_message('error','REQUEST USERS: '.json_encode($checkUserIds));

            if($validUserIds!==$checkUserIds){
                throw new \RuntimeException('Ada petugas yang tidak valid, tidak aktif, atau bukan petugas.');
            }

            $this->db->transBegin();

            log_message('error','TRANSACTION DIMULAI');

            $updateData=[
                'school_id'=>$schoolId,
                'visit_date'=>$visitDate,
                'updated_at'=>date('Y-m-d H:i:s')
            ];

            log_message('error','UPDATE VISITS DATA: '.json_encode($updateData));

            $updateResult=$this->db->table('visits')
                ->where('id',$visitId)
                ->update($updateData);

            log_message('error','UPDATE VISITS RESULT: '.($updateResult?'TRUE':'FALSE'));
            log_message('error','UPDATE VISITS DB ERROR: '.json_encode($this->db->error()));

            if(!$updateResult){
                throw new \RuntimeException('Gagal update tabel visits: '.json_encode($this->db->error()));
            }

            $deleteResult=$this->db->table('visit_team')
                ->where('visit_id',$visitId)
                ->delete();

            log_message('error','DELETE TEAM RESULT: '.($deleteResult?'TRUE':'FALSE'));
            log_message('error','DELETE TEAM DB ERROR: '.json_encode($this->db->error()));

            if(!$deleteResult){
                throw new \RuntimeException('Gagal menghapus tim lama: '.json_encode($this->db->error()));
            }

            $teamRows=[];

            foreach($userIds as $userId){
                $teamRows[]=[
                    'visit_id'=>$visitId,
                    'user_id'=>$userId,
                    'role'=>'anggota'
                ];
            }

            log_message('error','TEAM BARU: '.json_encode($teamRows));

            $insertResult=$this->db->table('visit_team')->insertBatch($teamRows);

            log_message('error','INSERT TEAM RESULT: '.($insertResult?'TRUE':'FALSE'));
            log_message('error','INSERT TEAM DB ERROR: '.json_encode($this->db->error()));

            if(!$insertResult){
                throw new \RuntimeException('Gagal memasukkan tim baru: '.json_encode($this->db->error()));
            }

            if($this->db->transStatus()===false){
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $this->db->transCommit();

            log_message('error','TRANSACTION COMMIT BERHASIL');

            $after=$this->db->table('visits')
                ->select('id,school_id,visit_date,status,updated_at')
                ->where('id',$visitId)
                ->get()
                ->getRowArray();

            log_message('error','DATA SETELAH UPDATE: '.json_encode($after));

            log_message('error','=== UPDATE VISIT SUCCESS ===');

            return $this->response->setJSON([
                'status'=>true,
                'message'=>'Kegiatan Monev berhasil diperbarui.',
                'csrfHash'=>csrf_hash()
            ]);

        }catch(\Throwable $e){

            if($this->db->transStatus()!==false){
                $this->db->transRollback();
            }

            log_message('error','=== UPDATE VISIT ERROR ===');
            log_message('error','ERROR MESSAGE: '.$e->getMessage());
            log_message('error','ERROR FILE: '.$e->getFile());
            log_message('error','ERROR LINE: '.$e->getLine());
            log_message('error','DB ERROR: '.json_encode($this->db->error()));
            log_message('error','TRACE: '.$e->getTraceAsString());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'=>false,
                    'message'=>$e->getMessage(),
                    'csrfHash'=>csrf_hash()
                ]);
        }
    }
}