<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        $appointments = [
            [
                'patient_id' => 1,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '09:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Regular checkup and blood pressure monitoring',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '10:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Follow-up for diabetes management',
                'status' => 'confirmed',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 3,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '14:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Chest pain and breathing difficulties',
                'status' => 'in_progress',
                'notes' => 'Patient arrived on time',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 4,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '09:30:00',
                'appointment_type' => 'routine_checkup',
                'reason' => 'Annual physical examination',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 5,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '11:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Skin rash and allergic reactions',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 1,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d', strtotime('-1 day')),
                'appointment_time' => '15:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Blood test results review',
                'status' => 'completed',
                'notes' => 'All test results normal. Continue current medication.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 6,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d', strtotime('-1 day')),
                'appointment_time' => '16:00:00',
                'appointment_type' => 'emergency',
                'reason' => 'Severe headache and nausea',
                'status' => 'completed',
                'notes' => 'Migraine episode. Prescribed pain medication.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 7,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d', strtotime('-2 days')),
                'appointment_time' => '10:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Joint pain and stiffness',
                'status' => 'no_show',
                'notes' => 'Patient did not arrive for appointment',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'patient_id' => 8,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d', strtotime('-2 days')),
                'appointment_time' => '13:30:00',
                'appointment_type' => 'consultation',
                'reason' => 'Stomach pain and digestive issues',
                'status' => 'cancelled',
                'notes' => 'Patient requested cancellation due to emergency',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d', strtotime('+2 days')),
                'appointment_time' => '08:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Diabetes medication adjustment follow-up',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 9,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d', strtotime('+2 days')),
                'appointment_time' => '14:30:00',
                'appointment_type' => 'routine_checkup',
                'reason' => 'Quarterly health assessment',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'patient_id' => 10,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d', strtotime('+3 days')),
                'appointment_time' => '11:30:00',
                'appointment_type' => 'consultation',
                'reason' => 'Vision problems and eye strain',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert appointments
        foreach ($appointments as $appointment) {
            $this->db->table('appointments')->insert($appointment);
        }

        echo "Inserted " . count($appointments) . " sample appointments.\n";
    }
}
