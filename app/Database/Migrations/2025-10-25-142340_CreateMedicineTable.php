<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicinesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'medicine_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'unique'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'stock' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // Use a raw definition to support DEFAULT CURRENT_TIMESTAMP
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addKey('id', true);
        // createTable with true for IF NOT EXISTS behavior
        $this->forge->createTable('medicines', true);
    }

    public function down()
    {
        $this->forge->dropTable('medicines', true);
    }
}
