<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PhilHealthCaseRatesSeeder extends Seeder
{
    public function run()
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // RVS-based (Case B preferred). Appendectomy
            [
                'code_type' => 'RVS',
                'code' => '48010',
                'description' => 'Appendectomy',
                'case_type' => 'B',
                'rate_total' => 21000.00,
                'facility_share' => 14700.00,
                'professional_share' => 6300.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // RVS alternative effective window (older rate to test date selection)
            [
                'code_type' => 'RVS',
                'code' => '48010',
                'description' => 'Appendectomy (old rate)',
                'case_type' => 'B',
                'rate_total' => 20000.00,
                'facility_share' => 14000.00,
                'professional_share' => 6000.00,
                'effective_from' => '2023-01-01',
                'effective_to' => '2023-12-31',
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // ICD-based (Case A). Pneumonia, unspecified organism
            [
                'code_type' => 'ICD',
                'code' => 'J18.9',
                'description' => 'Pneumonia, unspecified organism',
                'case_type' => 'A',
                'rate_total' => 15000.00,
                'facility_share' => 10500.00,
                'professional_share' => 4500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // ICD-based (Case A). Dengue fever
            [
                'code_type' => 'ICD',
                'code' => 'A90',
                'description' => 'Dengue fever (classical dengue)',
                'case_type' => 'A',
                'rate_total' => 10000.00,
                'facility_share' => 7000.00,
                'professional_share' => 3000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Another RVS - Cholecystectomy (example values)
            [
                'code_type' => 'RVS',
                'code' => '47562',
                'description' => 'Laparoscopic cholecystectomy',
                'case_type' => 'B',
                'rate_total' => 31000.00,
                'facility_share' => 21700.00,
                'professional_share' => 9300.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // An ICD with older effectivity to test boundary
            [
                'code_type' => 'ICD',
                'code' => 'I10',
                'description' => 'Essential (primary) hypertension',
                'case_type' => 'A',
                'rate_total' => 6000.00,
                'effective_from' => '2022-01-01',
                'effective_to' => '2023-12-31',
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // ========== LABORATORY TESTS ==========
            // Complete Blood Count (CBC)
            [
                'code_type' => 'RVS',
                'code' => '85025',
                'description' => 'Complete Blood Count (CBC)',
                'case_type' => 'A',
                'rate_total' => 500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Urinalysis
            [
                'code_type' => 'RVS',
                'code' => '81003',
                'description' => 'Urinalysis, Complete',
                'case_type' => 'A',
                'rate_total' => 375.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Fasting Blood Sugar (FBS)
            [
                'code_type' => 'RVS',
                'code' => '82947',
                'description' => 'Glucose, Quantitative, Blood (FBS)',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Lipid Profile
            [
                'code_type' => 'RVS',
                'code' => '80061',
                'description' => 'Lipid Profile',
                'case_type' => 'A',
                'rate_total' => 750.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Creatinine
            [
                'code_type' => 'RVS',
                'code' => '82565',
                'description' => 'Creatinine',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Blood Urea Nitrogen (BUN)
            [
                'code_type' => 'RVS',
                'code' => '84520',
                'description' => 'Blood Urea Nitrogen (BUN)',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // SGPT/ALT
            [
                'code_type' => 'RVS',
                'code' => '84460',
                'description' => 'Alanine Aminotransferase (ALT/SGPT)',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // SGOT/AST
            [
                'code_type' => 'RVS',
                'code' => '84450',
                'description' => 'Aspartate Aminotransferase (AST/SGOT)',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Alkaline Phosphatase
            [
                'code_type' => 'RVS',
                'code' => '84075',
                'description' => 'Alkaline Phosphatase',
                'case_type' => 'A',
                'rate_total' => 250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // HBA1C
            [
                'code_type' => 'RVS',
                'code' => '83036',
                'description' => 'Hemoglobin A1c',
                'case_type' => 'A',
                'rate_total' => 500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Urine Culture
            [
                'code_type' => 'RVS',
                'code' => '87086',
                'description' => 'Culture, Urine',
                'case_type' => 'B',
                'rate_total' => 1000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Blood Culture
            [
                'code_type' => 'RVS',
                'code' => '87040',
                'description' => 'Culture, Blood',
                'case_type' => 'B',
                'rate_total' => 1500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Sputum AFB
            [
                'code_type' => 'RVS',
                'code' => '87116',
                'description' => 'Culture, Mycobacteria (Sputum AFB)',
                'case_type' => 'B',
                'rate_total' => 1250.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Chest X-ray (PA)
            [
                'code_type' => 'RVS',
                'code' => '71046',
                'description' => 'Chest X-ray, 2 views (PA & Lateral)',
                'case_type' => 'B',
                'rate_total' => 1000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // ECG
            [
                'code_type' => 'RVS',
                'code' => '93000',
                'description' => 'Electrocardiogram (12-lead)',
                'case_type' => 'A',
                'rate_total' => 750.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // 2D Echo
            [
                'code_type' => 'RVS',
                'code' => '93306',
                'description' => '2D Echocardiography',
                'case_type' => 'B',
                'rate_total' => 2500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Ultrasound, Whole Abdomen
            [
                'code_type' => 'RVS',
                'code' => '76700',
                'description' => 'Ultrasound, Whole Abdomen',
                'case_type' => 'B',
                'rate_total' => 3000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Ultrasound, Pelvis
            [
                'code_type' => 'RVS',
                'code' => '76856',
                'description' => 'Ultrasound, Pelvis (Non-Obstetric)',
                'case_type' => 'B',
                'rate_total' => 2500.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // CT Scan, Head
            [
                'code_type' => 'RVS',
                'code' => '70450',
                'description' => 'CT Scan, Head (Without Contrast)',
                'case_type' => 'C',
                'rate_total' => 5000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // MRI, Brain
            [
                'code_type' => 'RVS',
                'code' => '70551',
                'description' => 'MRI, Brain (Without Contrast)',
                'case_type' => 'C',
                'rate_total' => 10000.00,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'active' => 1,
                'updated_by' => 'seeder',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $normalizedData = array_map(function (array $row) use ($timestamp) {
            $row['id'] = $row['id'] ?? null; // satisfy implicit column list
            $row['facility_share'] = $row['facility_share'] ?? 0;
            $row['professional_share'] = $row['professional_share'] ?? 0;
            $row['description'] = $row['description'] ?? null;
            $row['active'] = $row['active'] ?? 1;
            $row['updated_by'] = $row['updated_by'] ?? 'seeder';
            $row['created_at'] = $row['created_at'] ?? $timestamp;
            $row['updated_at'] = $row['updated_at'] ?? $timestamp;
            return $row;
        }, $data);

        $this->db->table('philhealth_case_rates')->insertBatch($normalizedData);
    }
}
