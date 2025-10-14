<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'patient_id', 
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'reason',
        'status',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'patient_id' => 'required|integer',
        'doctor_id' => 'required|integer',
        'appointment_date' => 'required|valid_date',
        'appointment_time' => 'required',
        'appointment_type' => 'required|in_list[consultation,follow_up,emergency,routine_checkup]',
        'status' => 'in_list[scheduled,confirmed,in_progress,completed,cancelled,no_show]'
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient ID is required',
            'integer' => 'Patient ID must be a valid number'
        ],
        'doctor_id' => [
            'required' => 'Doctor ID is required',
            'integer' => 'Doctor ID must be a valid number'
        ],
        'appointment_date' => [
            'required' => 'Appointment date is required',
            'valid_date' => 'Please provide a valid date'
        ],
        'appointment_time' => [
            'required' => 'Appointment time is required'
        ],
        'appointment_type' => [
            'required' => 'Appointment type is required',
            'in_list' => 'Invalid appointment type selected'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks (none needed when using only numeric id)
    protected $allowCallbacks = true;

    /**
     * Get all appointments with patient and doctor details
     */
    public function getAppointmentsWithDetails($limit = null, $offset = null)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         p.phone as patient_phone, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->orderBy('a.appointment_date', 'DESC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get appointments by date range
     */
    public function getAppointmentsByDateRange($startDate, $endDate)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->where('a.appointment_date >=', $startDate);
        $builder->where('a.appointment_date <=', $endDate);
        $builder->orderBy('a.appointment_date', 'ASC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get appointments by doctor
     */
    public function getAppointmentsByDoctor($doctorId, $date = null)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         p.phone as patient_phone');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->where('a.doctor_id', $doctorId);
        
        if ($date) {
            $builder->where('a.appointment_date', $date);
        }
        
        $builder->orderBy('a.appointment_date', 'ASC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get appointments by patient
     */
    public function getAppointmentsByPatient($patientId)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->where('a.patient_id', $patientId);
        $builder->orderBy('a.appointment_date', 'DESC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get appointments by status
     */
    public function getAppointmentsByStatus($status)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->where('a.status', $status);
        $builder->orderBy('a.appointment_date', 'ASC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get today's appointments
     */
    public function getTodaysAppointments()
    {
        return $this->getAppointmentsByDateRange(date('Y-m-d'), date('Y-m-d'));
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments($limit = 10)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        $builder->where('a.appointment_date >=', date('Y-m-d'));
        $builder->whereIn('a.status', ['scheduled', 'confirmed']);
        $builder->orderBy('a.appointment_date', 'ASC');
        $builder->orderBy('a.appointment_time', 'ASC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Check for appointment conflicts
     */
    public function checkAppointmentConflict($doctorId, $date, $time, $excludeId = null)
    {
        $builder = $this->db->table('appointments');
        $builder->where('doctor_id', $doctorId);
        $builder->where('appointment_date', $date);
        $builder->where('appointment_time', $time);
        $builder->whereNotIn('status', ['cancelled', 'no_show']);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus($appointmentId, $status, $notes = null)
    {
        $data = ['status' => $status];
        
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        return $this->update($appointmentId, $data);
    }

    /**
     * Get appointment statistics
     */
    public function getAppointmentStats($startDate = null, $endDate = null)
    {
        $builder = $this->db->table('appointments');
        
        if ($startDate && $endDate) {
            $builder->where('appointment_date >=', $startDate);
            $builder->where('appointment_date <=', $endDate);
        }
        
        $stats = [
            'total' => $builder->countAllResults('', false),
            'scheduled' => $builder->where('status', 'scheduled')->countAllResults('', false),
            'confirmed' => $builder->where('status', 'confirmed')->countAllResults('', false),
            'completed' => $builder->where('status', 'completed')->countAllResults('', false),
            'cancelled' => $builder->where('status', 'cancelled')->countAllResults('', false),
            'no_show' => $builder->where('status', 'no_show')->countAllResults('')
        ];
        
        return $stats;
    }

    /**
     * Search appointments
     */
    public function searchAppointments($searchTerm)
    {
        $builder = $this->db->table('appointments a');
        $builder->select('a.*, 
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name, 
                         u.username as doctor_name, 
                         u.email as doctor_email');
        $builder->join('patients p', 'a.patient_id = p.id', 'left');
        $builder->join('users u', 'a.doctor_id = u.id', 'left');
        $builder->join('roles r', 'u.role_id = r.id', 'left');
        $builder->where('r.name', 'doctor');
        
        $builder->groupStart();
        $builder->like('a.id', $searchTerm);
        $builder->orLike('p.first_name', $searchTerm);
        $builder->orLike('p.last_name', $searchTerm);
        $builder->orLike('u.username', $searchTerm);
        $builder->orLike('u.email', $searchTerm);
        $builder->orLike('a.reason', $searchTerm);
        $builder->groupEnd();
        
        $builder->orderBy('a.appointment_date', 'DESC');
        $builder->orderBy('a.appointment_time', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}
