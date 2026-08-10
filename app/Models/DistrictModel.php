<?php

namespace App\Models;

use CodeIgniter\Model;

class DistrictModel extends Model
{
    protected $table = 'district';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'city_id',
        'region_id',
        'name',
        'is_active',
        'created_at',
        'updated_at'
    ];

    public function getByCity($cityId)
    {
        return $this->where('city_id', $cityId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getWithRegion($id)
    {
        return $this->select('district.*,region.name AS region_name')
            ->join('region','region.id=district.region_id','left')
            ->where('district.id',$id)
            ->first();
    }
}