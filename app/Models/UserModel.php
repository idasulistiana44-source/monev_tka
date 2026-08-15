<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'institution'
    ];

    /**
     * Ambil semua user beserta multiple wilayah verifikasi
     */
    public function getUsers()
    {
        $users = $this->db->table($this->table)
            ->select('users.*')
            ->orderBy('users.created_at', 'DESC')
            ->orderBy('users.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($users as &$user) {
            $regions = $this->db->table('user_regions ur')
                ->select('ur.region_id, r.name as region_name')
                ->join('region r', 'r.id = ur.region_id', 'left')
                ->where('ur.user_id', $user['id'])
                ->orderBy('ur.region_id', 'ASC')
                ->get()
                ->getResultArray();

            $user['region_ids'] = array_column($regions, 'region_id');
            $user['region_names'] = array_column($regions, 'region_name');

            $user['region_name'] = !empty($user['region_names'])
                ? implode(', ', $user['region_names'])
                : '-';
        }

        return $users;
    }

    /**
     * Ambil satu user beserta multiple wilayah verifikasi
     */
    public function getUser($id)
    {
        $user = $this->db->table($this->table)
            ->select('users.*')
            ->where('users.id', $id)
            ->get()
            ->getRowArray();

        if (!$user) {
            return null;
        }

        $regions = $this->db->table('user_regions ur')
            ->select('ur.region_id, r.name as region_name')
            ->join('region r', 'r.id = ur.region_id', 'left')
            ->where('ur.user_id', $id)
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        $user['region_ids'] = array_map(
            'intval',
            array_column($regions, 'region_id')
        );

        $user['region_names'] = array_column(
            $regions,
            'region_name'
        );

        $user['region_name'] = !empty($user['region_names'])
            ? implode(', ', $user['region_names'])
            : '-';

        return $user;
    }

    /**
     * Simpan user baru
     */
    public function createUser($data)
    {
        $this->db->table($this->table)->insert($data);

        return $this->db->insertID();
    }

    /**
     * Update data user
     */
    public function updateUser($id, $data)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Cek username
     */
    public function usernameExists($username, $excludeId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('username', $username);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Update password
     */
    public function updatePassword($id, $password)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update([
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
    }

    /**
     * Update status
     */
    public function updateStatus($id, $status)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update([
                'is_active' => $status
            ]);
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    /**
     * Ambil region milik user
     */
    public function getUserRegions($userId)
    {
        return $this->db->table('user_regions ur')
            ->select('ur.region_id, r.name as region_name')
            ->join('region r', 'r.id = ur.region_id', 'left')
            ->where('ur.user_id', $userId)
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Simpan multiple region untuk user
     */
    public function syncUserRegions($userId, array $regionIds)
    {
        $builder = $this->db->table('user_regions');

        // HAPUS SEMUA WILAYAH LAMA USER
        $builder->where('user_id', $userId)->delete();

        // Bersihkan data wilayah baru
        $regionIds = array_values(array_unique(array_filter(
            array_map('intval', $regionIds),
            fn($id) => $id > 0
        )));

        // Kalau tidak ada wilayah baru
        if (empty($regionIds)) {
            return true;
        }

        $now = date('Y-m-d H:i:s');

        $rows = [];

        foreach ($regionIds as $regionId) {
            $rows[] = [
                'user_id'    => $userId,
                'region_id'  => $regionId,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        return $builder->insertBatch($rows);
    }
}