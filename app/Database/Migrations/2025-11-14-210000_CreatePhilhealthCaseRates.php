<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhilhealthCaseRates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code_type' => ['type' => 'ENUM', 'constraint' => ['RVS','ICD']],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'case_type' => ['type' => 'ENUM', 'constraint' => ['A','B']],
            'rate_total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'facility_share' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'professional_share' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'effective_from' => ['type' => 'DATE'],
            'effective_to' => ['type' => 'DATE', 'null' => true],
            'active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['code_type','code','case_type','effective_from']);
        $this->forge->createTable('philhealth_case_rates', true);
    }

    public function down()
    {
        $this->forge->dropTable('philhealth_case_rates', true);
    }
}
