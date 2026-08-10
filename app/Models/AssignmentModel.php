<?php
namespace App\Models;
use CodeIgniter\Model;
class AssignmentModel extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['school_id','user_id','assignment_date','status','notes'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    public function getAssignments($keyword = '', $status = '')
    {
        $builder = $this->db->table('assignments a');
        $builder->select('a.id,a.school_id,a.user_id,a.assignment_date,a.status,a.notes,a.created_at,a.updated_at,s.npsn,s.school_name,s.level,s.city_id,s.district_id,s.region_id,c.name AS city_name,d.name AS district_name,r.name AS region_name,u.name AS user_name');
        $builder->join('schools s','s.id = a.school_id','left');
        $builder->join('city c','c.id = s.city_id','left');
        $builder->join('district d','d.id = s.district_id','left');
        $builder->join('region r','r.id = s.region_id','left');
        $builder->join('users u','u.id = a.user_id','left');

        if($keyword !== ''){
            $builder->groupStart();
            $builder->like('s.school_name',$keyword);
            $builder->orLike('s.npsn',$keyword);
            $builder->orLike('u.name',$keyword);
            $builder->orLike('c.name',$keyword);
            $builder->orLike('d.name',$keyword);
            $builder->orLike('r.name',$keyword);
            $builder->groupEnd();
        }

        if($status !== ''){
            $builder->where('a.status',$status);
        }

        return $builder
            ->orderBy('a.assignment_date','DESC')
            ->orderBy('a.id','DESC')
            ->get()
            ->getResultArray();
    }

    public function getAssignment($id)
    {
        return $this->db->table('assignments a')
            ->select('a.*,s.npsn,s.school_name,s.level,s.city_id,s.district_id,s.region_id,c.name AS city_name,d.name AS district_name,r.name AS region_name,u.name AS user_name')
            ->join('schools s','s.id = a.school_id','left')
            ->join('city c','c.id = s.city_id','left')
            ->join('district d','d.id = s.district_id','left')
            ->join('region r','r.id = s.region_id','left')
            ->join('users u','u.id = a.user_id','left')
            ->where('a.id',$id)
            ->get()
            ->getRowArray();
    }

    public function getSchools()
    {
        return $this->db->table('schools s')
            ->select('s.id,s.npsn,s.school_name,s.level,s.city_id,s.district_id,s.region_id,c.name AS city_name,d.name AS district_name,r.name AS region_name')
            ->join('city c','c.id = s.city_id','left')
            ->join('district d','d.id = s.district_id','left')
            ->join('region r','r.id = s.region_id','left')
            ->where('s.is_active',1)
            ->orderBy('s.school_name','ASC')
            ->get()
            ->getResultArray();
    }

    public function getUsers()
    {
        return $this->db->table('users')
            ->select('id,name,username')
            ->where('is_active',1)
            ->orderBy('name','ASC')
            ->get()
            ->getResultArray();
    }

    public function hasActiveAssignment($schoolId,$exceptId = null)
    {
        $builder = $this->where('school_id',$schoolId)->where('status','ACTIVE');

        if($exceptId !== null){
            $builder->where('id !=',$exceptId);
        }

        return $builder->countAllResults() > 0;
    }
}