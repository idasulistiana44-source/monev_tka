<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolModel extends Model
{
    protected $table = 'schools';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'npsn',
        'school_name',
        'city_id',
        'district_id',
        'region_id',
        'level',
        'alamat',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getSchools($keyword = '', $level = '')
    {
        $builder = $this->select('schools.*,city.name AS city_name,district.name AS district_name,region.name AS region_name')
            ->join('city','city.id=schools.city_id','left')
            ->join('district','district.id=schools.district_id','left')
            ->join('region','region.id=schools.region_id','left')
            ->orderBy('schools.school_name','ASC');

        if($keyword !== ''){
            $builder->groupStart()
                ->like('schools.npsn',$keyword)
                ->orLike('schools.school_name',$keyword)
                ->groupEnd();
        }

        if($level !== ''){
            $builder->where('schools.level',$level);
        }

        return $builder->findAll();
    }

    public function getSchool($id)
    {
        return $this->select('schools.*,city.name AS city_name,district.name AS district_name,region.name AS region_name')
            ->join('city','city.id=schools.city_id','left')
            ->join('district','district.id=schools.district_id','left')
            ->join('region','region.id=schools.region_id','left')
            ->where('schools.id',$id)
            ->first();
    }
}