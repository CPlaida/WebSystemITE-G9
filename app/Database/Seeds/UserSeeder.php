<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Resolve role IDs by role name
        $roleIdByName = function (string $name) {
            $row = $this->db->table('roles')->where('name', $name)->get()->getRowArray();
            return $row ? (int) $row['id'] : null;
        };

        $users = [
            [
                'username'   => 'admin',
                'email'      => 'admin@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('admin'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'doctor',
                'email'      => 'doctor@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('doctor'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'nurse',
                'email'      => 'nurse@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('nurse'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'reception',
                'email'      => 'receptionist@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('receptionist'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'accounting',
                'email'      => 'accounting@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('accounting'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'itstaff',
                'email'      => 'itstaff@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('itstaff'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'laboratory',
                'email'      => 'laboratory@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('labstaff'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'pharmacy',
                'email'      => 'pharmacist@hms.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'    => $roleIdByName('pharmacist'),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $model = new UserModel();
        foreach ($users as $user) {
            // Insert via model to trigger beforeInsert ID generator
            $model->insert($user);
        }
    }
}

