<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInsuranceToPatients extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = [];
        $current = $db->getFieldNames('patients');

        if (!in_array('insurance_provider', $current, true)) {
            $fields['insurance_provider'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'emergency_contact',
            ];
        }
        if (!in_array('insurance_number', $current, true)) {
            $fields['insurance_number'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'insurance_provider',
            ];
        }
        if ($fields) {
            $this->forge->addColumn('patients', $fields);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $current = $db->getFieldNames('patients');
        if (in_array('insurance_number', $current, true)) {
            $this->forge->dropColumn('patients', 'insurance_number');
        }
        if (in_array('insurance_provider', $current, true)) {
            $this->forge->dropColumn('patients', 'insurance_provider');
        }
    }
}