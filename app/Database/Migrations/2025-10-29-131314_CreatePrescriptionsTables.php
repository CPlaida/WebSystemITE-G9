<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrescriptionsTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'patient_id' => ['type'=>'VARCHAR','constraint'=>20],
            'date' => ['type'=>'DATE'],
            'payment_method' => ['type'=>'ENUM','constraint'=>['cash','insurance'],'default'=>'cash'],
            'subtotal' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'tax' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'total_amount' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('patient_id');
        $this->forge->createTable('prescriptions', true);

        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'prescription_id' => ['type'=>'INT','unsigned'=>true],
            'medication_id' => ['type'=>'INT','unsigned'=>true],
            'quantity' => ['type'=>'INT','default'=>1],
            'price' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'total' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('prescription_id');
        $this->forge->addKey('medication_id');
        $this->forge->createTable('prescription_items', true);

        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'transaction_number' => ['type'=>'VARCHAR','constraint'=>50],
            'patient_id' => ['type'=>'VARCHAR','constraint'=>20],
            'date' => ['type'=>'DATE'],
            'total_items' => ['type'=>'INT','default'=>0],
            'total_amount' => ['type'=>'DECIMAL','constraint'=>'10,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transaction_number');
        $this->forge->addKey('patient_id');
        $this->forge->createTable('pharmacy_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('pharmacy_transactions', true);
        $this->forge->dropTable('prescription_items', true);
        $this->forge->dropTable('prescriptions', true);
    }
}