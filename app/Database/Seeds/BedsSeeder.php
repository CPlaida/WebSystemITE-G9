<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BedsSeeder extends Seeder
{
    public function run()
    {
        $beds = [];

        // CRITICAL CARE BEDS
        // ICU (includes merged CCU/SICU/MICU capacity)
        $icuRooms = [
            'ICU-101', 'ICU-102', 'ICU-103', 'ICU-104', 'ICU-105', 'ICU-106',
            'ICU-201', 'ICU-202', 'ICU-203', 'ICU-204', 'ICU-205',
            'ICU-301', 'ICU-302', 'ICU-303', 'ICU-304', 'ICU-305', 'ICU-306',
            'ICU-401', 'ICU-402', 'ICU-403', 'ICU-404', 'ICU-405', 'ICU-406',
        ];
        foreach ($icuRooms as $roomNumber) {
            $beds[] = [
                'bed_type' => 'Critical Care',
                'ward' => 'ICU',
                'room' => $roomNumber,
                'bed' => 'Bed 1',
                'status' => 'Available'
            ];
        }

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

        // GENERAL INPATIENT ROOMS BEDS
        // Pedia, Male, Female wards (6 beds per room)
        $generalWards = [
            'Pedia Ward' => ['P-101', 'P-102', 'P-103', 'P-104'],
            'Male Ward' => ['M-201', 'M-202', 'M-203', 'M-204'],
            'Female Ward' => ['F-301', 'F-302', 'F-303', 'F-304'],
        ];

        foreach ($generalWards as $wardName => $rooms) {
            foreach ($rooms as $roomNumber) {
                for ($i = 1; $i <= 6; $i++) {
                    $beds[] = [
                        'bed_type' => 'General Inpatient',
                        'ward' => $wardName,
                        'room' => $roomNumber,
                        'bed' => 'Bed ' . $i,
                        'status' => 'Available',
                    ];
                }
            }
        }

        // Semi-Private Rooms (2 beds per room)
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-401', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-401', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-402', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-402', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-403', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-403', 'bed' => 'Bed 2', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-404', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Semi-Private', 'ward' => 'Semi-Private Ward', 'room' => 'SP-404', 'bed' => 'Bed 2', 'status' => 'Available'];

        // Private Rooms (single bed per room)
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-501', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-502', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-503', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-504', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-505', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-506', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-507', 'bed' => 'Bed 1', 'status' => 'Available'];
        $beds[] = ['bed_type' => 'Private', 'ward' => 'Private Suites', 'room' => 'PR-508', 'bed' => 'Bed 1', 'status' => 'Available'];

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        foreach ($beds as &$bed) {
            $bed['created_at'] = $now;
            $bed['updated_at'] = $now;
        }
        unset($bed);

        // Insert beds
        $this->db->table('beds')->insertBatch($beds);
    }
}
