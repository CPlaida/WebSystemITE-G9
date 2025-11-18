<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmergencyContactFieldsToPatients extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('patients');
        $fieldNames = array_column($fields, 'name');
        
        $columnsToAdd = [];

        // Add emergency_contact_person if it doesn't exist
        if (!in_array('emergency_contact_person', $fieldNames, true)) {
            $columnsToAdd['emergency_contact_person'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'emergency_contact',
            ];
        }

        // Add emergency_contact_relationship if it doesn't exist
        if (!in_array('emergency_contact_relationship', $fieldNames, true)) {
            $columnsToAdd['emergency_contact_relationship'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'emergency_contact_person',
            ];
        }

        // Add emergency_contact_phone if it doesn't exist
        if (!in_array('emergency_contact_phone', $fieldNames, true)) {
            $columnsToAdd['emergency_contact_phone'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'emergency_contact_relationship',
            ];
        }

        if (!empty($columnsToAdd)) {
            $this->forge->addColumn('patients', $columnsToAdd);
        }

        // Migrate existing JSON data to separate columns
        $patients = $db->table('patients')
            ->where('emergency_contact IS NOT NULL')
            ->where('emergency_contact !=', '')
            ->get()
            ->getResultArray();

        foreach ($patients as $patient) {
            $contactStr = trim($patient['emergency_contact']);
            $person = null;
            $relationship = null;
            $phone = null;

            // Try to parse as JSON
            if (strpos($contactStr, '{') === 0) {
                $decoded = json_decode($contactStr, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $person = $decoded['person'] ?? null;
                    $relationship = $decoded['relationship'] ?? null;
                    $phone = $decoded['phone'] ?? null;
                } else {
                    // Malformed JSON - try to extract
                    if (preg_match('/"person"\s*:\s*"([^"]*)/', $contactStr, $matches)) {
                        $person = $matches[1] ?? null;
                    }
                    if (preg_match('/"phone"\s*:\s*"([^"]*)/', $contactStr, $matches)) {
                        $phone = $matches[1] ?? null;
                    }
                    if (preg_match('/"relationship"\s*:\s*"([^"]*)/', $contactStr, $matches)) {
                        $relationship = $matches[1] ?? null;
                    }
                }
            } else {
                // Not JSON - treat as phone number
                if (preg_match('/^\+?\d/', $contactStr)) {
                    $phone = $contactStr;
                }
            }

            // Update patient with extracted data
            $updateData = [];
            if ($person !== null) $updateData['emergency_contact_person'] = $person;
            if ($relationship !== null) $updateData['emergency_contact_relationship'] = $relationship;
            if ($phone !== null) $updateData['emergency_contact_phone'] = $phone;

            if (!empty($updateData)) {
                $db->table('patients')
                    ->where('id', $patient['id'])
                    ->update($updateData);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('patients');
        $fieldNames = array_column($fields, 'name');
        
        if (in_array('emergency_contact_phone', $fieldNames, true)) {
            $this->forge->dropColumn('patients', 'emergency_contact_phone');
        }
        if (in_array('emergency_contact_relationship', $fieldNames, true)) {
            $this->forge->dropColumn('patients', 'emergency_contact_relationship');
        }
        if (in_array('emergency_contact_person', $fieldNames, true)) {
            $this->forge->dropColumn('patients', 'emergency_contact_person');
        }
    }
}

