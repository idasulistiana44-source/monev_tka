<?php

namespace App\Controllers;

use App\Models\AuthModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    protected $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('reports'));
        }

        // UBAH BARIS INI: Panggil view auth/login secara langsung
        return view('auth/login', [
            'title' => 'Login - Sistem Monev TKA'
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