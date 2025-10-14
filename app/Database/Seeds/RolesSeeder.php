<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            ['name' => 'admin',        'description' => 'Hospital administrator',                 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'doctor',       'description' => 'Licensed physician',                     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nurse',        'description' => 'Registered nurse',                       'created_at' => $now, 'updated_at' => $now],
            ['name' => 'receptionist', 'description' => 'Front desk and patient intake',          'created_at' => $now, 'updated_at' => $now],
            ['name' => 'accounting',   'description' => 'Billing and accounting staff',           'created_at' => $now, 'updated_at' => $now],
            ['name' => 'itstaff',      'description' => 'IT support staff',                       'created_at' => $now, 'updated_at' => $now],
            ['name' => 'labstaff',     'description' => 'Laboratory staff and technicians',       'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pharmacist',   'description' => 'Pharmacy staff and inventory management','created_at' => $now, 'updated_at' => $now],
        ];

        // Insert only roles that do not exist yet (by name)
        foreach ($roles as $role) {
            $exists = $this->db->table('roles')->where('name', $role['name'])->countAllResults();
            if (!$exists) {
                $this->db->table('roles')->insert($role);
            }
        }

        echo "Roles seeded.\n";
    }
}
