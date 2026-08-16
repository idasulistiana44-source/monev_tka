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
            $existingVisits = $this->db->table('visits')
                ->select('school_id')
                ->get()
                ->getResultArray();

            $usedSchoolIds = array_filter(array_column($existingVisits, 'school_id'));

            $builder = $this->db->table('schools')
                ->select('id, npsn, school_name, level, city_id, district_id, region_id');

            if (!empty($usedSchoolIds)) {
                $builder->whereNotIn('id', $usedSchoolIds);
            }

            $rows = $builder->orderBy('school_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as &$row) {
                $row['name'] = $row['school_name'];
            }
            unset($row);

            return $this->response->setJSON([
                'status' => true,
                'data'   => $rows
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'VISITS SCHOOLS ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal mengambil data sekolah: ' . $e->getMessage()
            ]);
        }
    }

    public function officers()
    {
        try {
            $rows = $this->db->table('users') 
                ->select('id, name')
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'status' => true,
                'data'   => $rows
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VISITS OFFICERS ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal mengambil data petugas.'
            ]);
        }
    }

    public function create()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Request tidak valid.'
            ]);
        }

        $schoolId  = (int)$this->request->getPost('school_id');
        $visitDate = trim((string)$this->request->getPost('visit_date'));

        $userIds = $this->request->getPost('user_ids') ?? $this->request->getPost('user_ids[]');
        if (empty($userIds)) {
            $json    = $this->request->getJSON(true);
            $userIds = $json['user_ids'] ?? [];
        }

        if (!is_array($userIds)) {
            $userIds = $userIds ? [$userIds] : [];
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if ($schoolId <= 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Sekolah wajib dipilih.'
            ]);
        }

        $school = $this->db->table('schools')
            ->where('id', $schoolId)
            ->get()
            ->getRowArray();

        if (!$school) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Sekolah tidak ditemukan.'
            ]);
        }

        if ($visitDate === '') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal Monev wajib diisi.'
            ]);
        }

        if (!$this->isValidDate($visitDate)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Format tanggal Monev tidak valid.'
            ]);
        }

        if (count($userIds) < 1) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Minimal satu petugas harus dipilih.'
            ]);
        }

        $validUsers = $this->db->table('users')
            ->select('id')
            ->whereIn('id', $userIds)
            ->get()
            ->getResultArray();

        $validUserIds = array_map('intval', array_column($validUsers, 'id'));

        if (count($validUserIds) !== count($userIds)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Ada petugas yang tidak valid.'
            ]);
        }

        try {
            $visitId = $this->visitModel->createVisit($schoolId, $visitDate, $userIds);

            return $this->response->setJSON([
                'status'   => true,
                'message'  => 'Kegiatan Monev berhasil dibuat.',
                'id'       => $visitId,
                'redirect' => site_url('visits/form/' . $visitId)
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VISITS CREATE ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }

   public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Request tidak valid.'
            ]);
        }

        $id = (int)$id;

        if ($id <= 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'ID kegiatan Monev tidak valid.'
            ]);
        }

        try {
            $visit = $this->visitModel->find($id);

            if (!$visit) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.'
                ]);
            }

            $userRole = strtolower((string)session()->get('role'));

            // HAK AKSES DAN STATUS HAPUS:
            // 1. Admin: Boleh menghapus status DRAFT dan COMPLETED (tidak boleh menghapus yang sedang IN_PROGRESS)
            // 2. Petugas / Non-Admin: Hanya boleh menghapus status DRAFT
            if ($userRole === 'admin') {
                if (($visit['status'] ?? '') === 'IN_PROGRESS') {
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Kegiatan Monev yang sedang berlangsung (IN_PROGRESS) tidak dapat dihapus.'
                    ]);
                }
            } else {
                if (($visit['status'] ?? '') !== 'DRAFT') {
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Petugas hanya dapat menghapus kegiatan Monev yang berstatus DRAFT.'
                    ]);
                }
            }

            $this->visitModel->deleteVisit($id);

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Kegiatan Monev berhasil dihapus.'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VISITS DELETE ERROR: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage()
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
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'status' => false,
                        'message' => 'ID Visitasi tidak valid.',
                        'csrf_hash' => csrf_hash()
                    ]);
            }
            $visit = $this->visitModel->getDetail($id);
            if (!$visit) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
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
                    if (!$file || !$file->isValid()) {
                        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                            throw new \RuntimeException('Upload file gagal: ' . $file->getErrorString());
                        }
                        continue;
                    }
                    $questionId = (int) $questionId;
                    if ($questionId <= 0) {
                        continue;
                    }
                    $instrument = $this->visitModel->db
                        ->table('instruments')
                        ->select('id, answer_type')
                        ->where('id', $questionId)
                        ->get()
                        ->getRowArray();
                    if (!$instrument) {
                        throw new \RuntimeException('Instrumen tidak ditemukan untuk file yang diupload.');
                    }
                    $type = strtolower($instrument['answer_type'] ?? '');
                    $schoolName = $visit['school_name'] ?? 'SEKOLAH';
                    $schoolName = strtoupper(trim($schoolName));
                    $schoolName = preg_replace('/[^A-Z0-9]+/i', '_', $schoolName);
                    $schoolName = trim($schoolName, '_');
                    $oldAnswer = $this->visitModel->db
                        ->table('visit_answers')
                        ->select('answer')
                        ->where('visit_id', $id)
                        ->where('question_id', $questionId)
                        ->get()
                        ->getRowArray();
                    if ($type === 'pdf') {
                        $maxSize = 5 * 1024 * 1024;
                        if ($file->getSize() > $maxSize) {
                            throw new \RuntimeException('Ukuran PDF maksimal 5 MB.');
                        }
                        if ($file->getMimeType() !== 'application/pdf') {
                            throw new \RuntimeException('File harus berupa PDF.');
                        }
                        $extension = strtolower($file->guessExtension());
                        if ($extension !== 'pdf') {
                            throw new \RuntimeException('Ekstensi file harus .pdf.');
                        }
                        $uploadPath = FCPATH . 'uploads/monev/berkas/';
                        $baseUrl = base_url('uploads/monev/berkas/');
                        $baseName = $schoolName . '_BERKAS_';
                    } elseif ($type === 'photo') {
                        $maxSize = 3 * 1024 * 1024;
                        if ($file->getSize() > $maxSize) {
                            throw new \RuntimeException('Ukuran foto maksimal 3 MB.');
                        }
                        $mime = $file->getMimeType();
                        $allowedMime = ['image/jpeg', 'image/png'];
                        if (!in_array($mime, $allowedMime, true)) {
                            throw new \RuntimeException('Foto harus berformat JPG, JPEG, atau PNG.');
                        }
                        $extension = strtolower($file->guessExtension());
                        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                            throw new \RuntimeException('Ekstensi foto tidak diperbolehkan.');
                        }
                        if ($extension === 'jpeg') {
                            $extension = 'jpg';
                        }
                        $uploadPath = FCPATH . 'uploads/monev/foto/';
                        $baseUrl = base_url('uploads/monev/foto/');
                        $baseName = $schoolName . '_FOTO_';
                    } else {
                        throw new \RuntimeException('Instrumen tersebut bukan instrumen upload file.');
                    }
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0775, true);
                    }
                    $counter = 1;

                    if ($oldAnswer && !empty($oldAnswer['answer'])) {
                        $oldPath = parse_url($oldAnswer['answer'], PHP_URL_PATH);

                        if ($oldPath) {
                            $oldFileName = basename($oldPath);

                            if (preg_match('/_(\d+)\.[^.]+$/', $oldFileName, $matches)) {
                                $counter = ((int) $matches[1]) + 1;
                            }
                        }
                    }

                    $newName = $baseName . $counter . '.' . $extension;
                    $newFilePath = $uploadPath . $newName;
                    $file->move($uploadPath, $newName, true);
                    $fileUrl = $baseUrl . $newName;
                    $uploadedFiles[$questionId] = $fileUrl;
                    if ($oldAnswer && !empty($oldAnswer['answer'])) {
                        $oldPath = parse_url($oldAnswer['answer'], PHP_URL_PATH);
                        if ($oldPath) {
                            $oldFilePath = FCPATH . ltrim($oldPath, '/');
                            if (is_file($oldFilePath) && realpath($oldFilePath) !== realpath($newFilePath)) {
                                $oldFilesToDelete[] = $oldFilePath;
                            }
                        }
                    }
                }
            }
            foreach ($uploadedFiles as $questionId => $fileUrl) {
                $answers[$questionId] = $fileUrl;
            }
            $result = $this->visitModel->saveAnswers($id, $answers);
            foreach ($oldFilesToDelete as $oldFilePath) {
                if (is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Draft jawaban berhasil disimpan.',
                'visit_status' => $result['status'] ?? 'IN_PROGRESS',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Throwable $th) {
            log_message('error', 'Error saveAnswers: ' . $th->getMessage());
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

    /**
     * Helper privat untuk mengecek apakah user terotentikasi memiliki akses ke data visit terkait.
     */
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
}