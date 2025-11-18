<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBedIdToPatients extends Migration
{
    public function up()
    {
        // Check if bed_id column already exists
        $fields = $this->db->getFieldData('patients');
        $hasBedId = false;
        foreach ($fields as $field) {
            if ($field->name === 'bed_id') {
                $hasBedId = true;
                break;
            }
        }

        if (!$hasBedId) {
            // Add bed_id column
            $this->forge->addColumn('patients', [
                'bed_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'type',
                ],
            ]);

            // Add foreign key constraint
            $this->forge->addForeignKey('bed_id', 'beds', 'id', 'SET NULL', 'CASCADE');
        }

        // Migrate existing ward/room/bed data to bed_id if they exist
        $hasWard = false;
        foreach ($fields as $field) {
            if ($field->name === 'ward') {
                $hasWard = true;
                break;
            }
        }

        if ($hasWard) {
            // Migrate existing data
            $patients = $this->db->table('patients')
                ->where('ward IS NOT NULL')
                ->where('ward !=', '')
                ->where('room IS NOT NULL')
                ->where('room !=', '')
                ->where('bed IS NOT NULL')
                ->where('bed !=', '')
                ->where('type', 'inpatient')
                ->get()
                ->getResultArray();

            foreach ($patients as $patient) {
                $bed = $this->db->table('beds')
                    ->where('ward', $patient['ward'])
                    ->where('room', $patient['room'])
                    ->where('bed', $patient['bed'])
                    ->get()
                    ->getRowArray();

                if ($bed) {
                    $this->db->table('patients')
                        ->where('id', $patient['id'])
                        ->update(['bed_id' => $bed['id']]);
                }
            }

            // Remove old columns after migration
            $this->forge->dropColumn('patients', ['ward', 'room', 'bed']);
        }
    }

    public function down()
    {
        // Add back ward/room/bed columns
        $fields = [
            'ward' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'bed' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('patients', $fields);

        // Migrate data back
        $patients = $this->db->table('patients')
            ->where('bed_id IS NOT NULL')
            ->get()
            ->getResultArray();

        foreach ($patients as $patient) {
            $bed = $this->db->table('beds')
                ->where('id', $patient['bed_id'])
                ->get()
                ->getRowArray();

            if ($bed) {
                $this->db->table('patients')
                    ->where('id', $patient['id'])
                    ->update([
                        'ward' => $bed['ward'],
                        'room' => $bed['room'],
                        'bed'  => $bed['bed'],
                    ]);
            }
        }

        // Drop foreign key and bed_id
        try {
            $this->forge->dropForeignKey('patients', 'patients_bed_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist
        }
        $this->forge->dropColumn('patients', ['bed_id']);
    }
}

