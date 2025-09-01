<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
<<<<<<< HEAD
=======
                'constraint'     => 11,
>>>>>>> 57646d5 (Initial commit)
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'doctor_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'user_id' => [
                'type'       => 'INT',
<<<<<<< HEAD
                'unsigned'   => true,
                'null'       => true,
=======
                'constraint' => 11,
                'unsigned'   => true,
>>>>>>> 57646d5 (Initial commit)
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
<<<<<<< HEAD
                'null'       => true,
=======
>>>>>>> 57646d5 (Initial commit)
            ],
            'specialization' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'license_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
<<<<<<< HEAD
            'experience_years' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'qualification' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
=======
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'qualification' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'experience_years' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 0,
>>>>>>> 57646d5 (Initial commit)
            ],
            'consultation_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
<<<<<<< HEAD
            'schedule' => [
                'type' => 'TEXT',
                'null' => true,
            ],
=======
>>>>>>> 57646d5 (Initial commit)
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave'],
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

        $this->forge->addKey('id', true);
<<<<<<< HEAD
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
=======
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
>>>>>>> 57646d5 (Initial commit)
        $this->forge->createTable('doctors');
    }

    public function down()
    {
        $this->forge->dropTable('doctors');
    }
}
