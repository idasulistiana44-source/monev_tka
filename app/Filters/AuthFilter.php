<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Cek apakah sudah login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // 2. Cek Hak Akses Role jika ada argument di Route
        if (!empty($arguments)) {
            $userRole = session()->get('role');
            
            // Jika role user tidak ada di daftar role yang dizinkan untuk route tersebut
            if (!in_array($userRole, $arguments)) {
                // Jika akses via AJAX
                if ($request->isAJAX()) {
                    return service('response')->setJSON([
                        'status'  => 'error',
                        'message' => 'Akses ditolak. Anda tidak memiliki izin.'
                    ])->setStatusCode(403);
                }

                // Jika akses via browser biasa, beri notifikasi atau lempar ke dashboard
                session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                return redirect()->to(base_url('reports'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu diisi
    }
}