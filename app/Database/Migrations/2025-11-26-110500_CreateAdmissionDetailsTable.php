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
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('attending_doctor_id', 'doctors', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('bed_id', 'beds', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('admission_details');
    }

    public function down()
    {
        $this->forge->dropTable('admission_details');
    }
}
