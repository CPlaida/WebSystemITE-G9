<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctorSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
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
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK to staff_departments.id (FK constraint added in later migration)',
            ],
            'shift_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'preferred_days' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'JSON array of preferred working days'
            ],
            'is_available' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'consecutive_nights' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
                'comment' => 'Track consecutive night shifts'
            ],
            'monthly_shift_count' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'swap_request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'is_on_leave' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'shift_date' => [
                'type' => 'DATE',
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'completed', 'cancelled'],
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
        $this->forge->addKey('doctor_id');
        $this->forge->addKey('shift_date');
        $this->forge->addKey('department_id');
        // FK for doctor_id will be added after staff_profiles table is created
        // Note: FK constraint for department_id will be added after staff_departments table is created
        $this->forge->createTable('doctor_schedules');
        
        // Add FK for doctor_id after staff_profiles table is created
        if ($this->db->tableExists('staff_profiles') && $this->db->tableExists('doctor_schedules')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('doctor_schedules');
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
                            $constraintName = $fk->constraint_name ?? 'doctor_schedules_ibfk_1';
                            try {
                                $this->db->query("ALTER TABLE doctor_schedules DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            } catch (\Exception $e) {
                                // Ignore if FK doesn't exist
                            }
                        }
                    }
                    // Add new FK to staff_profiles
                    $this->db->query("ALTER TABLE doctor_schedules 
                        ADD CONSTRAINT fk_doctor_schedules_doctor 
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
        $this->migrateDoctorSchedulesDoctorId();
    }

    public function down()
    {
        $this->forge->dropTable('doctor_schedules');
    }
    
    /**
     * Migrate doctor_schedules.doctor_id from users.id (VARCHAR) to staff_profiles.id (INT)
     */
    protected function migrateDoctorSchedulesDoctorId(): void
    {
        if (!$this->db->tableExists('doctor_schedules') || !$this->db->tableExists('staff_profiles')) {
            return;
        }
        
        // Check if doctor_id is still VARCHAR (old structure)
        $fields = $this->db->getFieldData('doctor_schedules');
        $doctorIdField = null;
        foreach ($fields as $field) {
            if ($field->name === 'doctor_id') {
                $doctorIdField = $field;
                break;
            }
        }
        
        // If doctor_id is VARCHAR, we need to migrate
        if ($doctorIdField && strpos(strtolower($doctorIdField->type), 'varchar') !== false) {
            // Get all schedules with doctor_id
            $schedules = $this->db->table('doctor_schedules')
                ->select('id, doctor_id')
                ->where('doctor_id IS NOT NULL', null, false)
                ->get()
                ->getResultArray();
            
            if (empty($schedules)) {
                // No data to migrate, just change column type
                try {
                    $this->db->query("ALTER TABLE doctor_schedules 
                        MODIFY COLUMN doctor_id INT(11) UNSIGNED NULL 
                        COMMENT 'FK to staff_profiles.id (consolidated from users.id)'");
                } catch (\Exception $e) {
                    // Ignore errors
                }
                return;
            }
            
            // Migrate each schedule's doctor_id
            foreach ($schedules as $schedule) {
                $oldUserId = $schedule['doctor_id'];
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
                    // Update schedule with staff_profiles.id
                    $this->db->table('doctor_schedules')
                        ->where('id', $schedule['id'])
                        ->update(['doctor_id' => (int)$staffProfile['id']]);
                } else {
                    // No staff profile found, set to NULL
                    $this->db->table('doctor_schedules')
                        ->where('id', $schedule['id'])
                        ->update(['doctor_id' => null]);
                }
            }
            
            // Change column type after data migration
            try {
                $this->db->query("ALTER TABLE doctor_schedules 
                    MODIFY COLUMN doctor_id INT(11) UNSIGNED NULL 
                    COMMENT 'FK to staff_profiles.id (consolidated from users.id)'");
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
    }
}
