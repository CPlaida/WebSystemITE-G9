<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServicesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('services')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'unit' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'base_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addKey('code');
        $this->forge->createTable('services', true);

        // Add FK constraints for service_id fields in other tables
        // billing.service_id
        if ($this->db->tableExists('billing') && $this->db->fieldExists('service_id', 'billing')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('billing');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'services' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE billing 
                        ADD CONSTRAINT fk_billing_service 
                        FOREIGN KEY (service_id) 
                        REFERENCES services(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }

        // laboratory.service_id
        if ($this->db->tableExists('laboratory') && $this->db->fieldExists('service_id', 'laboratory')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('laboratory');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'services' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE laboratory 
                        ADD CONSTRAINT fk_laboratory_service 
                        FOREIGN KEY (service_id) 
                        REFERENCES services(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }

        // appointments.service_id
        if ($this->db->tableExists('appointments') && $this->db->fieldExists('service_id', 'appointments')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('appointments');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'services' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE appointments 
                        ADD CONSTRAINT fk_appointments_service 
                        FOREIGN KEY (service_id) 
                        REFERENCES services(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }

        // billing_items.service_id
        if ($this->db->tableExists('billing_items') && $this->db->fieldExists('service_id', 'billing_items')) {
            try {
                $foreignKeys = $this->db->getForeignKeyData('billing_items');
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if (isset($fk->foreign_table_name) && $fk->foreign_table_name === 'services' && 
                        isset($fk->foreign_column_name) && $fk->foreign_column_name === 'id') {
                        $fkExists = true;
                        break;
                    }
                }
                if (!$fkExists) {
                    $this->db->query("ALTER TABLE billing_items 
                        ADD CONSTRAINT fk_billing_items_service 
                        FOREIGN KEY (service_id) 
                        REFERENCES services(id) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE");
                }
            } catch (\Exception $e) {
                // FK constraint may already exist, ignore error
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('services', true);
    }
}
