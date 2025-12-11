<?php
namespace App\Services\Billing\Providers;

class AppointmentChargeProvider extends AbstractChargeProvider
{
    /** @var string[] */
    protected array $billableStatuses = ['completed', 'confirmed', 'in_progress'];

    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '' || !$this->tableExists('appointments')) {
            return [];
        }

        $builder = $this->db->table('appointments a');
        $builder->select('a.id, a.appointment_date, a.appointment_time, a.status, a.doctor_id, a.appointment_type, d.consultation_fee, d.first_name, d.last_name, u.username');
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
        if ($this->tableExists('doctors')) {
            $builder->join('doctors d', '(d.user_id = a.doctor_id OR d.id = a.doctor_id)', 'left', false);
        } else {
            $builder->select('NULL as consultation_fee');
        }
        if ($this->tableExists('users')) {
            $builder->join('users u', 'u.id = a.doctor_id', 'left');
        } else {
            $builder->select('NULL as username');
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
            
            // Build service label: "Appointment Fee with {doctorName} - {appointment_type}"
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
            
            $item = $this->defaultItem();
            $item['service'] = $service;
            $item['price'] = $fee;
            $item['amount'] = $fee;
            $item['category'] = 'consultation';
            $item['source_table'] = 'appointments';
            $item['source_id'] = (string)($row['id'] ?? '');
            $items[] = $item;
        }

        return $items;
    }

    protected function determineConsultationFee(array $row): float
    {
        $fee = isset($row['consultation_fee']) ? (float)$row['consultation_fee'] : 0.0;
        if ($fee > 0) {
            return $fee;
        }
        // Optional fallback: use appointment_type heuristics
        $type = strtolower((string)($row['appointment_type'] ?? ''));
        $fallbacks = [
            'consultation' => 500,
            'follow_up' => 350,
            'emergency' => 800,
            'routine_checkup' => 400,
        ];
        return (float)($fallbacks[$type] ?? 0);
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
