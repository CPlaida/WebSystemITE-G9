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
        $this->forge->addKey(['patient_id']);
        // Foreign keys will be added after tables are created
        $this->forge->createTable('hmo_authorizations', true);
        
        // Add foreign key constraints after tables exist
        if ($this->db->tableExists('billing') && $this->db->tableExists('hmo_authorizations')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('hmo_authorizations');
                $hasBillingFk = false;
                $hasProviderFk = false;
                $hasPatientFk = false;
                
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name)) {
                        if ($fk->foreign_table_name === 'billing') {
                            $hasBillingFk = true;
                        } elseif ($fk->foreign_table_name === 'hmo_providers') {
                            $hasProviderFk = true;
                        } elseif ($fk->foreign_table_name === 'patients') {
                            $hasPatientFk = true;
                        }
                    }
                }
                
                if (!$hasBillingFk) {
                    $this->db->query("ALTER TABLE hmo_authorizations 
                        ADD CONSTRAINT fk_hmo_auth_billing 
                        FOREIGN KEY (billing_id) 
                        REFERENCES billing(id) 
                        ON DELETE CASCADE 
                        ON UPDATE CASCADE");
                }
                
                if (!$hasProviderFk && $this->db->tableExists('hmo_providers')) {
                    $this->db->query("ALTER TABLE hmo_authorizations 
                        ADD CONSTRAINT fk_hmo_auth_provider 
                        FOREIGN KEY (provider_id) 
                        REFERENCES hmo_providers(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
                
                if (!$hasPatientFk && $this->db->tableExists('patients')) {
                    $this->db->query("ALTER TABLE hmo_authorizations 
                        ADD CONSTRAINT fk_hmo_auth_patient 
                        FOREIGN KEY (patient_id) 
                        REFERENCES patients(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraints may already exist, ignore error
            }
        }

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
            'hmo_authorization_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'comment' => 'FK to hmo_authorizations.id (normalized HMO data)',
                'after' => 'hmo_notes',
            ],
        ]);
        
        // Add FK for patients.hmo_provider_id
        if ($this->db->tableExists('patients') && $this->db->fieldExists('hmo_provider_id', 'patients')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('patients');
                $hasFk = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'hmo_providers') {
                        $hasFk = true;
                        break;
                    }
                }
                if (!$hasFk && $this->db->tableExists('hmo_providers')) {
                    $this->db->query("ALTER TABLE patients 
                        ADD CONSTRAINT fk_patients_hmo_provider 
                        FOREIGN KEY (hmo_provider_id) 
                        REFERENCES hmo_providers(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK may already exist
            }
        }
        
        // Add FK for billing.hmo_authorization_id
        if ($this->db->tableExists('billing') && $this->db->fieldExists('hmo_authorization_id', 'billing')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('billing');
                $hasFk = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'hmo_authorizations') {
                        $hasFk = true;
                        break;
                    }
                }
                if (!$hasFk && $this->db->tableExists('hmo_authorizations')) {
                    $this->db->query("CREATE INDEX IF NOT EXISTS idx_billing_hmo_auth ON billing(hmo_authorization_id)");
                    $this->db->query("ALTER TABLE billing 
                        ADD CONSTRAINT fk_billing_hmo_auth 
                        FOREIGN KEY (hmo_authorization_id) 
                        REFERENCES hmo_authorizations(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK may already exist
            }
        }
        
        // Migrate existing HMO data from billing to hmo_authorizations
        $this->migrateHmoData();
    }
    
    /**
     * Migrate HMO data from billing table to hmo_authorizations table
     */
    protected function migrateHmoData(): void
    {
        if (!$this->db->tableExists('billing') || !$this->db->tableExists('hmo_authorizations')) {
            return;
        }

        // Check if billing has HMO fields
        $billingFields = $this->db->getFieldNames('billing');
        $hasHmoFields = in_array('hmo_provider_id', $billingFields) && 
                       in_array('hmo_loa_number', $billingFields);

        if (!$hasHmoFields) {
            return; // No HMO data to migrate
        }

        // Get all bills with HMO data that don't have hmo_authorization_id set
        $bills = $this->db->table('billing')
            ->select('id, patient_id, hmo_provider_id, hmo_member_no, hmo_valid_from, hmo_valid_to, 
                     hmo_loa_number, hmo_coverage_limit, hmo_approved_amount, hmo_patient_share, 
                     hmo_status, hmo_notes')
            ->where('hmo_provider_id IS NOT NULL', null, false)
            ->where('hmo_authorization_id IS NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($bills as $bill) {
            // Check if authorization already exists for this bill
            $existing = $this->db->table('hmo_authorizations')
                ->where('billing_id', $bill['id'])
                ->get()
                ->getRowArray();

            if ($existing) {
                // Update billing to reference existing authorization
                $this->db->table('billing')
                    ->where('id', $bill['id'])
                    ->update(['hmo_authorization_id' => $existing['id']]);
                continue;
            }

            // Create new authorization record
            $authData = [
                'billing_id' => $bill['id'],
                'patient_id' => $bill['patient_id'] ?? null,
                'provider_id' => $bill['hmo_provider_id'] ?? null,
                'loa_number' => $bill['hmo_loa_number'] ?? null,
                'coverage_limit' => $bill['hmo_coverage_limit'] ?? null,
                'approved_amount' => $bill['hmo_approved_amount'] ?? null,
                'patient_share' => $bill['hmo_patient_share'] ?? null,
                'status' => $bill['hmo_status'] ?? null,
                'notes' => $bill['hmo_notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            try {
                $this->db->table('hmo_authorizations')->insert($authData);
                $authId = $this->db->insertID();

                // Update billing to reference the new authorization
                if ($authId) {
                    $this->db->table('billing')
                        ->where('id', $bill['id'])
                        ->update(['hmo_authorization_id' => $authId]);
                }
            } catch (\Exception $e) {
                // Log error but continue
                log_message('error', 'Failed to migrate HMO data for bill ' . $bill['id'] . ': ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('hmo_authorizations', true);
        $this->forge->dropTable('hmo_providers', true);

        // Drop columns from patients table if they exist
        if ($this->db->tableExists('patients')) {
            // Drop FK constraint first if it exists
            if ($this->db->fieldExists('hmo_provider_id', 'patients')) {
                try {
                    $foreignKeys = $this->db->getForeignKeyData('patients');
                    foreach ($foreignKeys as $fk) {
                        if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'hmo_providers') {
                            $constraintName = $fk->constraint_name ?? 'fk_patients_hmo_provider';
                            $this->db->query("ALTER TABLE patients DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // FK may not exist, ignore error
                }
            }
            
            $columnsToDrop = [];
            if ($this->db->fieldExists('hmo_provider_id', 'patients')) {
                $columnsToDrop[] = 'hmo_provider_id';
            }
            if ($this->db->fieldExists('hmo_member_no', 'patients')) {
                $columnsToDrop[] = 'hmo_member_no';
            }
            if ($this->db->fieldExists('hmo_valid_from', 'patients')) {
                $columnsToDrop[] = 'hmo_valid_from';
            }
            if ($this->db->fieldExists('hmo_valid_to', 'patients')) {
                $columnsToDrop[] = 'hmo_valid_to';
            }
            if (!empty($columnsToDrop)) {
                $this->forge->dropColumn('patients', $columnsToDrop);
            }
        }

        // Drop columns from billing table if they exist
        if ($this->db->tableExists('billing')) {
            $columnsToDrop = [];
            if ($this->db->fieldExists('hmo_authorization_id', 'billing')) {
                $columnsToDrop[] = 'hmo_authorization_id';
            }
            if ($this->db->fieldExists('hmo_provider_id', 'billing')) {
                $columnsToDrop[] = 'hmo_provider_id';
            }
            if ($this->db->fieldExists('hmo_member_no', 'billing')) {
                $columnsToDrop[] = 'hmo_member_no';
            }
            if ($this->db->fieldExists('hmo_valid_from', 'billing')) {
                $columnsToDrop[] = 'hmo_valid_from';
            }
            if ($this->db->fieldExists('hmo_valid_to', 'billing')) {
                $columnsToDrop[] = 'hmo_valid_to';
            }
            if ($this->db->fieldExists('hmo_loa_number', 'billing')) {
                $columnsToDrop[] = 'hmo_loa_number';
            }
            if ($this->db->fieldExists('hmo_coverage_limit', 'billing')) {
                $columnsToDrop[] = 'hmo_coverage_limit';
            }
            if ($this->db->fieldExists('hmo_approved_amount', 'billing')) {
                $columnsToDrop[] = 'hmo_approved_amount';
            }
            if ($this->db->fieldExists('hmo_patient_share', 'billing')) {
                $columnsToDrop[] = 'hmo_patient_share';
            }
            if ($this->db->fieldExists('hmo_status', 'billing')) {
                $columnsToDrop[] = 'hmo_status';
            }
            if ($this->db->fieldExists('hmo_notes', 'billing')) {
                $columnsToDrop[] = 'hmo_notes';
            }
            if (!empty($columnsToDrop)) {
                $this->forge->dropColumn('billing', $columnsToDrop);
            }
        }
    }
}
