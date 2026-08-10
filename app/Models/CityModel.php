<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table = 'city';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'is_active',
        'created_at',
        'updated_at'
    ];

    public function getActive()
    {
        return $this->where('is_active',1)
            ->orderBy('name','ASC')
            ->findAll();
    }
}