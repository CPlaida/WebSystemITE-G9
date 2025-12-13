<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToBilling extends Migration
{
    public function up()
    {
        // Add amount_paid field to billing table
        if (!$this->db->fieldExists('amount_paid', 'billing')) {
            $this->forge->addColumn('billing', [
                'amount_paid' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                    'after' => 'final_amount',
                ],
                'last_payment_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'amount_paid',
                ],
            ]);
        }

        // Remove 'overdue' from payment_status enum and update to only: pending, partial, paid
        if ($this->db->fieldExists('payment_status', 'billing')) {
            // Get current enum values
            $fields = $this->db->getFieldData('billing');
            $currentEnum = null;
            foreach ($fields as $field) {
                if ($field->name === 'payment_status') {
                    $currentEnum = $field;
                    break;
                }
            }

            // Modify enum to remove 'overdue'
            if ($currentEnum) {
                $this->db->query("ALTER TABLE billing MODIFY COLUMN payment_status ENUM('pending', 'partial', 'paid') DEFAULT 'pending'");
            }
        }
    }

    public function down()
    {
        // Remove added fields
        if ($this->db->fieldExists('amount_paid', 'billing')) {
            $this->forge->dropColumn('billing', ['amount_paid', 'last_payment_date']);
        }

        // Restore 'overdue' to enum (if needed for rollback)
        if ($this->db->fieldExists('payment_status', 'billing')) {
            $this->db->query("ALTER TABLE billing MODIFY COLUMN payment_status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending'");
        }
    }
}

