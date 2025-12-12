<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        // Doctor data - creates users and staff_profiles
        // Note: Specializations must exist in staff_specializations table
        $doctors = [
            [
                'username' => 'dr.smith',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@hospital.com',
                'phone' => '+1234567890',
                'specialization' => 'Internal Medicine', // Will be matched to staff_specializations.name
                'license_number' => 'LIC001234',
                'status' => 'active',
            ],
            [
                'username' => 'dr.johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+1234567891',
                'specialization' => 'Cardiology', // Will be matched to staff_specializations.name
                'license_number' => 'LIC001235',
                'status' => 'active',
            ],
            [
                'username' => 'dr.brown',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@hospital.com',
                'phone' => '+1234567892',
                'specialization' => 'Pediatrics', // Will be matched to staff_specializations.name
                'license_number' => 'LIC001236',
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
        $upsertedProfiles = 0;
        
        if (!$this->db->tableExists('staff_profiles')) {
            echo "Error: 'staff_profiles' table does not exist. Please run migrations first." . PHP_EOL;
            return;
        }

        $userModel = new UserModel();
        foreach ($doctors as $doc) {
            // Step 1: Find or create user account
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
                $userRow = $this->db->table('users')->where('email', $doc['email'])->get()->getRowArray();
                $createdUsers++;
            } else {
                // Ensure role_id is doctor
                if (($userRow['role_id'] ?? null) != $doctorRoleId) {
                    $this->db->table('users')->where('id', $userRow['id'])->update([
                        'role_id' => $doctorRoleId,
                        'updated_at' => $now,
                    ]);
                    $userRow = $this->db->table('users')->where('email', $doc['email'])->get()->getRowArray();
                    $updatedUsers++;
                }
            }
            $userId = $userRow['id'];

            // Step 2: Find or create staff profile (staff_profiles.id is what appointments/doctor_schedules reference)
            // Try to find specialization_id by name
            $specializationId = null;
            if (!empty($doc['specialization']) && $this->db->tableExists('staff_specializations')) {
                $specRow = $this->db->table('staff_specializations')
                    ->where('name', $doc['specialization'])
                    ->get()
                    ->getRowArray();
                if ($specRow) {
                    $specializationId = $specRow['id'];
                }
            }

            $profile = [
                'user_id'          => $userId,
                'first_name'       => $doc['first_name'],
                'last_name'        => $doc['last_name'],
                'email'            => $doc['email'],
                'phone'            => $doc['phone'],
                'role_id'          => $doctorRoleId,
                'license_number'   => $doc['license_number'],
                'specialization_id' => $specializationId,
                'status'           => $doc['status'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            // Check if profile exists by user_id (preferred) or email
            $existing = $this->db->table('staff_profiles')
                ->where('user_id', $userId)
                ->orWhere('email', $doc['email'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $this->db->table('staff_profiles')->where('id', $existing['id'])->update($profile);
            } else {
                $this->db->table('staff_profiles')->insert($profile);
            }
            $upsertedProfiles++;
        }

        $this->db->transComplete();

        $status = $this->db->transStatus() ? 'committed' : 'rolled back';
        echo "Doctor seeding {$status}. Users created: {$createdUsers}, users updated: {$updatedUsers}, staff profiles upserted: {$upsertedProfiles}." . PHP_EOL;
    }
}
