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
            'bed_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Critical Care, Specialized, General Inpatient',
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
        $this->forge->addKey('bed_type');
        $this->forge->addKey('ward');
        $this->forge->addKey(['ward', 'room', 'bed']);
        $this->forge->createTable('beds', true);
    }

    public function down()
    {
        $this->forge->dropTable('beds', true);
    }
}

