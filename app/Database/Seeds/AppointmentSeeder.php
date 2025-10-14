<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1) Ensure there are patients to satisfy the FK
        $patientsTable = $this->db->table('patients');
        $patientsCount = (int) $patientsTable->countAllResults();
        if ($patientsCount === 0) {
            // Seed 10 sample patients if table is empty
            $samplePatients = [];
            for ($i = 1; $i <= 10; $i++) {
                $samplePatients[] = [
                    'first_name'       => 'Patient'.$i,
                    'last_name'        => 'Sample',
                    'email'            => null,
                    'phone'            => '+63917'.str_pad((string)($i * 12345 % 1000000), 6, '0', STR_PAD_LEFT),
                    'date_of_birth'    => date('Y-m-d', strtotime('-'.(20+$i).' years')),
                    'gender'           => ($i % 2 === 0) ? 'female' : 'male',
                    'address'          => 'Sample Address '.$i,
                    'type'             => 'outpatient',
                    'blood_type'       => null,
                    'emergency_contact'=> null,
                    'medical_history'  => null,
                    'status'           => 'active',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
            $patientsTable->insertBatch($samplePatients);
            echo "Seeded 10 sample patients.\n";
        }

        // Fetch up to 10 existing patient IDs (ascending)
        $patientRows = $patientsTable->select('id')->orderBy('id', 'ASC')->get(10)->getResultArray();
        if (count($patientRows) < 2) {
            echo "Not enough patients to create sample appointments (need at least 2). Aborting seeding.\n";
            return;
        }
        $patientIds = array_map(static function ($r) { return (int)$r['id']; }, $patientRows);

        // 2) Resolve doctor user IDs to satisfy FK to users.id
        $userRows = $this->db->table('users')->select('id')->orderBy('id', 'ASC')->get(2)->getResultArray();
        if (count($userRows) < 1) {
            echo "No users found. Seed users/doctors first (e.g., UserSeeder/DoctorSeeder). Aborting seeding.\n";
            return;
        }
        $doctorIdA = (int)$userRows[0]['id'];
        $doctorIdB = (count($userRows) > 1) ? (int)$userRows[1]['id'] : (int)$userRows[0]['id'];

        // 3) Build appointments mapped to existing patient IDs and chosen doctor IDs
        // Use the first 10 patient IDs cyclically where needed
        $pid = function (int $index) use ($patientIds) {
            $count = count($patientIds);
            return $patientIds[($index - 1) % $count];
        };

        $appointments = [
            [
                'patient_id' => $pid(1),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '09:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Regular checkup and blood pressure monitoring',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(2),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '10:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Follow-up for diabetes management',
                'status' => 'confirmed',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(3),
                'doctor_id' => $doctorIdB,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '14:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Chest pain and breathing difficulties',
                'status' => 'in_progress',
                'notes' => 'Patient arrived on time',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(4),
                'doctor_id' => $doctorIdB,
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '09:30:00',
                'appointment_type' => 'routine_checkup',
                'reason' => 'Annual physical examination',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(5),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '11:00:00',
                'appointment_type' => 'consultation',
                'reason' => 'Skin rash and allergic reactions',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(1),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d', strtotime('-1 day')),
                'appointment_time' => '15:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Blood test results review',
                'status' => 'completed',
                'notes' => 'All test results normal. Continue current medication.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(6),
                'doctor_id' => $doctorIdB,
                'appointment_date' => date('Y-m-d', strtotime('-1 day')),
                'appointment_time' => '16:00:00',
                'appointment_type' => 'emergency',
                'reason' => 'Severe headache and nausea',
                'status' => 'completed',
                'notes' => 'Migraine episode. Prescribed pain medication.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(7),
                'doctor_id' => $doctorIdA,
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
                'patient_id' => $pid(8),
                'doctor_id' => $doctorIdB,
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
                'patient_id' => $pid(2),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d', strtotime('+2 days')),
                'appointment_time' => '08:30:00',
                'appointment_type' => 'follow_up',
                'reason' => 'Diabetes medication adjustment follow-up',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(9),
                'doctor_id' => $doctorIdB,
                'appointment_date' => date('Y-m-d', strtotime('+2 days')),
                'appointment_time' => '14:30:00',
                'appointment_type' => 'routine_checkup',
                'reason' => 'Quarterly health assessment',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'patient_id' => $pid(10),
                'doctor_id' => $doctorIdA,
                'appointment_date' => date('Y-m-d', strtotime('+3 days')),
                'appointment_time' => '11:30:00',
                'appointment_type' => 'consultation',
                'reason' => 'Vision problems and eye strain',
                'status' => 'scheduled',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        // Insert appointments
        foreach ($appointments as $appointment) {
            $this->db->table('appointments')->insert($appointment);
        }

        echo "Inserted " . count($appointments) . " sample appointments.\n";
    }
}
