<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingTable extends Migration
{
    public function up()
    {
        // Billing header table
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'patient_id'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'appointment_id'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            // Keep the column; we will add FK later when services table exists
            'service_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],

            'consultation_fee' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'medication_cost'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'lab_tests_cost'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'other_charges'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'total_amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'discount'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'tax'              => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'final_amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'payment_status'   => ['type' => 'ENUM', 'constraint' => ['pending','partial','paid','overdue'], 'default' => 'pending'],
            'payment_method'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'bill_date'        => ['type' => 'DATE'],
            'due_date'         => ['type' => 'DATE', 'null' => true],
            'notes'            => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('appointment_id', 'appointments', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('billing', true);

        // Billing items table
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'billing_id' => ['type' => 'INT', 'unsigned' => true],
            'service'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'qty'        => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'price'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('billing_id', 'billing', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('billing_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('billing_items', true);
        $this->forge->dropTable('billing', true);
    }
}