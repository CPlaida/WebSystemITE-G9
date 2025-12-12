<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatientVitalsTable extends Migration
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
            'patient_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'blood_pressure' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
                'comment'    => 'e.g. 120/80',
            ],
            'heart_rate' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'beats per minute',
            ],
            'temperature' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,1',
                'null'       => true,
                'comment'    => 'e.g. 36.7',
            ],
            'recorded_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'users.id of recorder (VARCHAR to match users.id type)',
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
        $this->forge->addKey('patient_id');
        $this->forge->addKey('recorded_by');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recorded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('patient_vitals', true);
    }

    public function down()
    {
        $this->forge->dropTable('patient_vitals', true);
    }
}


