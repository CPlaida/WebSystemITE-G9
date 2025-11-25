<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffProfilesTable extends Migration
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
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'specialization_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'hire_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave'],
                'default'    => 'active',
            ],
            'emergency_contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'emergency_contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
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
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('department_id', 'staff_departments', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('specialization_id', 'staff_specializations', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('staff_profiles', true);
    }

    public function down()
    {
        $this->forge->dropTable('staff_profiles', true);
    }
}
