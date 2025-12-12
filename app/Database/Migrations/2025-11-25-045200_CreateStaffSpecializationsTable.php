<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffSpecializationsTable extends Migration
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
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'unique'     => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'applicable_to' => [
                'type'       => 'ENUM',
                'constraint' => ['doctor', 'nurse', 'all'],
                'default'    => 'doctor',
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
        $this->forge->addForeignKey('department_id', 'staff_departments', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('staff_specializations', true);

        $this->seedDefaultSpecializations();

        // Note: doctors table has been consolidated into staff_profiles
        // FK constraint for specialization_id is handled in staff_profiles table creation
    }

    public function down()
    {
        $this->forge->dropTable('staff_specializations', true);
    }

    private function seedDefaultSpecializations(): void
    {
        $specializations = [
            ['name' => 'Emergency Medicine', 'department_slug' => 'emergency-department', 'description' => 'Emergency physicians on call 24/7'],
            ['name' => 'Cardiac Surgery', 'department_slug' => 'cardiology', 'description' => 'Surgical heart team'],
            ['name' => 'Interventional Cardiology', 'department_slug' => 'cardiology', 'description' => 'Cath lab and interventional procedures'],
            ['name' => 'Pediatric Neurology', 'department_slug' => 'pediatrics', 'description' => 'Neurology care for children'],
            ['name' => 'Neonatal Nursing', 'department_slug' => 'inpatient-nursing-unit', 'description' => 'Specialized neonatal nurses', 'applicable_to' => 'nurse'],
            ['name' => 'Critical Care Nursing', 'department_slug' => 'critical-care-nursing', 'description' => 'ICU specialized nurses', 'applicable_to' => 'nurse'],
        ];

        $deptMap = $this->getDepartmentSlugMap();
        $builder = $this->db->table('staff_specializations');
        $now = date('Y-m-d H:i:s');

        foreach ($specializations as $spec) {
            $deptId = $deptMap[$spec['department_slug']] ?? null;
            $builder->insert([
                'department_id' => $deptId,
                'name' => $spec['name'],
                'slug' => $this->slugify($spec['name']),
                'description' => $spec['description'] ?? null,
                'applicable_to' => $spec['applicable_to'] ?? 'doctor',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function getDepartmentSlugMap(): array
    {
        $rows = $this->db->table('staff_departments')->select('id, slug')->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['slug']] = $row['id'];
        }
        return $map;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim($value, '-') ?: uniqid('spec-');
    }
}
