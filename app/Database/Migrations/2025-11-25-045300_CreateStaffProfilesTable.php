<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'license_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'role_id',
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'specialization_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'hire_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave'],
                'default'    => 'active',
            ],
            'emergency_contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'emergency_contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
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
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('department_id', 'staff_departments', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('specialization_id', 'staff_specializations', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('staff_profiles', true);

        // Migrate existing doctors data to staff_profiles if doctors table exists
        if ($this->db->tableExists('doctors')) {
            $this->migrateDoctorsToStaffProfiles();
        }

        // Add FK constraints for doctor_id fields in other tables
        // admission_details.attending_doctor_id
        if ($this->db->tableExists('admission_details') && $this->db->fieldExists('attending_doctor_id', 'admission_details')) {
            // Get existing foreign keys to check if we need to update
            $foreignKeys = $this->db->getForeignKeyData('admission_details');
            $hasOldFk = false;
            $hasNewFk = false;
            
            foreach ($foreignKeys as $fk) {
                if (isset($fk->foreign_table_name)) {
                    if ($fk->foreign_table_name === 'doctors') {
                        $hasOldFk = true;
                        // Drop old FK to doctors
                        try {
                            $constraintName = $fk->constraint_name ?? 'admission_details_ibfk_2';
                            $this->db->query("ALTER TABLE admission_details DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                        } catch (\Exception $e) {
                            // Ignore if FK doesn't exist or can't be dropped
                        }
                    } elseif ($fk->foreign_table_name === 'staff_profiles') {
                        $hasNewFk = true;
                    }
                }
            }
            
            // Add new FK to staff_profiles if it doesn't exist
            if (!$hasNewFk) {
                try {
                    $this->db->query("ALTER TABLE admission_details 
                        ADD CONSTRAINT fk_admission_attending_doctor 
                        FOREIGN KEY (attending_doctor_id) 
                        REFERENCES staff_profiles(id) 
                        ON DELETE RESTRICT 
                        ON UPDATE CASCADE");
                } catch (\Exception $e) {
                    // FK constraint may already exist or other issue, ignore error
                }
            }
        }

        // laboratory.doctor_id
        if ($this->db->tableExists('laboratory') && $this->db->fieldExists('doctor_id', 'laboratory')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('laboratory');
                $hasOldFk = false;
                $hasNewFk = false;
                
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name)) {
                        if ($fk->foreign_table_name === 'users') {
                            $hasOldFk = true;
                            // Drop old FK to users
                            try {
                                $constraintName = $fk->constraint_name ?? 'laboratory_ibfk_2';
                                $this->db->query("ALTER TABLE laboratory DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            } catch (\Exception $e) {
                                // Ignore if FK doesn't exist
                            }
                        } elseif ($fk->foreign_table_name === 'staff_profiles') {
                            $hasNewFk = true;
                        }
                    }
                }
                
                // Add new FK to staff_profiles if it doesn't exist
                if (!$hasNewFk) {
                    try {
                        $this->db->query("ALTER TABLE laboratory 
                            ADD CONSTRAINT fk_laboratory_doctor 
                            FOREIGN KEY (doctor_id) 
                            REFERENCES staff_profiles(id) 
                            ON DELETE SET NULL 
                            ON UPDATE CASCADE");
                    } catch (\Exception $e) {
                        // FK constraint may already exist or other issue, ignore error
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        // Drop doctors table after migration is complete (consolidated into staff_profiles)
        // First, drop any remaining foreign key constraints that reference doctors table
        if ($this->db->tableExists('doctors')) {
            try {
                // Drop FK from admission_details if it still references doctors
                if ($this->db->tableExists('admission_details')) {
                    $foreignKeys = $this->db->getForeignKeyData('admission_details');
                    foreach ($foreignKeys as $fk) {
                        if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'doctors') {
                            $constraintName = $fk->constraint_name ?? 'admission_details_ibfk_2';
                            try {
                                $this->db->query("ALTER TABLE admission_details DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            } catch (\Exception $e) {
                                // Constraint might not exist or have different name
                            }
                        }
                    }
                }
                
                // Now safe to drop doctors table
                $this->forge->dropTable('doctors', true);
            } catch (\Exception $e) {
                // If drop fails, log but don't stop migration
                // Table might still be referenced elsewhere or have data dependencies
                log_message('info', 'Could not drop doctors table: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('staff_profiles', true);
    }

    /**
     * Migrate existing doctors data to staff_profiles table
     * This consolidates doctors table into the unified staff_profiles structure
     */
    private function migrateDoctorsToStaffProfiles(): void
    {
        if (!$this->db->tableExists('doctors')) {
            return;
        }

        $doctors = $this->db->table('doctors')->get()->getResultArray();
        $staffProfiles = $this->db->table('staff_profiles');
        $roles = $this->db->table('roles')->where('name', 'doctor')->get()->getRowArray();
        $doctorRoleId = $roles['id'] ?? null;

        foreach ($doctors as $doctor) {
            // Check if staff profile already exists for this user_id
            $existing = null;
            if (!empty($doctor['user_id'])) {
                $existing = $staffProfiles->where('user_id', $doctor['user_id'])->get()->getRowArray();
            }

            if ($existing) {
                // Update existing staff profile with doctor data
                $updateData = [
                    'first_name' => $doctor['first_name'] ?? $existing['first_name'],
                    'last_name' => $doctor['last_name'] ?? $existing['last_name'],
                    'email' => $doctor['email'] ?? $existing['email'],
                    'phone' => $doctor['phone'] ?? $existing['phone'],
                    'license_number' => $doctor['license_number'] ?? $existing['license_number'],
                    'specialization_id' => $doctor['specialization_id'] ?? $existing['specialization_id'],
                    'status' => $doctor['status'] ?? $existing['status'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $staffProfiles->where('id', $existing['id'])->update($updateData);
            } else {
                // Create new staff profile from doctor data
                $insertData = [
                    'user_id' => $doctor['user_id'] ?? null,
                    'first_name' => $doctor['first_name'] ?? '',
                    'last_name' => $doctor['last_name'] ?? '',
                    'email' => $doctor['email'] ?? null,
                    'phone' => $doctor['phone'] ?? null,
                    'role_id' => $doctorRoleId,
                    'license_number' => $doctor['license_number'] ?? null,
                    'specialization_id' => $doctor['specialization_id'] ?? null,
                    'status' => $doctor['status'] ?? 'active',
                    'created_at' => $doctor['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $doctor['updated_at'] ?? date('Y-m-d H:i:s'),
                ];
                $staffProfiles->insert($insertData);
            }
        }
    }
}
