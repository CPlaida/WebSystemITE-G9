<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoryTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('laboratory')) {
            return; 
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'VARCHAR',
                'constraint'     => 20,
                'null'           => false,
            ],
            'patient_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK to staff_profiles.id (doctor who ordered the test)',
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'test_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'test_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'priority' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'routine',
                'null'       => false,
            ],
            'test_date' => [
                'type' => 'DATE',
            ],
            'test_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'test_results' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'normal_range' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'billed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'result_file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'result_file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'result_file_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'result_file_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
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
        $this->forge->addKey('doctor_id');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        // FK for doctor_id will be added after staff_profiles table is created
        // Note: Changed from users.id to staff_profiles.id for consistency with admission_details
        if ($this->db->tableExists('staff_profiles')) {
            $this->forge->addForeignKey('doctor_id', 'staff_profiles', 'id', 'SET NULL', 'CASCADE');
        }
        $this->forge->createTable('laboratory', true);
        
        // Add FK constraint if staff_profiles exists (for cases where laboratory is created first)
        if ($this->db->tableExists('staff_profiles') && $this->db->tableExists('laboratory')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('laboratory');
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
                            $constraintName = $fk->constraint_name ?? 'laboratory_ibfk_2';
                            try {
                                $this->db->query("ALTER TABLE laboratory DROP FOREIGN KEY " . $this->db->escapeIdentifiers($constraintName));
                            } catch (\Exception $e) {
                                // Ignore if FK doesn't exist
                            }
                        }
                    }
                    // Add new FK to staff_profiles
                    $this->db->query("ALTER TABLE laboratory 
                        ADD CONSTRAINT fk_laboratory_doctor 
                        FOREIGN KEY (doctor_id) 
                        REFERENCES staff_profiles(id) 
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
        $this->forge->dropTable('laboratory', true);
    }
}
