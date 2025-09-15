<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DoctorUserSeeder extends Seeder
{
    public function run()
    {
        // Sample doctor users
        $doctors = [
            [
                'username' => 'dr.smith',
                'email' => 'dr.smith@stpeter.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'dr.johnson',
                'email' => 'dr.johnson@stpeter.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'dr.brown',
                'email' => 'dr.brown@stpeter.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'dr.davis',
                'email' => 'dr.davis@stpeter.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'dr.wilson',
                'email' => 'dr.wilson@stpeter.com',
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role' => 'doctor',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert doctor users
        foreach ($doctors as $doctor) {
            $this->db->table('users')->insert($doctor);
        }

        echo "Doctor users seeded successfully!\n";
    }
}
