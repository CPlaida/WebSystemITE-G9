<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHmoSupport extends Migration
{
    public function up()
    {
        // HMO providers directory
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'contact_person' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'hotline' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->createTable('hmo_providers', true);

        // HMO authorization tracker per bill
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'billing_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'patient_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'provider_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'loa_number' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'coverage_limit' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
            ],
            'approved_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
            ],
            'patient_share' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
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
        $this->forge->addKey(['billing_id']);
        $this->forge->addKey(['provider_id']);
        $this->forge->createTable('hmo_authorizations', true);

        // Extend patients table with optional HMO enrollment
        $this->forge->addColumn('patients', [
            'hmo_provider_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'insurance_number',
            ],
            'hmo_member_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'hmo_provider_id',
            ],
            'hmo_valid_from' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'hmo_member_no',
            ],
            'hmo_valid_to' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'hmo_valid_from',
            ],
        ]);

        // Extend billing table for HMO settlement data
        $this->forge->addColumn('billing', [
            'hmo_provider_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'payment_method',
            ],
            'hmo_member_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'hmo_provider_id',
            ],
            'hmo_valid_from' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'hmo_member_no',
            ],
            'hmo_valid_to' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'hmo_valid_from',
            ],
            'hmo_loa_number' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'hmo_valid_to',
            ],
            'hmo_coverage_limit' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'after' => 'hmo_loa_number',
            ],
            'hmo_approved_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'after' => 'hmo_coverage_limit',
            ],
            'hmo_patient_share' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'after' => 'hmo_approved_amount',
            ],
            'hmo_status' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'hmo_patient_share',
            ],
            'hmo_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'hmo_status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('hmo_authorizations', true);
        $this->forge->dropTable('hmo_providers', true);

        $this->forge->dropColumn('patients', ['hmo_provider_id', 'hmo_member_no', 'hmo_valid_from', 'hmo_valid_to']);

        $this->forge->dropColumn('billing', [
            'hmo_provider_id',
            'hmo_member_no',
            'hmo_loa_number',
            'hmo_coverage_limit',
            'hmo_approved_amount',
            'hmo_patient_share',
            'hmo_status',
            'hmo_notes',
        ]);
    }
}
