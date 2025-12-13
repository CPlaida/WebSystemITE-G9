<?php
namespace App\Services\Billing\Providers;

use App\Models\PatientModel;
use App\Models\ServiceModel;

class LaboratoryChargeProvider extends AbstractChargeProvider
{
    protected ServiceModel $serviceModel;
    protected ?PatientModel $patientModel = null;

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null)
    {
        parent::__construct($db);
        $this->serviceModel = new ServiceModel();
        $this->patientModel = class_exists(PatientModel::class) ? new PatientModel() : null;
    }

    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '' || !$this->tableExists('laboratory')) {
            return [];
        }

        $patientName = $this->resolvePatientName($patientId);

        $builder = $this->db->table('laboratory');
        $builder->select('*');
        $builder->where('status', 'completed');
        $builder->groupStart()
            ->where('patient_id', $patientId);
        if ($patientName !== '') {
            $builder->orLike('test_name', $patientName, 'both');
            foreach ($this->tokenizeName($patientName) as $token) {
                $builder->orLike('test_name', $token, 'both');
            }
        }
        $builder->groupEnd();
        if ($this->fieldExists('laboratory', 'billed')) {
            $builder->groupStart()
                ->where('billed', 0)
                ->orWhere('billed IS NULL', null, false)
                ->groupEnd();
        }
        $builder->orderBy('test_date', 'DESC');
        $rows = $builder->get()->getResultArray();

        // Exclude already linked lab IDs when billing_items has lab_id/source data
        // Check both lab_id and source_table/source_id to catch all cases
        if (!empty($rows) && $this->tableExists('billing_items')) {
            $ids = array_values(array_filter(array_map(fn($r) => $r['id'] ?? null, $rows)));
            if (!empty($ids)) {
                try {
                    // Check for items linked via lab_id
                    $linkedByLabId = $this->db->table('billing_items')
                        ->select('lab_id')
                        ->whereIn('lab_id', $ids)
                        ->get()->getResultArray();
                    
                    // Check for items linked via source_table and source_id
                    $linkedBySource = $this->db->table('billing_items')
                        ->select('source_id')
                        ->where('source_table', 'laboratory')
                        ->whereIn('source_id', array_map('strval', $ids))
                        ->get()->getResultArray();
                    
                    $linkedSet = [];
                    // Add lab_ids from lab_id column
                    foreach ($linkedByLabId as $l) {
                        if (!empty($l['lab_id'])) {
                            $linkedSet[(string)$l['lab_id']] = true;
                        }
                    }
                    // Add lab_ids from source_id column
                    foreach ($linkedBySource as $l) {
                        if (!empty($l['source_id'])) {
                            $linkedSet[(string)$l['source_id']] = true;
                        }
                    }
                    
                    if (!empty($linkedSet)) {
                        $rows = array_values(array_filter($rows, function ($row) use ($linkedSet) {
                            $id = $row['id'] ?? null;
                            return !$id || !isset($linkedSet[(string)$id]);
                        }));
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }
        
        // Also use the standard filterOutAlreadyLinked method as a safety net
        if (!empty($rows)) {
            $rows = $this->filterOutAlreadyLinked($rows, 'laboratory', 'id');
        }

        $items = [];
        foreach ($rows as $row) {
            $price = $this->determineLabPrice($row);
            if ($price <= 0) {
                continue;
            }
            $labId = isset($row['id']) ? (string)$row['id'] : null;
            $desc = 'Laboratory: ' . ($row['test_type'] ?? ($row['test_name'] ?? 'Diagnostic Test'));
            $item = $this->defaultItem();
            $item['service'] = $desc;
            $item['price'] = $price;
            $item['amount'] = $price;
            $item['lab_id'] = $labId;
            $item['category'] = 'laboratory';
            $item['source_table'] = 'laboratory';
            $item['source_id'] = $labId;
            $items[] = $item;
        }

        return $items;
    }

    protected function resolvePatientName(string $patientId): string
    {
        if ($this->patientModel === null || !$this->tableExists('patients')) {
            return '';
        }
        try {
            $row = $this->patientModel->select('*')->where('id', $patientId)->first();
            if (!$row) {
                return '';
            }
            $candidates = [
                $row['name'] ?? null,
                $row['full_name'] ?? null,
                trim(((string)($row['first_name'] ?? '')) . ' ' . ((string)($row['last_name'] ?? ''))),
                trim(((string)($row['firstname'] ?? '')) . ' ' . ((string)($row['lastname'] ?? ''))),
            ];
            foreach ($candidates as $candidate) {
                if ($candidate && trim($candidate) !== '') {
                    return trim($candidate);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return '';
    }

    protected function tokenizeName(string $name): array
    {
        $tokens = preg_split('/\s+/', trim($name));
        $tokens = is_array($tokens) ? array_filter($tokens, fn($t) => strlen($t) >= 2) : [];
        return array_values($tokens);
    }

    protected function determineLabPrice(array $row): float
    {
        $price = (float)($row['cost'] ?? 0);
        if ($price > 0) {
            return $price;
        }
        $candidates = [];
        if (!empty($row['test_type'])) {
            $candidates[] = $row['test_type'];
        }
        if (!empty($row['test_name'])) {
            $candidates[] = $row['test_name'];
        }
        foreach ($candidates as $candidate) {
            $svc = $this->matchService($candidate);
            if ($svc && isset($svc['base_price'])) {
                $price = (float)$svc['base_price'];
                if ($price > 0) {
                    return $price;
                }
            }
        }
        return 0.0;
    }

    protected function matchService(string $label): ?array
    {
        $label = trim($label);
        if ($label === '') {
            return null;
        }
        $norm = function (string $value): string {
            $value = strtolower(trim($value));
            return preg_replace('/[^a-z0-9]+/', '', $value);
        };
        $alias = [
            'bloodtest' => 'LAB-BLOOD',
            'urinetest' => 'LAB-URINE',
            'xray' => 'IMG-XRAY',
            'xrayplain' => 'IMG-XRAY',
            'xrayexam' => 'IMG-XRAY',
            'mri' => 'IMG-MRI',
            'mriscan' => 'IMG-MRI',
            'ct' => 'IMG-CT',
            'ctscan' => 'IMG-CT',
            'ultrasound' => 'IMG-US',
            'ecg' => 'CARD-ECG',
        ];
        $code = $alias[$norm($label)] ?? null;
        try {
            if ($code) {
                $svc = $this->serviceModel->where('code', $code)->where('active', 1)->first();
                if ($svc) {
                    return $svc;
                }
            }
            $svc = $this->serviceModel->findByCodeOrName($label);
            if ($svc) {
                return $svc;
            }
            $svc = $this->serviceModel->where('LOWER(name)', strtolower($label))->where('active', 1)->first();
            if ($svc) {
                return $svc;
            }
            return $this->serviceModel->like('name', $label, 'both')->where('active', 1)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
