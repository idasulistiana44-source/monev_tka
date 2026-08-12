<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'visits';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getFiltersData()
    {
        $districts = [];
        if ($this->db->tableExists('districts')) {
            $districts = $this->db->table('districts d')
                ->select('d.id, d.name AS district_name')
                ->join('schools s', 's.district_id = d.id', 'inner')
                ->distinct()
                ->orderBy('d.name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return [
            'districts' => $districts,
            'levels'    => $this->db->table('schools')->select('level')->distinct()->where('level IS NOT NULL')->orderBy('level', 'ASC')->get()->getResultArray(),
            'schools'   => $this->db->table('schools')->select('id, school_name AS name')->orderBy('school_name', 'ASC')->get()->getResultArray(),
            'officers'  => $this->db->table('users')->select('id, name')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray()
        ];
    }

    private function applyFilters(&$builder, $filters)
    {
        if (!empty($filters['start_date'])) {
            $builder->where('v.visit_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('v.visit_date <=', $filters['end_date']);
        }
        if (!empty($filters['district_id'])) {
            $builder->where('s.district_id', $filters['district_id']);
        }
        if (!empty($filters['level'])) {
            $builder->where('s.level', $filters['level']);
        }
        if (!empty($filters['school_id'])) {
            $builder->where('v.school_id', (int)$filters['school_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('v.status', $filters['status']);
        }
        if (!empty($filters['officer_id'])) {
            $builder->where("EXISTS (SELECT 1 FROM visit_team vt WHERE vt.visit_id = v.id AND vt.user_id = " . (int)$filters['officer_id'] . ")", null, false);
        }
    }

    public function getAspectSummary($filters = [])
    {
        $builder = $this->db->table('instrument_sections sec');
        $builder->select('sec.id, sec.name AS aspect_name, 
            COUNT(CASE WHEN LOWER(ans.answer) LIKE "%sangat baik%" OR LOWER(ans.answer) LIKE "%sangat memadai%" THEN 1 END) AS sangated_count,
            COUNT(CASE WHEN LOWER(ans.answer) LIKE "%baik%" AND LOWER(ans.answer) NOT LIKE "%sangat%" THEN 1 END) AS baik_count,
            COUNT(CASE WHEN LOWER(ans.answer) LIKE "%cukup%" THEN 1 END) AS cukup_count,
            COUNT(CASE WHEN LOWER(ans.answer) LIKE "%kurang%" THEN 1 END) AS kurang_count,
            COUNT(ans.id) AS total_answers');

        $builder->join('instruments inst', 'inst.section_id = sec.id', 'inner');
        $builder->join('visit_answers ans', 'ans.question_id = inst.id', 'inner');
        $builder->join('visits v', 'v.id = ans.visit_id', 'inner');
        $builder->join('schools s', 's.id = v.school_id', 'inner');

        $this->applyFilters($builder, $filters);

        $builder->groupBy('sec.id, sec.name');
        return $builder->get()->getResultArray();
    }

    public function getFollowups($filters = [])
    {
        $builder = $this->db->table('visit_answers ans');
        $builder->select('ans.id, s.school_name, sec.name AS aspect_name, ans.answer AS finding_text, "-" AS recommendation, v.status');
        $builder->join('visits v', 'v.id = ans.visit_id', 'inner');
        $builder->join('schools s', 's.id = v.school_id', 'inner');
        $builder->join('instruments inst', 'inst.id = ans.question_id', 'left');
        $builder->join('instrument_sections sec', 'sec.id = inst.section_id', 'left');

        // Khusus mengambil catatan visitasi (question_id = 18)
        $builder->where('ans.question_id', 18);
        $builder->where('ans.answer IS NOT NULL');
        $builder->where('ans.answer !=', '');

        $this->applyFilters($builder, $filters);

        $builder->orderBy('ans.id', 'DESC');
        return $builder->get()->getResultArray();
    }

    public function updateFollowupStatus($id, $status)
    {
        // Update status kegiatan pada tabel visits jika diperlukan
        return $this->db->table('visit_answers')
            ->where('id', (int)$id)
            ->update([
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}