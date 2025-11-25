<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffDepartmentsTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'unique'     => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'applicable_to' => [
                'type'       => 'ENUM',
                'constraint' => ['doctor', 'nurse', 'all'],
                'default'    => 'all',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->createTable('staff_departments', true);

        $this->seedDefaultDepartments();
    }

    public function down()
    {
        $this->forge->dropTable('staff_departments', true);
    }

    private function seedDefaultDepartments(): void
    {
        $departments = [
            ['name' => 'Emergency Department', 'description' => '24/7 emergency and trauma unit', 'applicable_to' => 'all'],
            ['name' => 'General Medicine', 'description' => 'Internal medicine specialists', 'applicable_to' => 'doctor'],
            ['name' => 'Cardiology', 'description' => 'Heart and vascular care', 'applicable_to' => 'doctor'],
            ['name' => 'Neurology', 'description' => 'Brain and nervous system care', 'applicable_to' => 'doctor'],
            ['name' => 'Orthopedics', 'description' => 'Bone and musculoskeletal care', 'applicable_to' => 'doctor'],
            ['name' => 'Pediatrics', 'description' => 'Child and adolescent health', 'applicable_to' => 'doctor'],
            ['name' => 'Obstetrics & Gynecology', 'description' => 'Women and maternal health', 'applicable_to' => 'doctor'],
            ['name' => 'Radiology & Imaging', 'description' => 'Diagnostic imaging center', 'applicable_to' => 'all'],
            ['name' => 'Laboratory Services', 'description' => 'Laboratory and diagnostics', 'applicable_to' => 'all'],
            ['name' => 'Inpatient Nursing Unit', 'description' => 'General ward nursing unit', 'applicable_to' => 'nurse'],
            ['name' => 'Critical Care Nursing', 'description' => 'ICU nursing team', 'applicable_to' => 'nurse'],
            ['name' => 'Outpatient Nursing', 'description' => 'Clinic and ambulatory nursing', 'applicable_to' => 'nurse'],
        ];

        $builder = $this->db->table('staff_departments');
        $now = date('Y-m-d H:i:s');
        foreach ($departments as $dept) {
            $builder->insert([
                'name' => $dept['name'],
                'slug' => $this->slugify($dept['name']),
                'description' => $dept['description'],
                'applicable_to' => $dept['applicable_to'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim($value, '-') ?: uniqid('dept-');
    }
}
