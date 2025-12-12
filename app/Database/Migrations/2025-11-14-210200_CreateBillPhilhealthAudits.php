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
            'patient_id' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'comment' => 'FK to patients.id'],
            'case_rate_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'comment' => 'FK to philhealth_case_rates.id'],
            'suggested_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'approved_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'officer_user_id' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'comment' => 'FK to users.id'],
            'codes_used' => ['type' => 'TEXT', 'null' => true],
            'rate_ids' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Deprecated: use case_rate_id instead'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('bill_id');
        $this->forge->addKey('patient_id');
        $this->forge->addKey('case_rate_id');
        $this->forge->addKey('officer_user_id');
        $this->forge->addForeignKey('bill_id', 'billing', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bill_philhealth_audits', true);
        
        // Add additional foreign key constraints after tables are created
        if ($this->db->tableExists('bill_philhealth_audits')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('bill_philhealth_audits');
                $hasPatientFk = false;
                $hasCaseRateFk = false;
                $hasOfficerFk = false;
                
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name)) {
                        if ($fk->foreign_table_name === 'patients') {
                            $hasPatientFk = true;
                        } elseif ($fk->foreign_table_name === 'philhealth_case_rates') {
                            $hasCaseRateFk = true;
                        } elseif ($fk->foreign_table_name === 'users') {
                            $hasOfficerFk = true;
                        }
                    }
                }
                
                if (!$hasPatientFk && $this->db->tableExists('patients')) {
                    $this->db->query("ALTER TABLE bill_philhealth_audits 
                        ADD CONSTRAINT fk_philhealth_audit_patient 
                        FOREIGN KEY (patient_id) 
                        REFERENCES patients(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
                
                if (!$hasCaseRateFk && $this->db->tableExists('philhealth_case_rates')) {
                    $this->db->query("ALTER TABLE bill_philhealth_audits 
                        ADD CONSTRAINT fk_philhealth_audit_case_rate 
                        FOREIGN KEY (case_rate_id) 
                        REFERENCES philhealth_case_rates(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
                
                if (!$hasOfficerFk && $this->db->tableExists('users')) {
                    $this->db->query("ALTER TABLE bill_philhealth_audits 
                        ADD CONSTRAINT fk_philhealth_audit_officer 
                        FOREIGN KEY (officer_user_id) 
                        REFERENCES users(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraints may already exist, ignore error
            }
        }
        
        // Add philhealth_audit_id to billing table
        if ($this->db->tableExists('billing') && !$this->db->fieldExists('philhealth_audit_id', 'billing')) {
            $this->forge->addColumn('billing', [
                'philhealth_audit_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                    'comment' => 'FK to bill_philhealth_audits.id (normalized PhilHealth data)',
                    'after' => 'philhealth_verified_at',
                ],
            ]);
            
            // Add index and FK
            if ($this->db->tableExists('bill_philhealth_audits')) {
                try {
                    $this->db->query("CREATE INDEX IF NOT EXISTS idx_billing_philhealth_audit ON billing(philhealth_audit_id)");
                    $foreignKeys = $this->db->getForeignKeyData('billing');
                    $hasFk = false;
                    foreach ($foreignKeys as $fk) {
                        if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'bill_philhealth_audits') {
                            $hasFk = true;
                            break;
                        }
                    }
                    if (!$hasFk) {
                        $this->db->query("ALTER TABLE billing 
                            ADD CONSTRAINT fk_billing_philhealth_audit 
                            FOREIGN KEY (philhealth_audit_id) 
                            REFERENCES bill_philhealth_audits(id) 
                            ON DELETE SET NULL 
                            ON UPDATE CASCADE");
                    }
                } catch (\Exception $e) {
                    // FK may already exist
                }
            }
        }
        
        // Migrate existing PhilHealth data from billing to bill_philhealth_audits
        $this->migratePhilhealthData();
    }
    
    /**
     * Migrate PhilHealth data from billing table to bill_philhealth_audits table
     */
    protected function migratePhilhealthData(): void
    {
        if (!$this->db->tableExists('billing') || !$this->db->tableExists('bill_philhealth_audits')) {
            return;
        }

        // Check if billing has PhilHealth fields
        $billingFields = $this->db->getFieldNames('billing');
        $hasPhilhealthFields = in_array('philhealth_member', $billingFields) && 
                              in_array('philhealth_approved_amount', $billingFields);

        if (!$hasPhilhealthFields) {
            return; // No PhilHealth data to migrate
        }

        // Check if philhealth_audit_id column exists using fieldExists (more reliable)
        $hasAuditIdField = $this->db->fieldExists('philhealth_audit_id', 'billing');

        // Build query - use raw SQL if column doesn't exist to avoid query builder issues
        if ($hasAuditIdField) {
            // Column exists - use normal query builder
            $bills = $this->db->table('billing')
                ->select('id, patient_id, philhealth_member, philhealth_suggested_amount, 
                         philhealth_approved_amount, philhealth_codes_used, philhealth_rate_ids, 
                         philhealth_verified_by, philhealth_verified_at')
                ->where('philhealth_member', 1)
                ->where('philhealth_audit_id IS NULL', null, false)
                ->get()
                ->getResultArray();
        } else {
            // Column doesn't exist yet - query without the filter
            $bills = $this->db->table('billing')
                ->select('id, patient_id, philhealth_member, philhealth_suggested_amount, 
                         philhealth_approved_amount, philhealth_codes_used, philhealth_rate_ids, 
                         philhealth_verified_by, philhealth_verified_at')
                ->where('philhealth_member', 1)
                ->get()
                ->getResultArray();
        }

        foreach ($bills as $bill) {
            // Check if audit already exists for this bill
            $existing = $this->db->table('bill_philhealth_audits')
                ->where('bill_id', $bill['id'])
                ->get()
                ->getRowArray();

            if ($existing) {
                // Update billing to reference existing audit
                $this->db->table('billing')
                    ->where('id', $bill['id'])
                    ->update(['philhealth_audit_id' => $existing['id']]);
                continue;
            }

            // Try to find case_rate_id from rate_ids if available
            $caseRateId = null;
            if (!empty($bill['philhealth_rate_ids'])) {
                $rateIds = json_decode($bill['philhealth_rate_ids'], true);
                if (is_array($rateIds) && !empty($rateIds)) {
                    // Use first rate ID
                    $caseRateId = (int)($rateIds[0] ?? null);
                }
            }

            // Create new audit record
            $auditData = [
                'bill_id' => $bill['id'],
                'patient_id' => $bill['patient_id'] ?? null,
                'case_rate_id' => $caseRateId,
                'suggested_amount' => $bill['philhealth_suggested_amount'] ?? 0.00,
                'approved_amount' => $bill['philhealth_approved_amount'] ?? 0.00,
                'officer_user_id' => $bill['philhealth_verified_by'] ?? null,
                'codes_used' => $bill['philhealth_codes_used'] ?? null,
                'rate_ids' => $bill['philhealth_rate_ids'] ?? null, // Keep for backward compatibility
                'notes' => null,
                'created_at' => $bill['philhealth_verified_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            try {
                $this->db->table('bill_philhealth_audits')->insert($auditData);
                $auditId = $this->db->insertID();

                // Update billing to reference the new audit
                if ($auditId) {
                    $this->db->table('billing')
                        ->where('id', $bill['id'])
                        ->update(['philhealth_audit_id' => $auditId]);
                }
            } catch (\Exception $e) {
                // Log error but continue
                log_message('error', 'Failed to migrate PhilHealth data for bill ' . $bill['id'] . ': ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Remove FK from billing if exists
        if ($this->db->tableExists('billing') && $this->db->fieldExists('philhealth_audit_id', 'billing')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('billing');
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'bill_philhealth_audits') {
                        $constraintName = $fk->constraint_name ?? 'fk_billing_philhealth_audit';
                        try {
                            $this->db->query("ALTER TABLE billing DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                        } catch (\Exception $e) {
                            // FK might not exist or have different name
                        }
                        break;
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
            try {
                $this->forge->dropColumn('billing', 'philhealth_audit_id');
            } catch (\Exception $e) {
                // Column might not exist
            }
        }
        
        $this->forge->dropTable('bill_philhealth_audits', true);
    }
}
