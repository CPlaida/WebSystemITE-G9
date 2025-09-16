<?php

namespace App\Models;

use CodeIgniter\Model;

class TestResultModel extends Model
{
    protected $table = 'test_results';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'result_id', 'request_id', 'patient_name', 'test_type', 'test_date',
        'result_data', 'normal_ranges', 'abnormal_flags', 'interpretation',
        'technician_notes', 'verified_by', 'verified_at', 'status',
        'critical_values', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'request_id' => 'required',
        'patient_name' => 'required|min_length[2]|max_length[100]',
        'test_type' => 'required|min_length[2]|max_length[100]',
        'test_date' => 'required|valid_date',
        'status' => 'required|in_list[pending,completed,verified,released]'
    ];

    protected $validationMessages = [
        'request_id' => [
            'required' => 'Request ID is required'
        ],
        'patient_name' => [
            'required' => 'Patient name is required'
        ],
        'test_type' => [
            'required' => 'Test type is required'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, completed, verified, or released'
        ]
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate unique result ID
     */
    public function generateResultId()
    {
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'TR' . $date . $random;
    }

    /**
     * Create test result record
     */
    public function createResult($data)
    {
        $data['result_id'] = $this->generateResultId();
        $data['status'] = 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->insert($data);
    }

    /**
     * Create test result from lab request
     */
    public function createFromRequest($data)
    {
        $resultData = [
            'result_id' => $this->generateResultId(),
            'request_id' => $data['request_id'],
            'patient_name' => $data['patient_name'],
            'test_type' => $data['test_type'],
            'test_date' => $data['test_date'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($resultData);
    }

    /**
     * Add test result data
     */
    public function addResultData($id, $resultData)
    {
        $updateData = [
            'result_data' => json_encode($resultData['results']),
            'normal_ranges' => json_encode($resultData['normal_ranges'] ?? []),
            'abnormal_flags' => json_encode($resultData['abnormal_flags'] ?? []),
            'interpretation' => $resultData['interpretation'] ?? '',
            'technician_notes' => $resultData['notes'] ?? '',
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check for critical values
        if (isset($resultData['critical_values']) && !empty($resultData['critical_values'])) {
            $updateData['critical_values'] = json_encode($resultData['critical_values']);
        }
        
        return $this->update($id, $updateData);
    }

    /**
     * Get all test results with optional filters
     */
    public function getResults($filters = [])
    {
        $builder = $this->builder();
        
        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (isset($filters['test_type'])) {
            $builder->like('test_type', $filters['test_type']);
        }
        
        if (isset($filters['patient_name'])) {
            $builder->like('patient_name', $filters['patient_name']);
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
     * Get pending results
     */
    public function getPendingResults()
    {
        return $this->where('status', 'pending')
                   ->orderBy('test_date', 'ASC')
                   ->findAll();
    }

    /**
     * Get completed results
     */
    public function getCompletedResults()
    {
        return $this->where('status', 'completed')
                   ->orderBy('updated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get critical results
     */
    public function getCriticalResults()
    {
        return $this->where('critical_values IS NOT NULL')
                   ->where('critical_values !=', '')
                   ->orderBy('updated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get result by request ID
     */
    public function getByRequestId($requestId)
    {
        return $this->where('request_id', $requestId)->first();
    }

    /**
     * Verify result
     */
    public function verifyResult($id, $verifiedBy)
    {
        return $this->update($id, [
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s'),
            'status' => 'verified',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Release result
     */
    public function releaseResult($id)
    {
        return $this->update($id, [
            'status' => 'released',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Search results
     */
    public function searchResults($query)
    {
        return $this->groupStart()
                   ->like('patient_name', $query)
                   ->orLike('result_id', $query)
                   ->orLike('request_id', $query)
                   ->orLike('test_type', $query)
                   ->groupEnd()
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get result with formatted data
     */
    public function getFormattedResult($id)
    {
        $result = $this->find($id);
        
        if ($result) {
            // Decode JSON fields
            $result['result_data'] = json_decode($result['result_data'] ?? '{}', true);
            $result['normal_ranges'] = json_decode($result['normal_ranges'] ?? '{}', true);
            $result['abnormal_flags'] = json_decode($result['abnormal_flags'] ?? '{}', true);
            $result['critical_values'] = json_decode($result['critical_values'] ?? '{}', true);
        }
        
        return $result;
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
        $stats['verified'] = $this->where('status', 'verified')->countAllResults(false);
        $stats['released'] = $this->where('status', 'released')->countAllResults(false);
        $stats['critical'] = $this->where('critical_values IS NOT NULL')
                                  ->where('critical_values !=', '')
                                  ->countAllResults(false);
        
        return $stats;
    }
}
