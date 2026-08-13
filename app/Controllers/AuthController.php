<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    protected $authModel;
    protected $db;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->db        = \Config\Database::connect();
    }

    /**
     * Menampilkan Halaman Form Login (GET)
     */
    public function index()
    {
        // Jika sudah login, langsung alihkan ke visits
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('visits'));
        }

        // PERBAIKAN: Memanggil file di app/Views/auth/login.php
        return view('auth/login');
    }

   public function login()
    {
        $username = trim((string)$this->request->getPost('username'));
        $password = (string)$this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->response->setJSON([
                'status' => 'validation_error',
                'errors' => [
                    'username' => empty($username) ? 'Username tidak boleh kosong' : '',
                    'password' => empty($password) ? 'Password tidak boleh kosong' : '',
                ]
            ]);
        }

        $user = $this->db->table('users')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if (!$user || !password_verify($password, $user['password'])) {
            // Mengembalikan status error tanpa HTTP 401 agar ditangkap SweetAlert
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Username atau Password salah!'
            ]);
        }

        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'fullname'   => $user['fullname'] ?? $user['name'] ?? '',
            'role'       => strtolower((string)$user['role']),
            'isLoggedIn' => true,
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Login berhasil!',
            'redirect' => base_url('visits')
        ]);
    }

    public function processLogin()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $rules = [
            'login'    => 'required',
            'password' => 'required'
        ];

        $messages = [
            'login'    => ['required' => 'Username harus diisi.'],
            'password' => ['required' => 'Password harus diisi.']
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'validation_error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $username = $this->request->getPost('login');
        $password = $this->request->getPost('password');

        $user = $this->authModel->getUserByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Username atau Password salah!'
            ]);
        }

        $sessionData = [
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'name'       => $user['name'],
            'role'       => strtolower($user['role']),
            'isLoggedIn' => true
        ];
        session()->set($sessionData);

        return redirect()->to(base_url('dashboard'))->with('success', 'Login berhasil! Selamat datang kembali.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}