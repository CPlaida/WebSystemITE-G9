<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaboratoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
<<<<<<< HEAD
=======
                'constraint'     => 11,
>>>>>>> 57646d5 (Initial commit)
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'test_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
<<<<<<< HEAD
=======
                'constraint' => 11,
>>>>>>> 57646d5 (Initial commit)
                'unsigned'   => true,
            ],
            'doctor_id' => [
                'type'       => 'INT',
<<<<<<< HEAD
=======
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'appointment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
>>>>>>> 57646d5 (Initial commit)
                'unsigned'   => true,
                'null'       => true,
            ],
            'test_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'test_type' => [
<<<<<<< HEAD
                'type'       => 'VARCHAR',
                'constraint' => 100,
=======
                'type'       => 'ENUM',
                'constraint' => ['blood_test', 'urine_test', 'x_ray', 'ct_scan', 'mri', 'ultrasound', 'ecg', 'other'],
                'default'    => 'blood_test',
            ],
            'test_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sample_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
>>>>>>> 57646d5 (Initial commit)
            ],
            'test_date' => [
                'type' => 'DATE',
            ],
            'test_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
<<<<<<< HEAD
            'test_results' => [
=======
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'],
                'default'    => 'pending',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'urgent', 'stat'],
                'default'    => 'normal',
            ],
            'results' => [
>>>>>>> 57646d5 (Initial commit)
                'type' => 'TEXT',
                'null' => true,
            ],
            'normal_range' => [
<<<<<<< HEAD
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'pending',
=======
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'units' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'technician_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'equipment_used' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
>>>>>>> 57646d5 (Initial commit)
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
<<<<<<< HEAD
=======
            'report_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
>>>>>>> 57646d5 (Initial commit)
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
<<<<<<< HEAD
=======
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
>>>>>>> 57646d5 (Initial commit)
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
<<<<<<< HEAD
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('doctor_id', 'doctors', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('laboratory');
=======

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('doctor_id', 'doctors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('appointment_id', 'appointments', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('technician_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('laboratory_tests');
>>>>>>> 57646d5 (Initial commit)
    }

    public function down()
    {
<<<<<<< HEAD
        $this->forge->dropTable('laboratory');
=======
        $this->forge->dropTable('laboratory_tests');
>>>>>>> 57646d5 (Initial commit)
    }
}
