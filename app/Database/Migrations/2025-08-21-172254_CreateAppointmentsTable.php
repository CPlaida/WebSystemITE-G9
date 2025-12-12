<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'patient_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK to staff_profiles.id (consolidated from users.id)',
            ],
            'appointment_date' => [
                'type' => 'DATE',
            ],
            'appointment_time' => [
                'type' => 'TIME',
            ],
            'appointment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['consultation', 'follow_up', 'emergency', 'routine_checkup'],
                'default'    => 'consultation',
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK to services.id (consultation fee service)',
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'],
                'default'    => 'scheduled',
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
        $this->forge->addKey('service_id');
        $this->forge->addKey('doctor_id');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        // FK for doctor_id will be added after staff_profiles table is created
        // FK for service_id will be added after services table is created
        $this->forge->createTable('appointments');
        
        // Add FK for doctor_id after staff_profiles table is created
        if ($this->db->tableExists('staff_profiles') && $this->db->tableExists('appointments')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('appointments');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'staff_profiles' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    // Drop old FK to users if it exists
                    foreach ($foreignKeys as $fk) {
                        if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'users') {
                            $constraintName = $fk->constraint_name ?? 'appointments_ibfk_2';
                            try {
                                $this->db->query("ALTER TABLE appointments DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            } catch (\Exception $e) {
                                // Ignore if FK doesn't exist
                            }
                        }
                    }
                    // Add new FK to staff_profiles
                    $this->db->query("ALTER TABLE appointments 
                        ADD CONSTRAINT fk_appointments_doctor 
                        FOREIGN KEY (doctor_id) 
                        REFERENCES staff_profiles(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }
        
        // Migrate existing data if table already exists with old structure
        $this->migrateAppointmentsDoctorId();
    }

    public function down()
    {
        $this->forge->dropTable('appointments');
    }
    
    /**
     * Migrate appointments.doctor_id from users.id (VARCHAR) to staff_profiles.id (INT)
     */
    protected function migrateAppointmentsDoctorId(): void
    {
        if (!$this->db->tableExists('appointments') || !$this->db->tableExists('staff_profiles')) {
            return;
        }
        
        // Check if doctor_id is still VARCHAR (old structure)
        $fields = $this->db->getFieldData('appointments');
        $doctorIdField = null;
        foreach ($fields as $field) {
            if ($field->name === 'doctor_id') {
                $doctorIdField = $field;
                break;
            }
        }
        
        // If doctor_id is VARCHAR, we need to migrate
        if ($doctorIdField && strpos(strtolower($doctorIdField->type), 'varchar') !== false) {
            // Get all appointments with doctor_id
            $appointments = $this->db->table('appointments')
                ->select('id, doctor_id')
                ->where('doctor_id IS NOT NULL', null, false)
                ->get()
                ->getResultArray();
            
            if (empty($appointments)) {
                // No data to migrate, just change column type
                try {
                    $this->db->query("ALTER TABLE appointments 
                        MODIFY COLUMN doctor_id INT(11) UNSIGNED NULL 
                        COMMENT 'FK to staff_profiles.id (consolidated from users.id)'");
                } catch (\Exception $e) {
                    // Ignore errors
                }
                return;
            }
            
            // Migrate each appointment's doctor_id
            foreach ($appointments as $appointment) {
                $oldUserId = $appointment['doctor_id'];
                if (empty($oldUserId)) {
                    continue;
                }
                
                // Find staff_profiles.id for this user_id
                $staffProfile = $this->db->table('staff_profiles')
                    ->select('id')
                    ->where('user_id', $oldUserId)
                    ->get()
                    ->getRowArray();
                
                if ($staffProfile && isset($staffProfile['id'])) {
                    // Update appointment with staff_profiles.id
                    $this->db->table('appointments')
                        ->where('id', $appointment['id'])
                        ->update(['doctor_id' => (int)$staffProfile['id']]);
                } else {
                    // No staff profile found, set to NULL
                    $this->db->table('appointments')
                        ->where('id', $appointment['id'])
                        ->update(['doctor_id' => null]);
                }
            }
            
            // Change column type after data migration
            try {
                $this->db->query("ALTER TABLE appointments 
                    MODIFY COLUMN doctor_id INT(11) UNSIGNED NULL 
                    COMMENT 'FK to staff_profiles.id (consolidated from users.id)'");
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
    }
}
