<?php

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return view('layout/template',[
            'title'=>'Users',
            'pageName'=>'users/index',
            'pageView'=>'users/index',
            'pageAsset'=>'users',
            'pageData'=>[]
        ]);
    }

    public function data()
    {
        $users = $this->userModel->getUsers();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role'],
                'is_active' => (int) $user['is_active'],
                'created_at' => $user['created_at']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
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

        $name = trim((string) $this->request->getPost('name'));
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $role = (string) $this->request->getPost('role');
        $isActive = (string) $this->request->getPost('is_active');

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

        if ($errors) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Periksa kembali data yang diisi.',
                'data' => $errors
            ]);
        }

        $data = [
            'name' => $name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => (int) $isActive
        ];

        $id = $this->userModel->createUser($data);

        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'User gagal ditambahkan.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'User berhasil ditambahkan.',
            'data' => [
                'id' => $id
            ]
        ]);
    }

    public function update()
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
                'success' => false,
                'message' => 'ID user tidak valid.'
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }

        $name = trim((string) $this->request->getPost('name'));
        $username = trim((string) $this->request->getPost('username'));
        $role = (string) $this->request->getPost('role');
        $isActive = (string) $this->request->getPost('is_active');

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Nama wajib diisi.';
        }

        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username minimal 3 karakter.';
        } elseif ($this->userModel->usernameExists($username, $id)) {
            $errors['username'] = 'Username sudah digunakan.';
        }

        if (!in_array($role, ['admin', 'petugas'], true)) {
            $errors['role'] = 'Role tidak valid.';
        }

        if (!in_array($isActive, ['0', '1'], true)) {
            $errors['is_active'] = 'Status tidak valid.';
        }

        if ($errors) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Periksa kembali data yang diisi.',
                'data' => $errors
            ]);
        }

        $result = $this->userModel->updateUser($id, [
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'is_active' => (int) $isActive
        ]);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'User gagal diperbarui.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'User berhasil diperbarui.'
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
                'success' => false,
                'message' => 'ID user tidak valid.'
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }

        $password = (string) $this->request->getPost('password');

        if ($password === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Password baru wajib diisi.',
                'data' => [
                    'password' => 'Password baru wajib diisi.'
                ]
            ]);
        }

        if (strlen($password) < 6) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Password minimal 6 karakter.',
                'data' => [
                    'password' => 'Password minimal 6 karakter.'
                ]
            ]);
        }

        $result = $this->userModel->updatePassword($id, $password);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Password gagal direset.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password berhasil direset.'
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
                'success' => false,
                'message' => 'ID user tidak valid.'
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }

        $newStatus = $user['is_active'] == 1 ? 0 : 1;

        $result = $this->userModel->updateStatus($id, $newStatus);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Status user gagal diperbarui.'
            ]);
        }

        $message = $newStatus == 1
            ? 'User berhasil diaktifkan.'
            : 'User berhasil dinonaktifkan.';

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $id,
                'is_active' => $newStatus
            ]
        ]);
    }

    public function delete()
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
                'success' => false,
                'message' => 'ID user tidak valid.'
            ]);
        }

        $user = $this->userModel->getUser($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }

        $result = $this->userModel->deleteUser($id);

        if (!$result) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'User gagal dihapus.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ]);
    }
}