<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNursesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'license_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'unique'     => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave'],
                'default'    => 'active',
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('nurses', true);
    }

    public function down()
    {
        $this->forge->dropTable('nurses', true);
    }
}