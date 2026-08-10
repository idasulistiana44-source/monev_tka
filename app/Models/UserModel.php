<?php

namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    public function getUsers()
    {
        return $this->orderBy('id', 'DESC')->findAll();
    }
    public function getUser($id)
    {
        return $this->find($id);
    }
    public function usernameExists($username, $exceptId = null)
    {
        $builder = $this->where('username', $username);
        if($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }
        return $builder->countAllResults() > 0;
    }
    public function createUser(array $data)
    {
        return $this->insert($data);
    }
    public function updateUser($id, array $data)
    {
        return $this->update($id, $data);
    }
    public function deleteUser($id)
    {
        return $this->delete($id);
    }
    public function updatePassword($id, $password)
    {
        return $this->update($id, [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
    public function updateStatus($id, $status)
    {
        return $this->update($id, [
            'is_active' => $status
        ]);
    }
}