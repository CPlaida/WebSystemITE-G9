<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBedsTable extends Migration
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
            'ward' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'bed' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Available',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('beds', true);

        // Seed initial bed layout for Pedia, Male, and Female wards
        $beds = [
            // Pedia Ward
            ['ward' => 'Pedia Ward', 'room' => 'P-101', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-101', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-102', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-102', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-103', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-103', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Pedia Ward', 'room' => 'P-104', 'bed' => 'Bed 1', 'status' => 'Available'],

            // Male Ward
            ['ward' => 'Male Ward', 'room' => 'M-201', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-201', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-202', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-202', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-203', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-203', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Male Ward', 'room' => 'M-204', 'bed' => 'Bed 1', 'status' => 'Available'],

            // Female Ward
            ['ward' => 'Female Ward', 'room' => 'F-301', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-301', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-302', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-302', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-303', 'bed' => 'Bed 1', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-303', 'bed' => 'Bed 2', 'status' => 'Available'],
            ['ward' => 'Female Ward', 'room' => 'F-304', 'bed' => 'Bed 1', 'status' => 'Available'],
        ];

        $this->db->table('beds')->insertBatch($beds);
    }

    public function down()
    {
        $this->forge->dropTable('beds', true);
    }
}
