<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BedsSeeder extends Seeder
{
    public function run()
    {
        $beds = [];

        // CRITICAL CARE BEDS
        // ICU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-101', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-102', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-103', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-104', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-105', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'ICU', 'room' => 'ICU-106', 'bed' => 'Bed 1', 'status' => 'Available'];

        // NICU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-201', 'bed' => 'Incubator 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-201', 'bed' => 'Incubator 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-201', 'bed' => 'Incubator 3', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-202', 'bed' => 'Incubator 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-202', 'bed' => 'Incubator 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'NICU', 'room' => 'NICU-202', 'bed' => 'Incubator 3', 'status' => 'Available'];

        // PICU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-301', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-301', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-302', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-302', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-303', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'PICU', 'room' => 'PICU-303', 'bed' => 'Bed 2', 'status' => 'Available'];

        // CCU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'CCU', 'room' => 'CCU-401', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'CCU', 'room' => 'CCU-402', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'CCU', 'room' => 'CCU-403', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'CCU', 'room' => 'CCU-404', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'CCU', 'room' => 'CCU-405', 'bed' => 'Bed 1', 'status' => 'Available'];

        // SICU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-501', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-502', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-503', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-504', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-505', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'SICU', 'room' => 'SICU-506', 'bed' => 'Bed 1', 'status' => 'Available'];

        // MICU
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-601', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-602', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-603', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-604', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-605', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Critical Care', 'ward' => 'MICU', 'room' => 'MICU-606', 'bed' => 'Bed 1', 'status' => 'Available'];

        // SPECIALIZED PATIENT ROOMS BEDS
        // ED
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-101', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-101', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-101', 'bed' => 'Bed 3', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-102', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-102', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ED', 'room' => 'ER-102', 'bed' => 'Bed 3', 'status' => 'Available'];

        // ISO
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ISO', 'room' => 'ISO-201', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ISO', 'room' => 'ISO-202', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ISO', 'room' => 'ISO-203', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ISO', 'room' => 'ISO-204', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ISO', 'room' => 'ISO-205', 'bed' => 'Bed 1', 'status' => 'Available'];

        // PACU
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-301', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-301', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-301', 'bed' => 'Bed 3', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-302', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-302', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'PACU', 'room' => 'PACU-302', 'bed' => 'Bed 3', 'status' => 'Available'];

        // LD
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'LD', 'room' => 'LD-401', 'bed' => 'Suite 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'LD', 'room' => 'LD-402', 'bed' => 'Suite 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'LD', 'room' => 'LD-403', 'bed' => 'Suite 3', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'LD', 'room' => 'LD-404', 'bed' => 'Suite 4', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'LD', 'room' => 'LD-405', 'bed' => 'Suite 5', 'status' => 'Available'];

        // SDU
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-501', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-501', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-502', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-502', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-503', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'SDU', 'room' => 'SDU-503', 'bed' => 'Bed 2', 'status' => 'Available'];

        // ONC
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-601', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-601', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-602', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-602', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-603', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'ONC', 'room' => 'ONC-603', 'bed' => 'Bed 2', 'status' => 'Available'];

        // REHAB
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-701', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-701', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-702', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-702', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-703', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Specialized', 'ward' => 'REHAB', 'room' => 'REHAB-703', 'bed' => 'Bed 2', 'status' => 'Available'];

        // GENERAL INPATIENT ROOMS BEDS
        // Pedia Ward
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-101', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-101', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-102', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-102', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-103', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-103', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Pedia Ward', 'room' => 'P-104', 'bed' => 'Bed 1', 'status' => 'Available'];

        // Male Ward
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-201', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-201', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-202', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-202', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-203', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-203', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Male Ward', 'room' => 'M-204', 'bed' => 'Bed 1', 'status' => 'Available'];

        // Female Ward
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-301', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-301', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-302', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-302', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-303', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-303', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'General Inpatient', 'ward' => 'Female Ward', 'room' => 'F-304', 'bed' => 'Bed 1', 'status' => 'Available'];

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($beds as &$bed) {
            $bed['created_at'] = $now;
            $bed['updated_at'] = $now;
        }

        // Insert beds
        $this->db->table('beds')->insertBatch($beds);
    }
}

