<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrescriptionsTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'patient_id' => ['type'=>'VARCHAR','constraint'=>20],
            'date' => ['type'=>'DATE'],
            'payment_method' => ['type'=>'ENUM','constraint'=>['cash','insurance'],'default'=>'cash'],
            'subtotal' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'tax' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'total_amount' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('patient_id');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('prescriptions', true);

        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'prescription_id' => ['type'=>'INT','unsigned'=>true],
            'medication_id' => ['type'=>'VARCHAR','constraint'=>20],
            'quantity' => ['type'=>'INT','default'=>1],
            'price' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'total' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('prescription_id');
        $this->forge->addKey('medication_id');
        $this->forge->addForeignKey('prescription_id', 'prescriptions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('prescription_items', true);

        // Add FK for medication_id after table is created (medicines table exists before this migration)
        if ($this->db->tableExists('medicines') && $this->db->tableExists('prescription_items')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('prescription_items');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'medicines' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE prescription_items 
                        ADD CONSTRAINT fk_prescription_items_medication 
                        FOREIGN KEY (medication_id) 
                        REFERENCES medicines(id) 
                        ON DELETE CASCADE 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }

        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'transaction_number' => ['type'=>'VARCHAR','constraint'=>50],
            'patient_id' => ['type'=>'VARCHAR','constraint'=>20],
            'prescription_id' => ['type'=>'INT','unsigned'=>true,'null'=>true,'comment'=>'FK to prescriptions.id'],
            'date' => ['type'=>'DATE'],
            'total_items' => ['type'=>'INT','default'=>0],
            'total_amount' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transaction_number');
        $this->forge->addKey('patient_id');
        $this->forge->addKey('prescription_id');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        // FK for prescription_id will be added after table is created
        $this->forge->createTable('pharmacy_transactions', true);
        
        // Add FK for prescription_id after table is created
        if ($this->db->tableExists('prescriptions') && $this->db->tableExists('pharmacy_transactions')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('pharmacy_transactions');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'prescriptions' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE pharmacy_transactions 
                        ADD CONSTRAINT fk_pharmacy_transactions_prescription 
                        FOREIGN KEY (prescription_id) 
                        REFERENCES prescriptions(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('pharmacy_transactions', true);
        $this->forge->dropTable('prescription_items', true);
        $this->forge->dropTable('prescriptions', true);
    }
}