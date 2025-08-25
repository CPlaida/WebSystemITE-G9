<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
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
            ],
        ];

        // Using Query Builder to insert data
        $this->db->table('users')->insertBatch($users);
    }
}
