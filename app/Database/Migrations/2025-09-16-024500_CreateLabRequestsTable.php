<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabRequestsTable extends Migration
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
            'request_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'patient_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'date_of_birth' => [
                'type' => 'DATE',
            ],
            'test_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'urgent', 'stat'],
                'default'    => 'normal',
            ],
            'clinical_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'test_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'pending',
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
        $this->forge->addUniqueKey('request_id');
        $this->forge->addKey('patient_id');
        $this->forge->addKey('doctor_id');
        $this->forge->addKey('status');
        $this->forge->addKey('test_date');
        $this->forge->addKey('created_at');

        $this->forge->createTable('lab_requests');
    }

    public function down()
    {
        $this->forge->dropTable('lab_requests');
    }
}
