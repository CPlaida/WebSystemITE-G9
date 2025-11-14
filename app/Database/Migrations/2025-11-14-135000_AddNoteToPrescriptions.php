<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoteToPrescriptions extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('note', 'prescriptions')) {
            $this->forge->addColumn('prescriptions', [
                'note' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'total_amount',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('note', 'prescriptions')) {
            $this->forge->dropColumn('prescriptions', 'note');
        }
    }
}


