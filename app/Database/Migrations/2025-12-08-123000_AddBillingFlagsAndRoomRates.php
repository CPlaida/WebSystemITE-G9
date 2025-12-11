<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillingFlagsAndRoomRates extends Migration
{
    public function up()
    {
        // Add room_rate to beds
        if (!$this->db->fieldExists('room_rate', 'beds')) {
            $this->forge->addColumn('beds', [
                'room_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                    'after' => 'status',
                ],
            ]);
        }

        // Add billed columns to source tables
        $this->addBilledColumn('appointments');
        $this->addBilledColumn('pharmacy_transactions');
        $this->addBilledColumn('prescriptions');
        $this->addBilledColumn('admission_details');
    }

    public function down()
    {
        if ($this->db->fieldExists('room_rate', 'beds')) {
            $this->forge->dropColumn('beds', 'room_rate');
        }

        $this->dropBilledColumn('appointments');
        $this->dropBilledColumn('pharmacy_transactions');
        $this->dropBilledColumn('prescriptions');
        $this->dropBilledColumn('admission_details');
    }

    protected function addBilledColumn(string $table): void
    {
        if ($this->db->fieldExists('billed', $table)) {
            return;
        }

        $columnDefinition = [
            'billed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
        ];

        if ($this->db->fieldExists('status', $table)) {
            $columnDefinition['billed']['after'] = 'status';
        }

        $this->forge->addColumn($table, $columnDefinition);

        $this->db->query("CREATE INDEX IF NOT EXISTS {$table}_billed_idx ON {$table} (billed)");
    }

    protected function dropBilledColumn(string $table): void
    {
        if ($this->db->fieldExists('billed', $table)) {
            $this->forge->dropColumn($table, 'billed');
        }
    }
}
