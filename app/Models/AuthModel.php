<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['username', 'password', 'name', 'role', 'is_active'];

    /**
     * Cari user berdasarkan username yang statusnya aktif
     */
    public function getUserByUsername(string $username)
    {
        return $this->where('is_active', 1)
                    ->where('username', $username)
                    ->first();
    }
}