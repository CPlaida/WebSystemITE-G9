<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccountantSeeder extends Seeder
{
    public function run()
    {
        // Delete existing accounting user first
        $this->db->table('users')->where('email', 'accounting@hms.com')->delete();
        $this->db->table('users')->where('username', 'accounting')->delete();
        
        // Insert accounting user with proper credentials
        $data = [
            'username' => 'accounting',
            'email' => 'accounting@hms.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'accounting',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}