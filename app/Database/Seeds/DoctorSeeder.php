<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $doctors = [
            [
                'doctor_id' => 'DOC001',
                'user_id' => null,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@hospital.com',
                'phone' => '+1234567890',
                'specialization' => 'Internal Medicine',
                'license_number' => 'LIC001234',
                'experience_years' => 15,
                'qualification' => 'MD, MBBS',
                'consultation_fee' => 150.00,
                'schedule' => 'Mon-Fri: 9:00 AM - 5:00 PM',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'doctor_id' => 'DOC002',
                'user_id' => null,
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+1234567891',
                'specialization' => 'Cardiology',
                'license_number' => 'LIC001235',
                'experience_years' => 12,
                'qualification' => 'MD, Cardiology Specialist',
                'consultation_fee' => 200.00,
                'schedule' => 'Mon-Wed-Fri: 10:00 AM - 6:00 PM',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'doctor_id' => 'DOC003',
                'user_id' => null,
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@hospital.com',
                'phone' => '+1234567892',
                'specialization' => 'Pediatrics',
                'license_number' => 'LIC001236',
                'experience_years' => 8,
                'qualification' => 'MD, Pediatrics',
                'consultation_fee' => 120.00,
                'schedule' => 'Tue-Thu-Sat: 8:00 AM - 4:00 PM',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'doctor_id' => 'DOC004',
                'user_id' => null,
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@hospital.com',
                'phone' => '+1234567893',
                'specialization' => 'Dermatology',
                'license_number' => 'LIC001237',
                'experience_years' => 10,
                'qualification' => 'MD, Dermatology Specialist',
                'consultation_fee' => 180.00,
                'schedule' => 'Mon-Thu: 11:00 AM - 7:00 PM',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'doctor_id' => 'DOC005',
                'user_id' => null,
                'first_name' => 'Robert',
                'last_name' => 'Wilson',
                'email' => 'robert.wilson@hospital.com',
                'phone' => '+1234567894',
                'specialization' => 'Orthopedics',
                'license_number' => 'LIC001238',
                'experience_years' => 20,
                'qualification' => 'MD, Orthopedic Surgeon',
                'consultation_fee' => 250.00,
                'schedule' => 'Mon-Wed-Fri: 9:00 AM - 3:00 PM',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert doctors
        foreach ($doctors as $doctor) {
            $this->db->table('doctors')->insert($doctor);
        }

        echo "Inserted " . count($doctors) . " sample doctors.\n";
    }
}
