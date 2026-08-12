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
            $keyword = trim((string)$this->request->getGet('keyword'));
            $status  = trim((string)$this->request->getGet('status'));
            $data    = $this->visitModel->getList($keyword, $status);

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
            // 1. Ambil array ID sekolah yang SUDAH ADA di tabel visits
            $existingVisits = $this->db->table('visits')
                ->select('school_id')
                ->get()
                ->getResultArray();

            // Extraksi ke array 1 dimensi ID sekolah, hilangkan nilai null/kosong
            $usedSchoolIds = array_filter(array_column($existingVisits, 'school_id'));

            // 2. Query tabel schools
            $builder = $this->db->table('schools')
                ->select('id, npsn, school_name, level, city_id, district_id, region_id');

            // Jika ada sekolah yang sudah masuk ke visit, abaikan dari daftar (WHERE NOT IN)
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
            // Asumsi tabel Anda bernama 'users'. Ganti jika nama tabel berbeda.
            $rows = $this->db->table('users') 
                ->select('id, name') // Sesuaikan field yang dibutuhkan
                ->where('is_active', 1) // FILTER: Hanya ambil yang aktif
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

        // FIX: Tangkap user_ids baik dari POST biasa, POST array, maupun JSON
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

            if (($visit['status'] ?? '') !== 'DRAFT') {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev yang sudah dimulai tidak dapat dihapus.'
                ]);
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
            $visit = $this->visitModel->find($id);

            if (!$visit) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kegiatan Monev tidak ditemukan.'
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
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'ID Visit tidak valid.'
            ]);
        }

        // Contoh Logika Update Status Visit ke COMPLETED
        $updated = $this->visitModel->update($id, [
            'status'       => 'COMPLETED',
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal memperbarui status visitasi di database.'
            ]);
        }

        return $this->response->setJSON([
            'status'    => true,
            'message'   => 'Kegiatan Monev berhasil diselesaikan.',
            'csrf_hash' => csrf_hash() // kirim token CSRF baru jika aktif
        ]);

    } catch (\Exception $e) {
        log_message('error', $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status'  => false,
            'message' => 'Error Server: ' . $e->getMessage()
        ]);
    }
}
public function saveAnswers($id = null)
{
    try {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'    => false,
                'message'   => 'ID Visitasi tidak valid.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $answers = $this->request->getPost('answers');

        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }

        // Panggil method di Model
        $result = $this->visitModel->saveAnswers($id, $answers);

        return $this->response->setJSON([
            'status'       => true,
            'message'      => 'Draft jawaban berhasil disimpan.',
            'visit_status' => $result['status'] ?? 'IN_PROGRESS',
            'csrf_hash'    => csrf_hash()
        ]);

    } catch (\Throwable $th) {
        log_message('error', 'Error saveAnswers: ' . $th->getMessage());

        return $this->response->setStatusCode(500)->setJSON([
            'status'    => false,
            'message'   => 'Server Error: ' . $th->getMessage(),
            'csrf_hash' => csrf_hash()
        ]);
    }
}
    protected function isValidDate($date)
    {
        $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateObject !== false && $dateObject->format('Y-m-d') === $date;
    }
}