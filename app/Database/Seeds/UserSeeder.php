<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
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
        ];

        // Using Query Builder to insert data
        $this->db->table('users')->insertBatch($users);
    }
}
