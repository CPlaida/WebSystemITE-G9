<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username', 'email', 'password', 'role', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all doctors
     */
    public function getAllDoctors()
    {
        return $this->where('role', 'doctor')
                    ->where('status', 'active')
                    ->findAll();
    }

    /**
     * Get doctor by ID
     */
    public function getDoctor($id)
    {
        return $this->where('id', $id)
                    ->where('role', 'doctor')
                    ->first();
    }

    /**
     * Get active doctors
     */
    public function getActiveDoctors()
    {
        return $this->getAllDoctors();
    }

    /**
     * Search doctors by username or email
     */
    public function searchDoctors($searchTerm)
    {
        return $this->where('role', 'doctor')
                    ->groupStart()
                    ->like('username', $searchTerm)
                    ->orLike('email', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }
}
