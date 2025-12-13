<?php
namespace App\Services\Billing\Providers;

use App\Models\ServiceModel;

class DoctorFeeProvider extends AbstractChargeProvider
{
    protected ?ServiceModel $serviceModel = null;
    
    /**
     * Default daily professional fee rate (configurable)
     * Can be overridden by service price if service exists
     */
    protected float $defaultDailyRate = 500.00;
    
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
        if ($patientId === '') {
            return [];
        }

        $items = [];
        
        // Get admissions for this patient
        if (!$this->tableExists('admission_details')) {
            return $items;
        }
        
        $builder = $this->db->table('admission_details ad');
        $builder->select('ad.id, ad.patient_id, ad.admission_date, ad.admission_time, ad.status, ad.attending_doctor_id');
        $builder->where('ad.patient_id', $patientId);
        
        // Filter by billed status if field exists
        if ($this->fieldExists('admission_details', 'billed')) {
            $builder->groupStart()
                ->where('ad.billed', 0)
                ->orWhere('ad.billed IS NULL', null, false)
                ->groupEnd();
        }
        
        // Include all active admissions (we'll handle incremental billing below)
        $builder->whereIn('ad.status', ['admitted', 'discharged'])
            ->orderBy('ad.admission_date', 'DESC');
        $admissions = $builder->get()->getResultArray();
        
        if (empty($admissions)) {
            return $items;
        }
        
        // Get already billed days for each admission (for incremental billing)
        $billedDaysMap = [];
        if ($this->tableExists('billing_items') && 
            $this->fieldExists('billing_items', 'source_table') && 
            $this->fieldExists('billing_items', 'source_id')) {
            $admissionIds = array_map(function($ad) {
                return (int)($ad['id'] ?? 0);
            }, $admissions);
            $admissionIds = array_filter($admissionIds);
            
            if (!empty($admissionIds)) {
                $billedItems = $this->db->table('billing_items')
                    ->select('source_id, qty')
                    ->where('source_table', 'admission_doctor_fee')
                    ->whereIn('source_id', $admissionIds)
                    ->get()
                    ->getResultArray();
                
                foreach ($billedItems as $bi) {
                    $admissionId = (string)($bi['source_id'] ?? '');
                    $billedQty = (int)($bi['qty'] ?? 0);
                    if ($admissionId !== '' && $billedQty > 0) {
                        $billedDaysMap[$admissionId] = ($billedDaysMap[$admissionId] ?? 0) + $billedQty;
                    }
                }
            }
        }
        
        // Get doctor information
        $doctorIds = array_values(array_filter(array_map(function($row) {
            return $row['attending_doctor_id'] ?? null;
        }, $admissions)));
        
        $doctors = [];
        if (!empty($doctorIds) && $this->tableExists('staff_profiles') && $this->tableExists('users')) {
            $doctorRows = $this->db->table('staff_profiles sp')
                ->select('sp.id, sp.first_name, sp.last_name, users.username')
                ->join('users', 'users.id = sp.user_id', 'left')
                ->whereIn('sp.id', $doctorIds)
                ->get()
                ->getResultArray();
            
            foreach ($doctorRows as $dr) {
                $doctorId = (int)($dr['id'] ?? 0);
                if ($doctorId > 0) {
                    $firstName = trim($dr['first_name'] ?? '');
                    $lastName = trim($dr['last_name'] ?? '');
                    $username = trim($dr['username'] ?? '');
                    
                    $doctorName = trim($firstName . ' ' . $lastName);
                    if ($doctorName === '') {
                        $doctorName = $username;
                    }
                    if ($doctorName === '') {
                        $doctorName = 'Doctor #' . $doctorId;
                    }
                    
                    $doctors[$doctorId] = $doctorName;
                }
            }
        }
        
        // Get service for doctor fee
        $service = null;
        $serviceId = null;
        $dailyRate = $this->defaultDailyRate;
        
        if ($this->serviceModel !== null) {
            $service = $this->serviceModel->where('code', 'FEE-DOCTOR-DAILY')
                ->where('active', 1)
                ->first();
            
            if ($service && isset($service['id'])) {
                $serviceId = (int)$service['id'];
                if (isset($service['base_price']) && (float)$service['base_price'] > 0) {
                    $dailyRate = (float)$service['base_price'];
                }
            }
        }
        
        // Process each admission
        foreach ($admissions as $admission) {
            $admissionId = (int)($admission['id'] ?? 0);
            if ($admissionId <= 0) {
                continue;
            }
            
            $doctorId = (int)($admission['attending_doctor_id'] ?? 0);
            $doctorName = $doctors[$doctorId] ?? 'Attending Physician';
            
            // Calculate days from admission date to today (or discharge date if discharged)
            $admissionDate = $admission['admission_date'] ?? null;
            if (!$admissionDate) {
                continue;
            }
            
            // Calculate total days from admission to today
            $totalDays = $this->calculateDays($admission);
            if ($totalDays <= 0) {
                $totalDays = 1; // Minimum 1 day
            }
            
            // Check how many days have already been billed (for incremental billing)
            $admissionIdStr = (string)$admissionId;
            $alreadyBilledDays = $billedDaysMap[$admissionIdStr] ?? 0;
            
            // Calculate days to bill (incremental: only bill additional days)
            $daysToBill = $totalDays - $alreadyBilledDays;
            
            // If all days are already billed, skip this admission
            if ($daysToBill <= 0) {
                continue;
            }
            
            // Calculate amount for the additional days only
            $amount = $dailyRate * $daysToBill;
            
            // Build service description
            $serviceName = $service['name'] ?? 'Professional Fee (Daily)';
            if ($alreadyBilledDays > 0) {
                // Show incremental billing info
                $serviceDesc = sprintf('%s - Dr. %s - %d additional day%s (Day %d-%d of %d total)', 
                    $serviceName,
                    $doctorName,
                    $daysToBill,
                    $daysToBill > 1 ? 's' : '',
                    $alreadyBilledDays + 1,
                    $totalDays,
                    $totalDays
                );
            } else {
                // First time billing
                $serviceDesc = sprintf('%s - Dr. %s - %d day%s', 
                    $serviceName,
                    $doctorName,
                    $daysToBill,
                    $daysToBill > 1 ? 's' : ''
                );
            }
            
            $item = $this->defaultItem();
            $item['service'] = $serviceDesc;
            $item['qty'] = $daysToBill; // Only bill the additional days
            $item['price'] = $dailyRate;
            $item['amount'] = $amount;
            $item['category'] = 'professional';
            $item['source_table'] = 'admission_doctor_fee'; // Special identifier for doctor fees
            $item['source_id'] = (string)$admissionId;
            $item['service_id'] = $serviceId;
            $item['locked'] = false; // Allow editing if needed
            
            $items[] = $item;
        }
        
        return $items;
    }
    
    /**
     * Calculate number of days from admission date to today (or discharge date)
     * 
     * @param array $admission
     * @return int
     */
    protected function calculateDays(array $admission): int
    {
        $admissionDate = $admission['admission_date'] ?? null;
        if (!$admissionDate) {
            return 1;
        }
        
        try {
            $admissionDateTime = new \DateTime($admissionDate);
            $today = new \DateTime('today');
            
            // If patient is discharged, check if there's a discharge date
            // For now, we'll calculate from admission to today
            // In the future, you could add a discharge_date field to admission_details
            
            $diff = $today->diff($admissionDateTime);
            $days = (int)$diff->days;
            
            // If same day, count as 1 day
            if ($days === 0) {
                $days = 1;
            } else {
                // Add 1 to include both start and end day
                $days = $days + 1;
            }
            
            return max(1, $days);
        } catch (\Throwable $e) {
            return 1;
        }
    }
}

