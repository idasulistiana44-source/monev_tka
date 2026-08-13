<?php

namespace App\Models;

use CodeIgniter\Model;

class RegionModel extends Model
{
    protected $table            = 'region'; // Sesuaikan dengan nama tabel di database kamu (misal: 'regions' atau 'region')
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name']; // Sesuaikan dengan kolom nama region di database kamu
    protected $returnType       = 'array';

    public function getAllRegions()
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}