<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWardRoomBedToPatients extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        $this->forge->dropColumn('patients', ['ward', 'room', 'bed']);
    }
}
