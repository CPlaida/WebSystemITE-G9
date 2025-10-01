<?php

namespace App\Models;

use CodeIgniter\Model;

class LabRequestModel extends Model
{
    protected $table = 'laboratory';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'test_id', 'doctor_id', 'test_name', 'test_type', 
        'test_date', 'test_time', 'test_results', 'normal_range', 'status', 
        'cost', 'notes', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'test_name' => 'required|min_length[2]|max_length[100]',
        'test_type' => 'required|min_length[2]|max_length[100]',
        'test_date' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'test_name' => [
            'required' => 'Test name is required',
            'min_length' => 'Test name must be at least 2 characters',
            'max_length' => 'Test name cannot exceed 100 characters'
        ],
        'test_type' => [
            'required' => 'Test type is required'
        ]
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate unique request ID
     */
    public function generateRequestId()
    {
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'LR' . $date . $random;
    }

    /**
     * Create new lab request
     */
    public function createRequest($data)
    {
        $data['request_id'] = $this->generateRequestId();
        $data['status'] = 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $insertId = $this->insert($data);
        
        if ($insertId) {
            return $this->find($insertId);
        }
        
        return false;
    }

    /**
     * Get lab requests with filters
     */
    public function getRequests($filters = [])
    {
        $builder = $this->builder();
        
        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (isset($filters['test_type'])) {
            $builder->like('test_type', $filters['test_type']);
        }
        
        if (isset($filters['patient_name'])) {
            $builder->like('test_name', $filters['patient_name']); 
        }
        
        if (isset($filters['date_from'])) {
            $builder->where('test_date >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('test_date <=', $filters['date_to']);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests()
    {
        return $this->where('status', 'pending')
                   ->orderBy('priority', 'DESC')
                   ->orderBy('test_date', 'ASC')
                   ->findAll();
    }

    /**
     * Get urgent requests
     */
    public function getUrgentRequests()
    {
        return $this->where('priority', 'urgent')
                   ->orWhere('priority', 'stat')
                   ->orderBy('test_date', 'ASC')
                   ->findAll();
    }

    /**
     * Get today's requests
     */
    public function getTodaysRequests()
    {
        return $this->where('test_date', date('Y-m-d'))
                   ->orderBy('priority', 'DESC')
                   ->findAll();
    }

    /**
     * Update request status
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Search requests
     */
    public function searchRequests($query)
    {
        return $this->groupStart()
                   ->like('test_name', $query)
                   ->orLike('request_id', $query)
                   ->orLike('test_type', $query)
                   ->groupEnd()
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get statistics
     */
    public function getStats()
    {
        $stats = [];
        
        $stats['total'] = $this->countAll();
        $stats['pending'] = $this->where('status', 'pending')->countAllResults(false);
        $stats['completed'] = $this->where('status', 'completed')->countAllResults(false);
        $stats['in_progress'] = $this->where('status', 'in_progress')->countAllResults(false);
        $stats['urgent'] = $this->where('priority', 'urgent')->orWhere('priority', 'stat')->countAllResults(false);
        $stats['today'] = $this->where('test_date', date('Y-m-d'))->countAllResults(false);
        
        return $stats;
    }
}
