<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $doctors = [
            [
                'username' => 'dr.smith',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@hospital.com',
                'phone' => '+1234567890',
                'specialization' => 'Internal Medicine',
                'license_number' => 'LIC001234',
                'experience_years' => 15,
                'qualification' => 'MD, MBBS',
                'consultation_fee' => 150.00,
                'schedule' => 'Mon-Fri: 09:00-17:00',
                'status' => 'active',
            ],
            [
                'username' => 'dr.johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+1234567891',
                'specialization' => 'Cardiology',
                'license_number' => 'LIC001235',
                'experience_years' => 12,
                'qualification' => 'MD, Cardiology Specialist',
                'consultation_fee' => 200.00,
                'schedule' => 'Mon-Wed-Fri: 10:00-18:00',
                'status' => 'active',
            ],
            [
                'username' => 'dr.brown',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@hospital.com',
                'phone' => '+1234567892',
                'specialization' => 'Pediatrics',
                'license_number' => 'LIC001236',
                'experience_years' => 8,
                'qualification' => 'MD, Pediatrics',
                'consultation_fee' => 120.00,
                'schedule' => 'Tue-Thu-Sat: 08:00-16:00',
                'status' => 'active',
            ],
        ];

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        // Ensure 'doctor' role exists and get its ID
        $roleQuery = $this->db->table('roles')->where('name', 'doctor')->get();
        $roleRow = $roleQuery ? $roleQuery->getRowArray() : null;
        if (!$roleRow) {
            $this->db->table('roles')->insert(['name' => 'doctor']);
            $doctorRoleId = $this->db->insertID();
        } else {
            $doctorRoleId = $roleRow['id'];
        }

        $createdUsers = 0;
        $updatedUsers = 0;
        $upsertedDoctors = 0;
        $hasDoctorsTable = $this->db->tableExists('doctors');

        $userModel = new UserModel();
        foreach ($doctors as $doc) {
            // Find-or-create user by email via model so ID generator runs
            $userRow = $this->db->table('users')->where('email', $doc['email'])->get()->getRowArray();
            if (!$userRow) {
                $userData = [
                    'username'   => $doc['username'],
                    'email'      => $doc['email'],
                    'password'   => password_hash('doctor123', PASSWORD_DEFAULT),
                    'role_id'    => $doctorRoleId,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $userModel->insert($userData);
                // Retrieve by email to get generated string ID
                $userRow = $this->db->table('users')->where('email', $doc['email'])->get()->getRowArray();
                $createdUsers++;
            } else {
                // Ensure role_id is doctor
                if (($userRow['role_id'] ?? null) != $doctorRoleId) {
                    $this->db->table('users')->where('id', $userRow['id'])->update([
                        'role_id' => $doctorRoleId,
                        'updated_at' => $now,
                    ]);
                    // refresh row
                    $userRow = $this->db->table('users')->where('email', $doc['email'])->get()->getRowArray();
                    $updatedUsers++;
                }
            }
            $userId = $userRow['id'];

            // Upsert doctor profile if table exists
            if ($hasDoctorsTable) {
                $profile = [
                    'user_id'          => $userId,
                    'first_name'       => $doc['first_name'],
                    'last_name'        => $doc['last_name'],
                    'email'            => $doc['email'],
                    'phone'            => $doc['phone'],
                    'specialization'   => $doc['specialization'],
                    'license_number'   => $doc['license_number'],
                    'experience_years' => $doc['experience_years'],
                    'qualification'    => $doc['qualification'],
                    'consultation_fee' => $doc['consultation_fee'],
                    'schedule'         => $doc['schedule'],
                    'status'           => $doc['status'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                // Match by email first (or fallback to user_id)
                $existing = $this->db->table('doctors')->where('email', $doc['email'])->get();
                $existingRow = $existing ? $existing->getRowArray() : null;

                if ($existingRow) {
                    $this->db->table('doctors')->where('id', $existingRow['id'])->update($profile);
                } else {
                    $this->db->table('doctors')->insert($profile);
                }
                $upsertedDoctors++;
            }
        }

        $this->db->transComplete();

        $status = $this->db->transStatus() ? 'committed' : 'rolled back';
        if (!$hasDoctorsTable) {
            echo "Note: 'doctors' table not found. Skipped doctor profile upserts." . PHP_EOL;
        }
        echo "Doctor seeding {$status}. Users created: {$createdUsers}, users updated: {$updatedUsers}, doctor profiles upserted: {$upsertedDoctors}." . PHP_EOL;
    }
}
