<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestResultsTable extends Migration
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
            'result_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
            ],
            'request_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'patient_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'test_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'test_date' => [
                'type' => 'DATE',
            ],
            'result_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'normal_ranges' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'abnormal_flags' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'interpretation' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'technician_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'completed', 'verified', 'released'],
                'default'    => 'pending',
            ],
            'critical_values' => [
                'type' => 'JSON',
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
        $this->forge->addUniqueKey('result_id');
        $this->forge->addKey('request_id');
        $this->forge->addKey('status');
        $this->forge->addKey('test_date');
        $this->forge->addKey('created_at');

        $this->forge->createTable('test_results');
    }

    public function down()
    {
        $this->forge->dropTable('test_results');
    }
}
