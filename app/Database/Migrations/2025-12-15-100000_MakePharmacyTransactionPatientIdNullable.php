<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakePharmacyTransactionPatientIdNullable extends Migration
{
    public function up()
    {
        // Make patient_id nullable in both prescriptions and pharmacy_transactions tables
        // This allows OPD walk-in patients without a patient record
        
        // Fix prescriptions table
        if ($this->db->tableExists('prescriptions')) {
            try {
                // Drop the foreign key constraint first
                $foreignKeys = $this->db->getForeignKeyData('prescriptions');
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'patients' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $constraintName = $fk->constraint_name ?? 'prescriptions_patient_id_foreign';
                        $this->db->query("ALTER TABLE prescriptions DROP FOREIGN KEY `{$constraintName}`");
                        break;
                    }
                }
                
                // Modify column to allow NULL
                $this->db->query("ALTER TABLE prescriptions MODIFY COLUMN patient_id VARCHAR(20) NULL");
                
                // Re-add foreign key constraint (allows NULL)
                $this->db->query("ALTER TABLE prescriptions 
                    ADD CONSTRAINT prescriptions_patient_id_foreign 
                    FOREIGN KEY (patient_id) 
                    REFERENCES patients(id) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE");
            } catch (\Exception $e) {
                // If constraint doesn't exist or other error, try to just modify the column
                try {
                    $this->db->query("ALTER TABLE prescriptions MODIFY COLUMN patient_id VARCHAR(20) NULL");
                } catch (\Exception $e2) {
                    log_message('error', 'Failed to make prescriptions.patient_id nullable: ' . $e2->getMessage());
                }
            }
        }
        
        // Fix pharmacy_transactions table
        if ($this->db->tableExists('pharmacy_transactions')) {
            try {
                // Drop the foreign key constraint first
                $foreignKeys = $this->db->getForeignKeyData('pharmacy_transactions');
                $constraintDropped = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'patients' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $constraintName = $fk->constraint_name ?? 'pharmacy_transactions_patient_id_foreign';
                        $this->db->query("ALTER TABLE pharmacy_transactions DROP FOREIGN KEY `{$constraintName}`");
                        $constraintDropped = true;
                        break;
                    }
                }
                
                // Modify column to allow NULL
                $this->db->query("ALTER TABLE pharmacy_transactions MODIFY COLUMN patient_id VARCHAR(20) NULL");
                
                // Re-add foreign key constraint only if we dropped it (allows NULL)
                if ($constraintDropped) {
                    // Use a unique constraint name to avoid duplicates
                    $this->db->query("ALTER TABLE pharmacy_transactions 
                        ADD CONSTRAINT pharmacy_transactions_patient_id_fk 
                        FOREIGN KEY (patient_id) 
                        REFERENCES patients(id) 
                        ON DELETE CASCADE 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // If constraint doesn't exist or other error, try to just modify the column
                try {
                    $this->db->query("ALTER TABLE pharmacy_transactions MODIFY COLUMN patient_id VARCHAR(20) NULL");
                } catch (\Exception $e2) {
                    log_message('error', 'Failed to make pharmacy_transactions.patient_id nullable: ' . $e2->getMessage());
                }
            }
        }
    }

    public function down()
    {
        // Revert: Make patient_id NOT NULL (but this might fail if there are NULL values)
        if ($this->db->tableExists('pharmacy_transactions')) {
            try {
                // First, update any NULL values to a default (this might not be ideal)
                // For safety, we'll just leave it nullable in the down migration
                // $this->db->query("UPDATE pharmacy_transactions SET patient_id = '' WHERE patient_id IS NULL");
                // $this->db->query("ALTER TABLE pharmacy_transactions MODIFY COLUMN patient_id VARCHAR(20) NOT NULL");
            } catch (\Exception $e) {
                log_message('error', 'Failed to revert patient_id nullable: ' . $e->getMessage());
            }
        }
    }
}

