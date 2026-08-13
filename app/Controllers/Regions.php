<?php

namespace App\Controllers;

use App\Models\RegionModel;

class Regions extends BaseController
{
    protected $regionModel;

    public function __construct()
    {
        $this->regionModel = new RegionModel();
    }

   public function data()
    {
        $regions = $this->regionModel
                        ->select('id, name')
                        ->where('is_active', 1)
                        // Parameter false kedua mencegah CI4 melakukan auto-quoting/backticks
                        ->orderBy('FIELD(id, 10)', 'DESC', false) 
                        ->orderBy('id', 'ASC')
                        ->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'data'     => $regions,
            'csrfHash' => csrf_hash()
        ]);
    }
}