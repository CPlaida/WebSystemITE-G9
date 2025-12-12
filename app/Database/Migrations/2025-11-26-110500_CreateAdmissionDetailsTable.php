<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmissionDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'admission_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'admission_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'admission_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'attending_doctor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'comment' => 'FK to staff_profiles.id (consolidated from doctors table)',
            ],
            'ward' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'room' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'bed_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'admitting_diagnosis' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'reason_admission' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'admitted',
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
        $this->forge->addKey('patient_id');
        $this->forge->addKey('bed_id');
        $this->forge->addKey('attending_doctor_id');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        // Changed from doctors.id to staff_profiles.id for consolidation
        // Note: If staff_profiles table doesn't exist yet, FK will be added after table creation
        if ($this->db->tableExists('staff_profiles')) {
            $this->forge->addForeignKey('attending_doctor_id', 'staff_profiles', 'id', 'RESTRICT', 'CASCADE');
        }
        $this->forge->addForeignKey('bed_id', 'beds', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('admission_details');
        
        // Add FK constraint if staff_profiles exists (for cases where admission_details is created first)
        if ($this->db->tableExists('staff_profiles') && $this->db->tableExists('admission_details')) {
            try {
                // Check if FK constraint already exists
                $foreignKeys = $this->db->getForeignKeyData('admission_details');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'staff_profiles' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE admission_details 
                        ADD CONSTRAINT fk_admission_attending_doctor 
                        FOREIGN KEY (attending_doctor_id) 
                        REFERENCES staff_profiles(id) 
                        ON DELETE RESTRICT 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist or table structure issue, ignore error
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('admission_details');
    }
}
