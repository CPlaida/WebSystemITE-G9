<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchedulingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
            ],
            'day_of_week' => [
                'type'       => 'ENUM',
                'constraint' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'break_start' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'break_end' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'max_patients' => [
                'type'       => 'INT',
                'default'    => 20,
            ],
            'appointment_duration' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 30,
                'comment'    => 'Duration in minutes',
            ],
            'is_available' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
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
        $this->forge->addForeignKey('doctor_id', 'doctors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scheduling');
    }

    public function down()
    {
        $this->forge->dropTable('scheduling');
    }
}
