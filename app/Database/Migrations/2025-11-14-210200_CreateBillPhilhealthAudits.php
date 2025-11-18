<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillPhilhealthAudits extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'bill_id' => ['type' => 'INT', 'unsigned' => true],
            'patient_id' => ['type' => 'VARCHAR', 'constraint' => 20],
            'suggested_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'approved_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'officer_user_id' => ['type' => 'VARCHAR', 'constraint' => 20],
            'codes_used' => ['type' => 'TEXT', 'null' => true],
            'rate_ids' => ['type' => 'TEXT', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('bill_id');
        $this->forge->addForeignKey('bill_id', 'billing', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bill_philhealth_audits', true);
    }

    public function down()
    {
        $this->forge->dropTable('bill_philhealth_audits', true);
    }
}
