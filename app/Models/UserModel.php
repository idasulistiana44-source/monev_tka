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
        'institution',
        'region_id'
    ];

    public function getUsers()
    {
        return $this->db->table($this->table)
            ->select('users.*, region.name as region_name')
            ->join('region', 'region.id = users.region_id', 'left')
            ->get()
            ->getResultArray();
    }

    public function getUser($id)
    {
        return $this->db->table($this->table)
            ->select('users.*, region.name as region_name')
            ->join('region', 'region.id = users.region_id', 'left')
            ->where('users.id', $id)
            ->get()
            ->getRowArray();
    }

    public function createUser($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID();
    }

    public function updateUser($id, $data)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    public function usernameExists($username, $excludeId = null)
    {
        $builder = $this->db->table($this->table)->where('username', $username);
        
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function updatePassword($id, $password)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function updateStatus($id, $status)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['is_active' => $status]);
    }

    public function deleteUser($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }
}