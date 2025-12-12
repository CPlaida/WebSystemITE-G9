<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class StaffProfilesSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        
        // Get role IDs
        $roles = $this->db->table('roles')->select('id, name')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[strtolower($role['name'])] = $role['id'];
        }
        
        // Get department IDs
        $departments = $this->db->table('staff_departments')->select('id, name')->get()->getResultArray();
        $deptMap = [];
        foreach ($departments as $dept) {
            $deptMap[strtolower($dept['name'])] = $dept['id'];
        }
        
        // Get specialization IDs
        $specializations = $this->db->table('staff_specializations')->select('id, name')->get()->getResultArray();
        $specMap = [];
        foreach ($specializations as $spec) {
            $specMap[strtolower($spec['name'])] = $spec['id'];
        }
        
        // Helper function to get IDs safely
        $getRoleId = function($name) use ($roleMap) {
            return $roleMap[strtolower($name)] ?? null;
        };
        
        $getDeptId = function($name) use ($deptMap) {
            return $deptMap[strtolower($name)] ?? null;
        };
        
        $getSpecId = function($name) use ($specMap) {
            return $specMap[strtolower($name)] ?? null;
        };
        
        // Staff profiles data
        // Note: Doctors are handled by DoctorSeeder, so only non-doctor staff are here
        $staffProfiles = [
            // Nurses
            [
                'first_name' => 'Sarah',
                'middle_name' => 'Mendoza',
                'last_name' => 'Villanueva',
                'gender' => 'female',
                'date_of_birth' => '1992-05-20',
                'phone' => '+63 912 345 6785',
                'email' => 'sarah.villanueva@hospital.com',
                'role_id' => $getRoleId('nurse'),
                'license_number' => 'RN-2022-001',
                'department_id' => $getDeptId('Critical Care Nursing'),
                'specialization_id' => $getSpecId('Critical Care Nursing'),
                'address' => '321 Nursing Home, Pasig',
                'hire_date' => '2022-01-05',
                'status' => 'active',
                'emergency_contact_name' => 'Michael Villanueva',
                'emergency_contact_phone' => '+63 912 345 6784',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'first_name' => 'Robert',
                'middle_name' => 'Dela Cruz',
                'last_name' => 'Fernandez',
                'gender' => 'male',
                'date_of_birth' => '1989-09-14',
                'phone' => '+63 912 345 6784',
                'email' => 'robert.fernandez@hospital.com',
                'role_id' => $getRoleId('nurse'),
                'license_number' => 'RN-2021-002',
                'department_id' => $getDeptId('Inpatient Nursing Unit'),
                'specialization_id' => $getSpecId('Neonatal Nursing'),
                'address' => '654 Care Blvd, Mandaluyong',
                'hire_date' => '2021-08-20',
                'status' => 'on_leave',
                'emergency_contact_name' => 'Lisa Fernandez',
                'emergency_contact_phone' => '+63 912 345 6783',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Receptionists
            [
                'first_name' => 'Jennifer',
                'middle_name' => 'Ramos',
                'last_name' => 'Bautista',
                'gender' => 'female',
                'date_of_birth' => '1995-02-28',
                'phone' => '+63 912 345 6783',
                'email' => 'jennifer.bautista@hospital.com',
                'role_id' => $getRoleId('receptionist'),
                'license_number' => null,
                'department_id' => null,
                'specialization_id' => null,
                'address' => '987 Front Desk Ave, Taguig',
                'hire_date' => '2023-02-01',
                'status' => 'active',
                'emergency_contact_name' => 'David Bautista',
                'emergency_contact_phone' => '+63 912 345 6782',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Pharmacists
            [
                'first_name' => 'Michael',
                'middle_name' => 'Tan',
                'last_name' => 'Lim',
                'gender' => 'male',
                'date_of_birth' => '1987-12-05',
                'phone' => '+63 912 345 6782',
                'email' => 'michael.lim@hospital.com',
                'role_id' => $getRoleId('pharmacist'),
                'license_number' => 'RPh-2020-001',
                'department_id' => null,
                'specialization_id' => null,
                'address' => '147 Pharmacy Lane, San Juan',
                'hire_date' => '2020-05-15',
                'status' => 'active',
                'emergency_contact_name' => 'Grace Lim',
                'emergency_contact_phone' => '+63 912 345 6781',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Lab Staff
            [
                'first_name' => 'Patricia',
                'middle_name' => 'Chua',
                'last_name' => 'Ong',
                'gender' => 'female',
                'date_of_birth' => '1993-08-18',
                'phone' => '+63 912 345 6781',
                'email' => 'patricia.ong@hospital.com',
                'role_id' => $getRoleId('labstaff'),
                'license_number' => null,
                'department_id' => $getDeptId('Laboratory Services'),
                'specialization_id' => null,
                'address' => '258 Lab Street, Paranaque',
                'hire_date' => '2022-07-10',
                'status' => 'active',
                'emergency_contact_name' => 'James Ong',
                'emergency_contact_phone' => '+63 912 345 6780',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // Accounting Staff
            [
                'first_name' => 'Christopher',
                'middle_name' => 'Sy',
                'last_name' => 'Yu',
                'gender' => 'male',
                'date_of_birth' => '1991-04-25',
                'phone' => '+63 912 345 6780',
                'email' => 'christopher.yu@hospital.com',
                'role_id' => $getRoleId('accounting'),
                'license_number' => null,
                'department_id' => null,
                'specialization_id' => null,
                'address' => '369 Finance Road, Alabang',
                'hire_date' => '2021-11-01',
                'status' => 'active',
                'emergency_contact_name' => 'Michelle Yu',
                'emergency_contact_phone' => '+63 912 345 6779',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // IT Staff
            [
                'first_name' => 'Daniel',
                'middle_name' => 'Ang',
                'last_name' => 'Chua',
                'gender' => 'male',
                'date_of_birth' => '1994-06-12',
                'phone' => '+63 912 345 6779',
                'email' => 'daniel.chua@hospital.com',
                'role_id' => $getRoleId('itstaff'),
                'license_number' => null,
                'department_id' => null,
                'specialization_id' => null,
                'address' => '741 Tech Park, Ortigas',
                'hire_date' => '2023-01-15',
                'status' => 'active',
                'emergency_contact_name' => 'Karen Chua',
                'emergency_contact_phone' => '+63 912 345 6778',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
        ];
        
        // Insert staff profiles and create user accounts
        $inserted = 0;
        $skipped = 0;
        $usersCreated = 0;
        $userModel = new UserModel();
        
        foreach ($staffProfiles as $staff) {
            // Check if staff with same email or license number already exists
            $exists = false;
            $existingProfile = null;
            if (!empty($staff['email'])) {
                $existingProfile = $this->db->table('staff_profiles')
                    ->where('email', $staff['email'])
                    ->get()
                    ->getRowArray();
                $exists = $existingProfile !== null;
            }
            
            if (!$exists && !empty($staff['license_number'])) {
                $existingProfile = $this->db->table('staff_profiles')
                    ->where('license_number', $staff['license_number'])
                    ->get()
                    ->getRowArray();
                $exists = $existingProfile !== null;
            }
            
            if (!$exists) {
                // Step 1: Create user account if it doesn't exist
                $userId = null;
                if (!empty($staff['email'])) {
                    $userRow = $userModel->where('email', $staff['email'])->first();
                    
                    if (!$userRow) {
                        // Generate username from email or name
                        $username = str_replace('@hospital.com', '', $staff['email']);
                        $username = str_replace('.', '_', $username);
                        
                        $userData = [
                            'username' => $username,
                            'email' => $staff['email'],
                            'password' => password_hash('staff123', PASSWORD_DEFAULT), // Default password
                            'role_id' => $staff['role_id'],
                            'status' => $staff['status'] === 'active' ? 'active' : 'inactive',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        
                        // Use model to insert so ID is auto-generated
                        $userModel->insert($userData);
                        $userId = $userModel->getInsertID();
                        $usersCreated++;
                    } else {
                        $userId = $userRow['id'];
                        // Update role_id if needed
                        if (($userRow['role_id'] ?? null) != $staff['role_id']) {
                            $userModel->update($userId, ['role_id' => $staff['role_id'], 'updated_at' => $now]);
                        }
                    }
                }
                
                // Step 2: Add user_id to staff profile
                $staff['user_id'] = $userId;
                
                // Step 3: Insert staff profile
                $this->db->table('staff_profiles')->insert($staff);
                $inserted++;
                $name = trim(($staff['last_name'] ?? '') . ', ' . ($staff['first_name'] ?? ''));
                echo "✓ Inserted staff: {$name}\n";
            } else {
                // Update existing profile if user_id is missing
                if (empty($existingProfile['user_id']) && !empty($staff['email'])) {
                    $userRow = $userModel->where('email', $staff['email'])->first();
                    
                    if ($userRow) {
                        $this->db->table('staff_profiles')
                            ->where('id', $existingProfile['id'])
                            ->update(['user_id' => $userRow['id'], 'updated_at' => $now]);
                    }
                }
                
                $skipped++;
                $name = trim(($staff['last_name'] ?? '') . ', ' . ($staff['first_name'] ?? ''));
                echo "- Skipped existing staff: {$name}\n";
            }
        }
        
        echo "\nStaff Profiles Seeder completed.\n";
        echo "Inserted: {$inserted} staff profiles\n";
        echo "Users created: {$usersCreated}\n";
        echo "Skipped: {$skipped} existing staff profiles\n";
    }
}

