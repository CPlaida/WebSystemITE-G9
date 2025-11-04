<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctorSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'doctor_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'doctor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'shift_type' => [
                'type'       => 'ENUM',
                'constraint' => ['morning', 'afternoon', 'night'],
            ],
            'preferred_days' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'JSON array of preferred working days'
            ],
            'is_available' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'consecutive_nights' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
                'comment' => 'Track consecutive night shifts'
            ],
            'monthly_shift_count' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'swap_request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'is_on_leave' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'shift_date' => [
                'type' => 'DATE',
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'completed', 'cancelled'],
                'default'    => 'scheduled',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('doctor_id');
        $this->forge->addKey('shift_date');
        $this->forge->addForeignKey('doctor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('doctor_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('doctor_schedules');
    }
}
