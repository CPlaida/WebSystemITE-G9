<?php
namespace App\Services\Billing\Providers;

use App\Models\ServiceModel;

class AppointmentChargeProvider extends AbstractChargeProvider
{
    /** @var string[] */
    protected array $billableStatuses = ['completed', 'confirmed', 'in_progress'];
    
    protected ?ServiceModel $serviceModel = null;
    
    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null)
    {
        parent::__construct($db);
        if (class_exists(ServiceModel::class)) {
            $this->serviceModel = new ServiceModel();
        }
    }

    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '' || !$this->tableExists('appointments')) {
            return [];
        }

        // Only show appointment charges for outpatients - exclude inpatients/admitted patients
        if ($this->tableExists('patients')) {
            $patient = $this->db->table('patients')
                ->select('type')
                ->where('id', $patientId)
                ->get()
                ->getRowArray();
            
            // If patient type is 'inpatient', exclude appointment charges
            if ($patient && strtolower(trim((string)($patient['type'] ?? ''))) === 'inpatient') {
                return [];
            }
            
            // Also check if patient has an active admission
            if ($this->tableExists('admission_details')) {
                $activeAdmission = $this->db->table('admission_details')
                    ->where('patient_id', $patientId)
                    ->where('status', 'admitted')
                    ->countAllResults();
                
                if ($activeAdmission > 0) {
                    return [];
                }
            }
        }

        $builder = $this->db->table('appointments a');
        $builder->select('a.id, a.appointment_date, a.appointment_time, a.status, a.doctor_id, a.appointment_type');
        
        // Add service_id if field exists
        if ($this->fieldExists('appointments', 'service_id')) {
            $builder->select('a.service_id');
        }
        
        $builder->where('a.patient_id', $patientId);
        if (!empty($this->billableStatuses)) {
            $builder->whereIn('a.status', $this->billableStatuses);
        }
        if ($this->fieldExists('appointments', 'billed')) {
            $builder->groupStart()
                ->where('a.billed', 0)
                ->orWhere('a.billed IS NULL', null, false)
                ->groupEnd();
        }
        
        // Join services table if service_id exists
        if ($this->fieldExists('appointments', 'service_id') && $this->tableExists('services')) {
            $builder->join('services s', 's.id = a.service_id', 'left');
            $builder->select('s.base_price as service_price, s.name as service_name, s.code as service_code');
        }
        
        // Join staff_profiles for doctor info (doctor_id now references staff_profiles.id)
        if ($this->tableExists('staff_profiles')) {
            $builder->join('staff_profiles sp', 'sp.id = a.doctor_id', 'left');
            $builder->join('users u', 'u.id = sp.user_id', 'left');
            $builder->join('roles r', 'r.id = sp.role_id', 'left');
            $builder->where('r.name', 'doctor');
            $builder->select('0 as consultation_fee, sp.first_name, sp.last_name, u.username');
        } else {
            $builder->select('NULL as consultation_fee, NULL as first_name, NULL as last_name, NULL as username');
        }
        $builder->orderBy('a.appointment_date', 'DESC');
        $appointments = $builder->get()->getResultArray();

        if (empty($appointments)) {
            return [];
        }

        $appointments = $this->filterOutAlreadyLinked($appointments, 'appointments');

        $items = [];
        foreach ($appointments as $row) {
            $fee = $this->determineConsultationFee($row);
            if ($fee <= 0) {
                continue;
            }
            $doctorName = $this->formatDoctorName($row);
            $appointmentType = $this->formatAppointmentType($row['appointment_type'] ?? '');
            
            // Build service label: Use service name if available, otherwise build from appointment type
            $serviceName = $row['service_name'] ?? null;
            if ($serviceName) {
                $service = $serviceName;
                if ($doctorName) {
                    $service .= " with {$doctorName}";
                }
            } else {
                // Fallback to old format
                if ($doctorName) {
                    $service = "Appointment Fee with {$doctorName}";
                    if ($appointmentType !== '') {
                        $service .= " - {$appointmentType}";
                    }
                } else {
                    $service = 'Appointment Fee';
                    if ($appointmentType !== '') {
                        $service .= " - {$appointmentType}";
                    }
                }
            }
            
            $item = $this->defaultItem();
            $item['service'] = $service;
            $item['price'] = $fee;
            $item['amount'] = $fee;
            $item['category'] = 'consultation';
            $item['source_table'] = 'appointments';
            $item['source_id'] = (string)($row['id'] ?? '');
            
            // Add service_id if available
            if (!empty($row['service_id'])) {
                $item['service_id'] = (int)$row['service_id'];
            }
            
            $items[] = $item;
        }

        return $items;
    }

    protected function determineConsultationFee(array $row): float
    {
        // Priority 1: Use service price if service_id exists and service is joined
        if (!empty($row['service_id']) && isset($row['service_price']) && (float)$row['service_price'] > 0) {
            return (float)$row['service_price'];
        }
        
        // Priority 2: Look up service by appointment_type if service_id not set
        if (empty($row['service_id']) && $this->serviceModel !== null) {
            $service = $this->findServiceByAppointmentType($row['appointment_type'] ?? '');
            if ($service && isset($service['base_price']) && (float)$service['base_price'] > 0) {
                return (float)$service['base_price'];
            }
        }
        
        // Priority 3: Use consultation_fee from row if available
        $fee = isset($row['consultation_fee']) ? (float)$row['consultation_fee'] : 0.0;
        if ($fee > 0) {
            return $fee;
        }
        
        // Priority 4: Fallback to hardcoded values (for backward compatibility)
        $type = strtolower((string)($row['appointment_type'] ?? ''));
        $fallbacks = [
            'consultation' => 500,
            'follow_up' => 350,
            'emergency' => 800,
            'routine_checkup' => 400,
        ];
        return (float)($fallbacks[$type] ?? 0);
    }
    
    /**
     * Find service by appointment type
     * 
     * @param string $appointmentType
     * @return array|null
     */
    protected function findServiceByAppointmentType(string $appointmentType): ?array
    {
        if ($this->serviceModel === null) {
            return null;
        }
        
        $type = strtolower(trim($appointmentType));
        $codeMap = [
            'consultation' => 'CONS-CONSULT',
            'follow_up' => 'CONS-FOLLOWUP',
            'emergency' => 'CONS-EMERGENCY',
            'routine_checkup' => 'CONS-ROUTINE',
        ];
        
        $code = $codeMap[$type] ?? null;
        if ($code) {
            try {
                $service = $this->serviceModel->where('code', $code)
                    ->where('active', 1)
                    ->where('category', 'consultation')
                    ->first();
                if ($service) {
                    return $service;
                }
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }
        
        return null;
    }

    protected function formatDoctorName(array $row): string
    {
        $parts = [];
        if (!empty($row['first_name']) || !empty($row['last_name'])) {
            $parts[] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        } elseif (!empty($row['username'])) {
            $parts[] = $row['username'];
        }
        $name = trim(implode(' ', array_filter($parts)));
        
        // Remove "User" word from the name if present
        if ($name !== '') {
            $name = preg_replace('/\s+User\s*$/i', '', $name);
            $name = trim($name);
        }
        
        return $name !== '' ? $name : 'Doctor';
    }

    /**
     * Format appointment type for display
     * 
     * @param string $type Raw appointment type from database
     * @return string Formatted appointment type
     */
    protected function formatAppointmentType(string $type): string
    {
        $type = strtolower(trim($type));
        $typeMap = [
            'consultation' => 'Consultation',
            'follow_up' => 'Follow-up',
            'emergency' => 'Emergency',
            'routine_checkup' => 'Routine Checkup',
        ];
        return $typeMap[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
