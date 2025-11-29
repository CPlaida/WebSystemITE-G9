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

    /**
     * Get doctor performance report
     */
    public function getPerformanceReport(array $filters = []): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('users u');
        $builder->select('u.id, u.username, u.email');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->where('u.status', 'active');

        if (!empty($filters['doctor_id'])) {
            $builder->where('u.id', $filters['doctor_id']);
        }

        $doctors = $builder->get()->getResultArray();

        $performance = [];
        foreach ($doctors as $doctor) {
            $doctorId = $doctor['id'];

            // Count appointments
            $aptBuilder = $db->table('appointments');
            $aptBuilder->where('doctor_id', $doctorId);
            if (!empty($filters['start_date'])) {
                $aptBuilder->where('appointment_date >=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $aptBuilder->where('appointment_date <=', $filters['end_date']);
            }
            $appointments = $aptBuilder->countAllResults();

            // Count prescriptions
            $presBuilder = $db->table('prescriptions');
            if (!empty($filters['start_date'])) {
                $presBuilder->where('date >=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $presBuilder->where('date <=', $filters['end_date']);
            }
            $prescriptions = $presBuilder->countAllResults();

            // Calculate revenue
            $revBuilder = $db->table('billing b');
            $revBuilder->selectSum('b.final_amount', 'revenue');
            $revBuilder->join('appointments a', 'a.patient_id = b.patient_id', 'left');
            $revBuilder->where('a.doctor_id', $doctorId);
            $revBuilder->where('b.payment_status', 'paid');
            if (!empty($filters['start_date'])) {
                $revBuilder->where('b.bill_date >=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $revBuilder->where('b.bill_date <=', $filters['end_date']);
            }
            $revenueResult = $revBuilder->get()->getRowArray();
            $revenue = (float)($revenueResult['revenue'] ?? 0);

            $performance[] = [
                'doctor_id' => $doctorId,
                'doctor_name' => $doctor['username'],
                'appointments' => $appointments,
                'prescriptions' => $prescriptions,
                'revenue' => $revenue,
            ];
        }

        return [
            'performance' => $performance,
        ];
    }
}
