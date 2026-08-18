<?php
namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table         = 'visits';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'school_id',
        'visit_date',
        'officer_id',
        'status',
        'completed_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getList($keyword = '', $status = '', $userId = null)
    {
        $builder=$this->db->table('visits v');
        $builder->select('v.id,v.school_id,v.visit_date,v.status,v.notes,v.created_at,v.updated_at,s.npsn,s.school_name AS school_name,s.level,s.region_id,r.name AS region_name');
        $builder->join('schools s','s.id=v.school_id','left');
        $builder->join('region r','r.id=s.region_id','left');

        // FILTER PETUGAS: Jika $userId diisi, filter hanya kegiatan Monev yang diikuti petugas tersebut
        if ($userId !== null && (int)$userId > 0) {
            $builder->join('visit_team vt_filter', 'vt_filter.visit_id = v.id', 'inner');
            $builder->where('vt_filter.user_id', (int)$userId);
        }

        if ($keyword !== '') {
            $builder->groupStart();
            $builder->like('s.npsn', $keyword);
            $builder->orLike('s.school_name', $keyword);
            $builder->groupEnd();
        }

        if ($status !== '') {
            $builder->where('v.status', $status);
        }

        $builder->orderBy('v.id', 'DESC');
        
        // Gunakan DISTINCT jika JOIN tim berpotensi menghasilkan duplikasi baris
        if ($userId !== null && (int)$userId > 0) {
            $builder->groupBy('v.id');
        }

        $rows = $builder->get()->getResultArray();

        if (!$rows) {
            return [];
        }

        $visitIds = array_map('intval', array_column($rows, 'id'));

        $teamRows = $this->db->table('visit_team vt')
            ->select('vt.visit_id, vt.user_id, u.name')
            ->join('users u', 'u.id = vt.user_id', 'left')
            ->whereIn('vt.visit_id', $visitIds)
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();

        $teams = [];
        foreach ($teamRows as $team) {
            $visitId = (int)$team['visit_id'];
            if (!isset($teams[$visitId])) {
                $teams[$visitId] = [];
            }
            $teams[$visitId][] = [
                'id'   => (int)$team['user_id'],
                'name' => $team['name'] ?? 'Petugas'
            ];
        }

        foreach ($rows as &$row) {
            $row['members'] = $teams[(int)$row['id']] ?? [];
            $names          = [];
            foreach ($row['members'] as $member) {
                $names[] = $member['name'];
            }
            $row['member_names'] = implode(', ', $names);
        }

        return $rows;
    }

    public function getSchools()
    {
        return $this->db->table('schools')
            ->select('id, npsn, name, level, city, district, region')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getOfficers()
    {
        return $this->db->table('users')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function createVisit($schoolId, $visitDate, $userIds)
    {
        $this->db->transStart();
        $this->insert([
            'school_id'  => (int)$schoolId,
            'visit_date' => $visitDate,
            'status'     => 'DRAFT',
            'notes'      => null
        ]);

        $visitId = (int)$this->getInsertID();

        if ($visitId <= 0) {
            $this->db->transRollback();
            throw new \RuntimeException('Gagal membuat kegiatan Monev.');
        }

        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), function ($id) {
            return $id > 0;
        })));

        if (empty($userIds)) {
            $this->db->transRollback();
            throw new \RuntimeException('Petugas Monev belum dipilih.');
        }

        $teamRows = [];
        foreach ($userIds as $userId) {
            $teamRows[] = [
                'visit_id' => $visitId,
                'user_id'  => $userId,
                'role'     => 'anggota'
            ];
        }

        $this->db->table('visit_team')->insertBatch($teamRows);
        $savedCount = $this->db->affectedRows();

        if ($savedCount !== count($teamRows)) {
            $this->db->transRollback();
            throw new \RuntimeException('Jumlah petugas yang tersimpan tidak sesuai dengan petugas yang dipilih.');
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Gagal menyimpan kegiatan Monev.');
        }

        return $visitId;
    }

    public function getDetail($id)
    {
        $visit = $this->db->table('visits v')
            ->select('v.id, v.school_id, v.visit_date, v.status, v.notes, v.created_at, v.updated_at, s.npsn, s.school_name, s.level')
            ->join('schools s', 's.id = v.school_id', 'left')
            ->where('v.id', (int)$id)
            ->get()
            ->getRowArray();

        if (!$visit) {
            return null;
        }

        $visit['members'] = $this->db->table('visit_team vt')
            ->select('vt.id, vt.user_id, u.name')
            ->join('users u', 'u.id = vt.user_id', 'left')
            ->where('vt.visit_id', (int)$id)
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();

        return $visit;
    }

    public function updateStatus($id, $status)
    {
        log_message('error', 'UPDATE STATUS VISIT: ID=' . $id . ' STATUS=' . $status);
        $result = $this->update((int)$id, ['status' => $status]);
        log_message('error', 'UPDATE STATUS RESULT: ' . ($result ? 'TRUE' : 'FALSE'));
        return $result;
    }

    public function deleteVisit($id)
    {
        $id = (int)$id;
        $this->db->transStart();

        if ($this->db->tableExists('visit_answers')) {
            $this->db->table('visit_answers')->where('visit_id', $id)->delete();
        }
        if ($this->db->tableExists('visit_team')) {
            $this->db->table('visit_team')->where('visit_id', $id)->delete();
        }
        if ($this->db->tableExists('visit_notes')) {
            $this->db->table('visit_notes')->where('visit_id', $id)->delete();
        }
        if ($this->db->tableExists('visit_photos')) {
            $this->db->table('visit_photos')->where('visit_id', $id)->delete();
        }

        $this->where('id', $id)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Gagal menghapus kegiatan Monev.');
        }

        return true;
    }

    public function getInstrumentData($visitId)
    {
        $rows = $this->db->table('instruments i')
            ->select('i.id, i.section_id, i.code, i.question, i.description, i.answer_type, i.options, i.sort_order, i.is_active, s.name AS section_name, s.description AS section_description, s.sort_order AS section_sort_order')
            ->join('instrument_sections s', 's.id = i.section_id', 'left')
            ->where('i.is_active', 1)
            ->orderBy('s.sort_order', 'ASC')
            ->orderBy('i.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $answers = [];
        if ($visitId) {
            $answerRows = $this->db->table('visit_answers')
                ->select('question_id, answer')
                ->where('visit_id', (int)$visitId)
                ->get()
                ->getResultArray();

            foreach ($answerRows as $answer) {
                $answers[$answer['question_id']] = $answer['answer'];
            }
        }

        $sections = [];
        foreach ($rows as $row) {
            $sectionId = (int)$row['section_id'];
            if (!isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'id'          => $sectionId,
                    'name'        => $row['section_name'] ?? '-',
                    'description' => $row['section_description'] ?? '',
                    'instruments' => []
                ];
            }

            $answer  = $answers[$row['id']] ?? '';
            $options = [];

            if (!empty($row['options'])) {
                if (is_string($row['options'])) {
                    $decoded = json_decode($row['options'], true);
                    if (is_array($decoded)) {
                        $options = $decoded;
                    } else {
                        $options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $row['options']))));
                    }
                } elseif (is_array($row['options'])) {
                    $options = $row['options'];
                }
            }

            $sections[$sectionId]['instruments'][] = [
                'id'          => (int)$row['id'],
                'code'        => $row['code'],
                'question'    => $row['question'],
                'description' => $row['description'],
                'answer_type' => $row['answer_type'],
                'options'     => $options,
                'answer'      => $answer
            ];
        }

        return array_values($sections);
    }

    public function saveAnswers($visitId, $answers = [])
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        if (!empty($answers) && is_array($answers)) {
            foreach ($answers as $questionId => $answerValue) {
                if (empty($questionId)) {
                    continue;
                }

                // Jika jawaban berbentuk array (misal checkbox), ubah ke string JSON
                if (is_array($answerValue)) {
                    $answerValue = json_encode($answerValue, JSON_UNESCAPED_UNICODE);
                }

                // Pengecekan ketersediaan data
                $exists = $db->table('visit_answers')
                             ->where('visit_id', $visitId)
                             ->where('question_id', $questionId)
                             ->countAllResults();

                if ($exists > 0) {
                    // UPDATE data yang sudah ada
                    $db->table('visit_answers')
                       ->where('visit_id', $visitId)
                       ->where('question_id', $questionId)
                       ->update([
                           'answer'     => $answerValue,
                           'updated_at' => $now
                       ]);
                } else {
                    // INSERT data baru jika belum ada
                    $db->table('visit_answers')
                       ->insert([
                           'visit_id'    => $visitId,
                           'question_id' => $questionId,
                           'answer'      => $answerValue,
                           'created_at'  => $now,
                           'updated_at'  => $now
                       ]);
                }
            }
        }

        // Update status visitasi
      $visit = $this->find((int) $visitId);
        if ($visit && $visit['status'] !== 'COMPLETED') {
            $this->update((int) $visitId, [
                'status' => 'IN_PROGRESS',
                'updated_at' => $now
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Gagal menyimpan data ke database.');
        }

        return [
            'status' => 'IN_PROGRESS'
        ];
    }

    public function completeVisit($id)
    {
        $id    = (int)$id;
        $visit = $this->find($id);

        if (!$visit) {
            return [
                'status'  => false,
                'message' => 'Kegiatan Monev tidak ditemukan.'
            ];
        }

        if ($visit['status'] === 'COMPLETED') {
            return [
                'status'  => false,
                'message' => 'Kegiatan Monev sudah selesai.'
            ];
        }

        $required = $this->db->table('instruments')
            ->select('id, code, question')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->get()
            ->getResultArray();

        if ($required) {
            $answered = $this->db->table('visit_answers')
                ->select('question_id')
                ->where('visit_id', $id)
                ->get()
                ->getResultArray();

            $answeredIds = [];
            foreach ($answered as $item) {
                $answeredIds[] = (int)$item['question_id'];
            }

            $missing = [];
            foreach ($required as $item) {
                if (!in_array((int)$item['id'], $answeredIds, true)) {
                    $missing[] = $item['code'] . ' - ' . $item['question'];
                }
            }

            if (!empty($missing)) {
                return [
                    'status'  => false,
                    'message' => 'Masih ada instrumen wajib yang belum diisi.',
                    'missing' => $missing
                ];
            }
        }

        $this->updateStatus($id, 'COMPLETED');

        return [
            'status'       => true,
            'message'      => 'Kegiatan Monev berhasil diselesaikan.',
            'status_value' => 'COMPLETED'
        ];
    }
}