<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run()
    {
        // Sample lab requests
        $labRequests = [
            [
                'request_id' => 'LR' . date('Ymd') . '0001',
                'patient_id' => 1,
                'doctor_id' => 1,
                'patient_name' => 'Juan Dela Cruz',
                'date_of_birth' => '1985-03-15',
                'test_type' => 'Complete Blood Count (CBC)',
                'priority' => 'normal',
                'clinical_notes' => 'Routine checkup, patient complains of fatigue',
                'test_date' => date('Y-m-d'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'request_id' => 'LR' . date('Ymd') . '0002',
                'patient_id' => 2,
                'doctor_id' => 1,
                'patient_name' => 'Maria Santos',
                'date_of_birth' => '1990-07-22',
                'test_type' => 'Urinalysis',
                'priority' => 'urgent',
                'clinical_notes' => 'Patient has UTI symptoms',
                'test_date' => date('Y-m-d'),
                'status' => 'in_progress',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'request_id' => 'LR' . date('Ymd') . '0003',
                'patient_id' => 3,
                'doctor_id' => 2,
                'patient_name' => 'Pedro Rodriguez',
                'date_of_birth' => '1978-11-08',
                'test_type' => 'Lipid Profile',
                'priority' => 'normal',
                'clinical_notes' => 'Cardiovascular risk assessment',
                'test_date' => date('Y-m-d', strtotime('+1 day')),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'request_id' => 'LR' . date('Ymd') . '0004',
                'patient_id' => 4,
                'doctor_id' => 2,
                'patient_name' => 'Ana Garcia',
                'date_of_birth' => '1995-01-30',
                'test_type' => 'Thyroid Function Test',
                'priority' => 'stat',
                'clinical_notes' => 'Suspected hyperthyroidism',
                'test_date' => date('Y-m-d'),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert lab requests
        $this->db->table('lab_requests')->insertBatch($labRequests);

        // Sample test results - matching the actual table structure
        $testResults = [
            [
                'result_id' => 'TR' . date('Ymd') . '0001',
                'request_id' => 'LR' . date('Ymd') . '0001',
                'patient_name' => 'Juan Dela Cruz',
                'test_type' => 'Complete Blood Count (CBC)',
                'test_date' => date('Y-m-d'),
                'result_data' => json_encode([
                    'Hemoglobin' => '14.5',
                    'Hematocrit' => '42.5',
                    'White Blood Cells' => '6.5',
                    'Red Blood Cells' => '4.8',
                    'Platelets' => '250'
                ]),
                'normal_ranges' => json_encode([
                    'Hemoglobin' => '12.0-16.0 g/dL',
                    'Hematocrit' => '36-46%',
                    'White Blood Cells' => '4.0-10.0 x10³/µL',
                    'Red Blood Cells' => '4.2-5.4 x10⁶/µL',
                    'Platelets' => '150-450 x10³/µL'
                ]),
                'abnormal_flags' => json_encode([]),
                'interpretation' => 'All values within normal limits',
                'technician_notes' => 'Sample processed without issues',
                'verified_by' => null,
                'verified_at' => null,
                'status' => 'pending',
                'critical_values' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'result_id' => 'TR' . date('Ymd') . '0002',
                'request_id' => 'LR' . date('Ymd') . '0002',
                'patient_name' => 'Maria Santos',
                'test_type' => 'Urinalysis',
                'test_date' => date('Y-m-d'),
                'result_data' => json_encode([
                    'Color' => 'Yellow',
                    'Clarity' => 'Cloudy',
                    'Specific Gravity' => '1.025',
                    'pH' => '6.0',
                    'Protein' => 'Trace',
                    'Glucose' => 'Negative',
                    'Ketones' => 'Negative',
                    'Blood' => 'Negative',
                    'Nitrites' => 'Positive',
                    'Leukocyte Esterase' => 'Positive'
                ]),
                'normal_ranges' => json_encode([
                    'Color' => 'Yellow to amber',
                    'Clarity' => 'Clear',
                    'Specific Gravity' => '1.003-1.030',
                    'pH' => '4.6-8.0',
                    'Protein' => 'Negative to trace',
                    'Nitrites' => 'Negative',
                    'Leukocyte Esterase' => 'Negative'
                ]),
                'abnormal_flags' => json_encode([
                    'Clarity' => 'Abnormal - Cloudy',
                    'Nitrites' => 'Abnormal - Positive',
                    'Leukocyte Esterase' => 'Abnormal - Positive'
                ]),
                'interpretation' => 'Findings consistent with urinary tract infection',
                'technician_notes' => 'Recommend antibiotic sensitivity testing',
                'verified_by' => null,
                'verified_at' => null,
                'status' => 'completed',
                'critical_values' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'result_id' => 'TR' . date('Ymd') . '0003',
                'request_id' => 'LR' . date('Ymd') . '0003',
                'patient_name' => 'Pedro Rodriguez',
                'test_type' => 'Lipid Profile',
                'test_date' => date('Y-m-d', strtotime('+1 day')),
                'result_data' => null,
                'normal_ranges' => null,
                'abnormal_flags' => null,
                'interpretation' => null,
                'technician_notes' => null,
                'verified_by' => null,
                'verified_at' => null,
                'status' => 'pending',
                'critical_values' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'result_id' => 'TR' . date('Ymd') . '0004',
                'request_id' => 'LR' . date('Ymd') . '0004',
                'patient_name' => 'Ana Garcia',
                'test_type' => 'Thyroid Function Test',
                'test_date' => date('Y-m-d'),
                'result_data' => json_encode([
                    'TSH' => '0.1',
                    'Free T4' => '2.8',
                    'Free T3' => '6.2'
                ]),
                'normal_ranges' => json_encode([
                    'TSH' => '0.4-4.0 mIU/L',
                    'Free T4' => '0.9-1.7 ng/dL',
                    'Free T3' => '2.3-4.2 pg/mL'
                ]),
                'abnormal_flags' => json_encode([
                    'TSH' => 'Low',
                    'Free T4' => 'High',
                    'Free T3' => 'High'
                ]),
                'critical_values' => json_encode([
                    'TSH' => 'Critically low',
                    'Free T4' => 'Critically high'
                ]),
                'interpretation' => 'Results consistent with hyperthyroidism',
                'technician_notes' => 'Critical values called to physician',
                'verified_by' => 'Dr. Laboratory Supervisor',
                'verified_at' => date('Y-m-d H:i:s'),
                'status' => 'verified',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert test results
        $this->db->table('test_results')->insertBatch($testResults);

        echo "Laboratory sample data seeded successfully!\n";
        echo "- 4 lab requests created\n";
        echo "- 4 test results created\n";
        echo "- Includes various test types and statuses\n";
        echo "- Sample data ready for testing\n";
    }
}
