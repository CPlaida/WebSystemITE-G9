<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
<<<<<<< HEAD
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'username'   => 'admin',
                'email'      => 'admin@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'doctor',
                'email'      => 'doctor@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'doctor',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'nurse',
                'email'      => 'nurse@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'nurse',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'reception',
                'email'      => 'receptionist@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'receptionist',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'accounting',
                'email'      => 'accounting@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'accounting',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'itstaff',
                'email'      => 'itstaff@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'itstaff',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'laboratory',
                'email'      => 'laboratory@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'labstaff',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'pharmacy',
                'email'      => 'pharmacist@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'pharmacist',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
=======
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Hospital Administrator',
            ],
            [
                'name' => 'Doctor User',
                'email' => 'doctor@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Doctor',
            ],
            [
                'name' => 'Nurse User',
                'email' => 'nurse@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Nurse',
            ],
            [
                'name' => 'Receptionist User',
                'email' => 'receptionist@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Receptionist',
            ],
            [
                'name' => 'Lab Staff User',
                'email' => 'lab@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Laboratory Staff',
            ],
            [
                'name' => 'Pharmacist User',
                'email' => 'pharmacist@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Pharmacist',
            ],
            [
                'name' => 'Accountant User',
                'email' => 'accountant@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'Accountant',
            ],
            [
                'name' => 'IT Staff User',
                'email' => 'it@hms.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'IT Staff',
>>>>>>> 57646d5 (Initial commit)
            ],
        ];

        // Using Query Builder to insert data
        $this->db->table('users')->insertBatch($users);
    }
}
