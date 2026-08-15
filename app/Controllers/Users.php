<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RegionModel;

class Users extends BaseController
{
    protected $userModel;
    protected $regionModel;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->regionModel = new RegionModel();
    }

    public function index()
    {
        $regions = $this->regionModel->findAll();

        return view('layout/template', [
            'title'     => 'Users',
            'pageName'  => 'users/index',
            'pageView'  => 'users/index',
            'pageAsset' => 'users',
            'pageData'  => [
                'regions' => $regions
            ]
        ]);
    }

    public function data()
    {
        // Mengambil data user beserta multiple region
        $users = $this->userModel->getUsers();
        $data  = [];

        foreach ($users as $user) {
            $data[] = [
                'id'           => $user['id'],
                'name'         => $user['name'],
                'username'     => $user['username'],
                'role'         => $user['role'],
                'is_active'    => (int) $user['is_active'],
                'institution'  => $user['institution'] ?? '',

                // Multiple region
                'region_ids'   => $user['region_ids'] ?? [],
                'region_names' => $user['region_names'] ?? [],
                'region_name'  => $user['region_name'] ?? '-',

                // Tetap dikirim untuk kompatibilitas sementara
                'region_id'    => $user['region_id'] ?? null,

                'created_at'   => $user['created_at']
            ];
        }

        return $this->response->setJSON([
            'success'  => true,
            'data'     => $data,
            'csrfHash' => csrf_hash()
        ]);
    }

    public function store()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method tidak diizinkan.'
            ]);
        }

        $name        = trim((string) $this->request->getPost('name'));
        $username    = trim((string) $this->request->getPost('username'));
        $password    = (string) $this->request->getPost('password');
        $role        = (string) $this->request->getPost('role');
        $isActive    = (string) $this->request->getPost('is_active');
        $institution = trim((string) $this->request->getPost('institution'));

        // Multiple region
        $regionIds = $this->request->getPost('region_id');

        if (!is_array($regionIds)) {
            $regionIds = !empty($regionIds)
                ? [$regionIds]
                : [];
        }

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Nama wajib diisi.';
        }

        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username minimal 3 karakter.';
        } elseif ($this->userModel->usernameExists($username)) {
            $errors['username'] = 'Username sudah digunakan.';
        }

        if ($password === '') {
            $errors['password'] = 'Password wajib diisi.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        }

        if (!in_array($role, ['admin', 'petugas'], true)) {
            $errors['role'] = 'Role tidak valid.';
        }

        if (!in_array($isActive, ['0', '1'], true)) {
            $errors['is_active'] = 'Status tidak valid.';
        }

        if ($institution === '') {
            $errors['institution'] = 'Institusi wajib diisi.';
        }

        // Validasi multiple region
        $regionIds = array_values(array_unique(array_filter(
            array_map('intval', $regionIds),
            fn($id) => $id > 0
        )));

        if (empty($regionIds)) {
            $errors['region_id'] = 'Wilayah Verifikasi wajib dipilih.';
        }

        if ($errors) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'Periksa kembali data yang diisi.',
                'data'     => $errors,
                'csrfHash' => csrf_hash()
            ]);
        }

        /*
         * region_id lama tetap disimpan untuk sementara.
         * Kita gunakan region pertama sebagai nilai kompatibilitas.
         */
        $data = [
            'name'        => $name,
            'username'    => $username,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'role'        => $role,
            'is_active'   => (int) $isActive,
            'institution' => $institution
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $id = $this->userModel->createUser($data);

        if (!$id) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'User gagal ditambahkan.',
                'csrfHash' => csrf_hash()
            ]);
        }

        // Simpan semua wilayah ke user_regions
        $regionsSaved = $this->userModel->syncUserRegions(
            $id,
            $regionIds
        );

        if (!$regionsSaved) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'Wilayah user gagal disimpan.',
                'csrfHash' => csrf_hash()
            ]);
        }

       $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'User gagal ditambahkan.',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'User berhasil ditambahkan.',
            'data'     => [
                'id'         => $id,
                'region_ids' => $regionIds
            ],
            'csrfHash' => csrf_hash()
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');

        $rules = [
            'id'          => 'required|is_natural_no_zero',
            'name'        => 'required|min_length[3]',
            'username'    => 'required|min_length[3]',
            'institution' => 'required',
            'role'        => 'required',
            'is_active'   => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'message'  => 'Validasi gagal.',
                'errors'   => $this->validator->getErrors(),
                'csrfHash' => csrf_hash()
            ]);
        }

        // Multiple region
        $regionIds = $this->request->getPost('region_id');

        if (!is_array($regionIds)) {
            $regionIds = !empty($regionIds)
                ? [$regionIds]
                : [];
        }

        $regionIds = array_values(array_unique(array_filter(
            array_map('intval', $regionIds),
            fn($regionId) => $regionId > 0
        )));

        if (empty($regionIds)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'Wilayah Verifikasi wajib dipilih.',
                'errors'   => [
                    'region_id' => 'Wilayah Verifikasi wajib dipilih.'
                ],
                'csrfHash' => csrf_hash()
            ]);
        }

        $data = [
            'name'        => trim((string) $this->request->getPost('name')),
            'role'        => $this->request->getPost('role'),
            'username'    => trim((string) $this->request->getPost('username')),
            'is_active'   => (int) $this->request->getPost('is_active'),
            'institution' => trim((string) $this->request->getPost('institution'))
        ];

            $db = \Config\Database::connect();
            $db->transStart();

        $updated = $this->userModel->updateUser($id, $data);

        if (!$updated) {
            $db->transRollback();

            return $this->response->setJSON([
                'success'  => false,
                'message'  => 'Gagal memperbarui data user.',
                'csrfHash' => csrf_hash()
            ]);
        }

        // Sinkronkan multiple region
        $regionsSaved = $this->userModel->syncUserRegions(
            $id,
            $regionIds
        );

        if (!$regionsSaved) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'Wilayah user gagal diperbarui.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success'  => false,
                'message'  => 'Gagal memperbarui data user.',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Data user berhasil diperbarui.',
            'data'     => [
                'id'         => $id,
                'region_ids' => $regionIds
            ],
            'csrfHash' => csrf_hash()
        ]);
    }

    public function resetPassword()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');

        if (!$id || !is_numeric($id)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'ID user tidak valid.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'  => false,
                'message'  => 'User tidak ditemukan.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $password = (string) $this->request->getPost('password');

        if ($password === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'Password baru wajib diisi.',
                'data'     => ['password' => 'Password baru wajib diisi.'],
                'csrfHash' => csrf_hash()
            ]);
        }

        if (strlen($password) < 6) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'Password minimal 6 karakter.',
                'data'     => ['password' => 'Password minimal 6 karakter.'],
                'csrfHash' => csrf_hash()
            ]);
        }

        $result = $this->userModel->updatePassword($id, $password);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'Password gagal direset.',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Password berhasil direset.',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function toggleStatus()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');

        if (!$id || !is_numeric($id)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'message'  => 'ID user tidak valid.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'  => false,
                'message'  => 'User tidak ditemukan.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $newStatus = $user['is_active'] == 1 ? 0 : 1;
        $result    = $this->userModel->updateStatus($id, $newStatus);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'message'  => 'Status user gagal diperbarui.',
                'csrfHash' => csrf_hash()
            ]);
        }

        $message = $newStatus == 1
            ? 'User berhasil diaktifkan.'
            : 'User berhasil dinonaktifkan.';

        return $this->response->setJSON([
            'success'  => true,
            'message'  => $message,
            'data'     => [
                'id'        => $id,
                'is_active' => $newStatus
            ],
            'csrfHash' => csrf_hash()
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        if ($id && $this->userModel->deleteUser($id)) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'User berhasil dihapus',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'success'  => false,
            'message'  => 'Gagal menghapus user',
            'csrfHash' => csrf_hash()
        ]);
    }
}