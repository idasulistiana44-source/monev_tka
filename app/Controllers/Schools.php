<?php

namespace App\Controllers;

use App\Models\SchoolModel;
use App\Models\CityModel;
use App\Models\DistrictModel;

class Schools extends BaseController
{
    protected $schoolModel;
    protected $cityModel;
    protected $districtModel;

    public function __construct()
    {
        $this->schoolModel = new SchoolModel();
        $this->cityModel = new CityModel();
        $this->districtModel = new DistrictModel();
    }

    public function index()
    {
        return view('layout/template',[
            'title'=>'Schools',
            'pageName'=>'schools/index',
            'pageView'=>'schools/index',
            'pageAsset'=>'schools',
            'pageData'=>[],
            'schoolCss'=>true
        ]);
    }
    public function data()
    {
        try {
            $keyword = trim((string) $this->request->getGet('keyword'));
            $level = trim((string) $this->request->getGet('level'));

            $data = $this->schoolModel->getSchools($keyword, $level);

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ERROR LOAD SCHOOLS: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => []
                ]);
        }
    }

    public function city()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data' => $this->cityModel->getActive()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ERROR LOAD CITY: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => []
                ]);
        }
    }

    public function district()
    {
        try {
            $cityId = (int) $this->request->getGet('city_id');

            if ($cityId <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'City has not been selected.',
                    'data' => []
                ]);
            }

            $data = $this->districtModel->getByCity($cityId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ERROR LOAD DISTRICT: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => []
                ]);
        }
    }

    public function detail($id)
    {
        try {
            $data = $this->schoolModel->getSchool((int) $id);

            if (!$data) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'success' => false,
                        'message' => 'School data not found.'
                    ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ERROR SCHOOL DETAIL: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
        }
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(405)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid request.'
                ]);
        }

        $rules = [
            'npsn' => 'required|max_length[20]',
            'school_name' => 'required|max_length[150]',
            'city_id' => 'required|integer',
            'district_id' => 'required|integer',
            'level' => 'required|in_list[SMA,SMK]',
            'address' => 'permit_empty',
            'is_active' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Please check the school data again.',
                    'errors' => $this->validator->getErrors()
                ]);
        }

        $cityId = (int) $this->request->getPost('city_id');
        $districtId = (int) $this->request->getPost('district_id');

        $district = $this->districtModel->getWithRegion($districtId);

        if (!$district) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'District not found.'
                ]);
        }

        if ((int) $district['city_id'] !== $cityId) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'District does not belong to the selected city.'
                ]);
        }

        if (empty($district['region_id'])) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Region for this district has not been defined.'
                ]);
        }

        $npsn = trim((string) $this->request->getPost('npsn'));

        $existing = $this->schoolModel
            ->where('npsn', $npsn)
            ->first();

        if ($existing) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'NPSN is already registered.'
                ]);
        }

        $data = [
            'npsn' => $npsn,
            'school_name' => trim((string) $this->request->getPost('school_name')),
            'city_id' => $cityId,
            'district_id' => $districtId,
            'region_id' => (int) $district['region_id'],
            'level' => $this->request->getPost('level'),
            'address' => trim((string) $this->request->getPost('alamat')),
            'is_active' => (int) $this->request->getPost('is_active')
        ];

        $id = $this->schoolModel->insert($data);

        if (!$id) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'School data could not be saved.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'School successfully added.',
            'data' => $this->schoolModel->getSchool($id)
        ]);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(405)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid request.'
                ]);
        }

        $id = (int) $id;

        $school = $this->schoolModel->find($id);

        if (!$school) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'School data not found.'
                ]);
        }

        $rules = [
            'npsn' => 'required|max_length[20]',
            'school_name' => 'required|max_length[150]',
            'city_id' => 'required|integer',
            'district_id' => 'required|integer',
            'level' => 'required|in_list[SMA,SMK]',
            'address' => 'permit_empty',
            'is_active' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Please check the school data again.',
                    'errors' => $this->validator->getErrors()
                ]);
        }

        $cityId = (int) $this->request->getPost('city_id');
        $districtId = (int) $this->request->getPost('district_id');

        $district = $this->districtModel->getWithRegion($districtId);

        if (!$district) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'District not found.'
                ]);
        }

        if ((int) $district['city_id'] !== $cityId) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'District does not belong to the selected city.'
                ]);
        }

        if (empty($district['region_id'])) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Region for this district has not been defined.'
                ]);
        }

        $npsn = trim((string) $this->request->getPost('npsn'));

        $duplicate = $this->schoolModel
            ->where('npsn', $npsn)
            ->where('id !=', $id)
            ->first();

        if ($duplicate) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'NPSN is already used by another school.'
                ]);
        }

        $data = [
            'npsn' => $npsn,
            'school_name' => trim((string) $this->request->getPost('name')),
            'city_id' => $cityId,
            'district_id' => $districtId,
            'region_id' => (int) $district['region_id'],
            'level' => $this->request->getPost('level'),
            'address' => trim((string) $this->request->getPost('address')),
            'is_active' => (int) $this->request->getPost('is_active')
        ];

        if (!$this->schoolModel->update($id, $data)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'School data could not be updated.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'School data successfully updated.',
            'data' => $this->schoolModel->getSchool($id)
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(405)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid request.'
                ]);
        }

        $id = (int) $id;

        $school = $this->schoolModel->find($id);

        if (!$school) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'School data not found.'
                ]);
        }

        if (!$this->schoolModel->delete($id)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'School data could not be deleted.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'School successfully deleted.'
        ]);
    }
}