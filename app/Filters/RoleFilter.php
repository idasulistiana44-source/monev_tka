<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Memeriksa hak akses user sebelum controller dijalankan
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session  = session();
        $userRole = strtolower((string) $session->get('role')); // Ambil role dari session

        // Jika belum login, abaikan (biarkan AuthFilter yang mengurus)
        if (! $session->get('isLoggedIn')) {
            return;
        }

        // Admin memiliki akses penuh ke seluruh rute
        if ($userRole === 'admin') {
            return;
        }

        // Jika rute membutuhkan role tertentu ($arguments), cek apakah role user ada di dalamnya
        if (! empty($arguments) && ! in_array($userRole, array_map('strtolower', $arguments), true)) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Anda tidak memiliki hak akses untuk membuka halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah request
    }
}