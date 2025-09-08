<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'license_number',
        'department',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name' => 'required|min_length[2]|max_length[50]',
        'email' => 'required|valid_email|is_unique[doctors.email]',
        'phone' => 'permit_empty|min_length[10]|max_length[15]',
        'specialization' => 'required|min_length[3]|max_length[100]',
        'license_number' => 'permit_empty|max_length[50]',
        'department' => 'permit_empty|max_length[100]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required',
            'min_length' => 'First name must be at least 2 characters long',
            'max_length' => 'First name cannot exceed 50 characters'
        ],
        'last_name' => [
            'required' => 'Last name is required',
            'min_length' => 'Last name must be at least 2 characters long',
            'max_length' => 'Last name cannot exceed 50 characters'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'specialization' => [
            'required' => 'Specialization is required',
            'min_length' => 'Specialization must be at least 3 characters long'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active doctors
     */
    public function getActiveDoctors()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get doctors by specialization
     */
    public function getDoctorsBySpecialization($specialization)
    {
        return $this->where('specialization', $specialization)
                    ->where('status', 'active')
                    ->findAll();
    }

    /**
     * Get doctor with full name
     */
    public function getDoctorsWithFullName()
    {
        return $this->select('id, CONCAT(first_name, " ", last_name) as full_name, specialization, status')
                    ->findAll();
    }

    /**
     * Search doctors
     */
    public function searchDoctors($searchTerm)
    {
        return $this->groupStart()
                    ->like('first_name', $searchTerm)
                    ->orLike('last_name', $searchTerm)
                    ->orLike('specialization', $searchTerm)
                    ->orLike('department', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }
}
