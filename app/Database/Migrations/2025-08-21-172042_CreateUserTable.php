<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
<<<<<<< HEAD
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
=======
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
>>>>>>> 57646d5 (Initial commit)
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'role' => [
                'type'       => 'ENUM',
<<<<<<< HEAD
                'constraint' => ['admin', 'doctor', 'nurse', 'receptionist', 'accounting', 'itstaff', 'labstaff', 'pharmacist'],
                'default'    => 'receptionist',
=======
                'constraint' => ['Hospital Administrator', 'Doctor', 'Nurse', 'Receptionist', 'Laboratory Staff', 'Pharmacist', 'Accountant', 'IT Staff'],
                'default'    => 'Receptionist',
>>>>>>> 57646d5 (Initial commit)
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
<<<<<<< HEAD

        $this->forge->addKey('id', true); // PRIMARY KEY
        $this->forge->createTable('users', true); // true = IF NOT EXISTS
=======
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
>>>>>>> 57646d5 (Initial commit)
    }

    public function down()
    {
<<<<<<< HEAD
        $this->forge->dropTable('users', true);
=======
        $this->forge->dropTable('users');
>>>>>>> 57646d5 (Initial commit)
    }
}
