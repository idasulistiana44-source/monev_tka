<?php
namespace App\Models;
use CodeIgniter\Model;
class ReportsModel extends Model
{
    protected $table='visits';
    protected $primaryKey='id';

   public function getReports(
    $keyword = '',
    $regionId = '',
    $status = '',
    $dateFrom = '',
    $dateTo = '',
    $userRole = '',
    $userId = 0
    ) {
        $builder = $this->db->table('visits v');

        $builder->select('
            v.id,
            v.school_id,
            v.visit_date,
            v.status,
            v.created_at,
            v.updated_at,

            s.npsn,
            s.school_name,
            s.level,
            s.region_id,

            r.name AS region_name,

            v.created_by,
            creator.name AS created_by_name,

            v.submitted_by,
            submitter.name AS submitted_by_name
        ');

        // HANYA ambil visit yang sekolahnya masih ada
        $builder->join(
            'schools s',
            's.id = v.school_id',
            'inner'
        );

        $builder->join(
            'region r',
            'r.id = s.region_id',
            'left'
        );

        $builder->join(
            'users creator',
            'creator.id = v.created_by',
            'left'
        );

        $builder->join(
            'users submitter',
            'submitter.id = v.submitted_by',
            'left'
        );

        // PETUGAS hanya melihat Monev yang diikutinya
        if (strtolower($userRole) !== 'admin') {

            $builder->join(
                'visit_team vt',
                'vt.visit_id = v.id',
                'inner'
            );

            $builder->where(
                'vt.user_id',
                (int) $userId
            );

            // Hindari duplicate karena satu visit
            // bisa memiliki beberapa anggota tim
            $builder->groupBy('v.id');
        }

        // SEARCH
        if ($keyword !== '') {

            $builder->groupStart();

            $builder->like(
                's.school_name',
                $keyword
            );

            $builder->orLike(
                's.npsn',
                $keyword
            );

            $builder->orLike(
                'r.name',
                $keyword
            );

            $builder->groupEnd();
        }

        // FILTER WILAYAH
        if ($regionId !== '') {

            $builder->where(
                's.region_id',
                (int) $regionId
            );
        }

        // REPORT HANYA YANG SELESAI
        $builder->where(
            'v.status',
            'COMPLETED'
        );

        // FILTER TANGGAL AWAL
        if ($dateFrom !== '') {

            $builder->where(
                'v.visit_date >=',
                $dateFrom
            );
        }

        // FILTER TANGGAL AKHIR
        if ($dateTo !== '') {

            $builder->where(
                'v.visit_date <=',
                $dateTo
            );
        }

        // URUTKAN
        $builder->orderBy(
            'v.visit_date',
            'DESC'
        );

        $builder->orderBy(
            'v.id',
            'DESC'
        );

        $visits = $builder
            ->get()
            ->getResultArray();

        // TAMBAHKAN DATA TIM
        foreach ($visits as &$visit) {

            $visit['members'] = $this->getMembers(
                (int) $visit['id']
            );

            $names = [];

            foreach ($visit['members'] as $member) {

                if (!empty($member['name'])) {
                    $names[] = $member['name'];
                }
            }

            $visit['member_names'] = implode(
                ', ',
                $names
            );
        }

        unset($visit);

        return $visits;
    }

    public function getReport(
    $id,
    $userRole = '',
    $userId = 0
) {
    $id = (int) $id;

    if ($id <= 0) {
        return null;
    }

    $builder = $this->db->table('visits v');

    $builder->select('
        v.id,
        v.school_id,
        v.visit_date,
        v.status,
        v.created_at,
        v.updated_at,

        s.npsn,
        s.school_name,
        s.level,
        s.region_id,

        r.name AS region_name,

        v.created_by,
        creator.name AS created_by_name,

        v.submitted_by,
        submitter.name AS submitted_by_name
    ');

    /*
    |--------------------------------------------------------------------------
    | SEKOLAH
    |--------------------------------------------------------------------------
    | INNER JOIN supaya visit lama yang sekolahnya sudah dihapus
    | tidak bisa lagi diambil sebagai report.
    */
    $builder->join(
        'schools s',
        's.id = v.school_id',
        'inner'
    );

    $builder->join(
        'region r',
        'r.id = s.region_id',
        'left'
    );

    $builder->join(
        'users creator',
        'creator.id = v.created_by',
        'left'
    );

    $builder->join(
        'users submitter',
        'submitter.id = v.submitted_by',
        'left'
    );

    /*
    |--------------------------------------------------------------------------
    | ID REPORT
    |--------------------------------------------------------------------------
    */
    $builder->where(
        'v.id',
        $id
    );

    /*
    |--------------------------------------------------------------------------
    | HANYA REPORT COMPLETED
    |--------------------------------------------------------------------------
    */
    $builder->where(
        'v.status',
        'COMPLETED'
    );

    /*
    |--------------------------------------------------------------------------
    | PETUGAS
    |--------------------------------------------------------------------------
    | Admin bisa melihat semua.
    | Petugas hanya bisa membuka report yang menjadi anggota timnya.
    */
    if (strtolower($userRole) !== 'admin') {

        $builder->join(
            'visit_team vt',
            'vt.visit_id = v.id',
            'inner'
        );

        $builder->where(
            'vt.user_id',
            (int) $userId
        );
    }

    $visit = $builder
        ->get()
        ->getRowArray();

    /*
    |--------------------------------------------------------------------------
    | REPORT TIDAK DITEMUKAN
    |--------------------------------------------------------------------------
    */
    if (!$visit) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | TEAM / PETUGAS
    |--------------------------------------------------------------------------
    */
    $visit['members'] = $this->getMembers(
        $id
    );

    $names = [];

    foreach ($visit['members'] as $member) {

        if (!empty($member['name'])) {
            $names[] = $member['name'];
        }
    }

    $visit['member_names'] = implode(
        ', ',
        $names
    );

    /*
    |--------------------------------------------------------------------------
    | AMBIL SEMUA JAWABAN
    |--------------------------------------------------------------------------
    */
    $allAnswers = $this->db
        ->table('visit_answers va')
        ->select('
            va.id,
            va.visit_id,
            va.question_id,
            va.answer,

            i.code,
            i.question,
            i.description,
            i.answer_type,
            i.options
        ')
        ->join(
            'instruments i',
            'i.id = va.question_id',
            'left'
        )
        ->where(
            'va.visit_id',
            $id
        )
        ->orderBy(
            'va.question_id',
            'ASC'
        )
        ->get()
        ->getResultArray();

    /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */
    $visit['answers'] = [];
    $visit['documents'] = [];
    $visit['photos'] = [];

    /*
    |--------------------------------------------------------------------------
    | PISAHKAN JAWABAN
    |--------------------------------------------------------------------------
    |
    | question_id 20 = dokumen
    | question_id 21 = foto
    |
    */
    foreach ($allAnswers as $answer) {

        $questionId = (int) $answer['question_id'];

        // DOKUMEN
        if ($questionId === 20) {

            if (!empty($answer['answer'])) {
                $visit['documents'][] = $answer;
            }

            continue;
        }

        // FOTO
        if ($questionId === 21) {

            if (!empty($answer['answer'])) {
                $visit['photos'][] = $answer;
            }

            continue;
        }

        // JAWABAN NORMAL
        $visit['answers'][] = $answer;
    }

    return $visit;
}

    protected function getMembers($visitId)
    {
        return $this->db->table('visit_team vt')
            ->select('u.id,u.name')
            ->join(
                'users u',
                'u.id=vt.user_id',
                'left'
            )
            ->where(
                'vt.visit_id',
                (int)$visitId
            )
            ->orderBy(
                'u.name',
                'ASC'
            )
            ->get()
            ->getResultArray();
    }

    public function getRegions()
    {
        return $this->db->table('region')
            ->select('id,name')
            ->orderBy(
                'name',
                'ASC'
            )
            ->get()
            ->getResultArray();
    }
}