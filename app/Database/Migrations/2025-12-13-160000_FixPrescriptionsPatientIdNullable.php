<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixPrescriptionsPatientIdNullable extends Migration
{
    public function up()
    {
        // Force fix prescriptions.patient_id to be nullable
        if ($this->db->tableExists('prescriptions')) {
            // Get all foreign keys
            $foreignKeys = $this->db->getForeignKeyData('prescriptions');
            
            // Find and drop the patient_id foreign key
            foreach ($foreignKeys as $fk) {
                if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'patients' && 
                    isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                    $constraintName = $fk->constraint_name ?? 'prescriptions_patient_id_foreign';
                    try {
                        $this->db->query("ALTER TABLE prescriptions DROP FOREIGN KEY `{$constraintName}`");
                        log_message('info', "Dropped foreign key: {$constraintName}");
                    } catch (\Exception $e) {
                        log_message('error', "Could not drop FK (may not exist): " . $e->getMessage());
                    }
                    break;
                }
            }
            
            // Modify column to allow NULL
            try {
                $this->db->query("ALTER TABLE prescriptions MODIFY COLUMN patient_id VARCHAR(20) NULL");
                log_message('info', "Modified prescriptions.patient_id to allow NULL");
            } catch (\Exception $e) {
                log_message('error', "Failed to modify column: " . $e->getMessage());
                throw $e;
            }
            
            // Re-add foreign key constraint (allows NULL)
            try {
                $this->db->query("ALTER TABLE prescriptions 
                    ADD CONSTRAINT prescriptions_patient_id_foreign 
                    FOREIGN KEY (patient_id) 
                    REFERENCES patients(id) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE");
                log_message('info', "Re-added foreign key constraint");
            } catch (\Exception $e) {
                log_message('error', "Could not re-add FK (may already exist): " . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Leave as nullable for safety
    }
}

