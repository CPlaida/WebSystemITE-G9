<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FinancialDataSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        
        // Sample bills
        $bills = [
            [
                'patient_id' => 1,
                'amount' => 150.00,
                'status' => 'pending',
                'description' => 'General consultation',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'patient_id' => 2,
                'amount' => 300.00,
                'status' => 'paid',
                'description' => 'Blood test and X-ray',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'patient_id' => 3,
                'amount' => 75.00,
                'status' => 'pending',
                'description' => 'Prescription medication',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        $this->db->table('bills')->insertBatch($bills);
        
        // Sample payments
        $payments = [
            [
                'bill_id' => 2,
                'amount' => 300.00,
                'payment_date' => date('Y-m-d H:i:s'),
                'method' => 'card',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'bill_id' => null,
                'amount' => 100.00,
                'payment_date' => date('Y-m-d H:i:s'),
                'method' => 'cash',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        $this->db->table('payments')->insertBatch($payments);
        
        // Sample insurance claims
        $insurance = [
            [
                'patient_id' => 1,
                'claim_number' => 'INS-2025-001',
                'amount' => 500.00,
                'status' => 'pending',
                'provider' => 'Health Insurance Corp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'patient_id' => 2,
                'claim_number' => 'INS-2025-002',
                'amount' => 250.00,
                'status' => 'approved',
                'provider' => 'Medical Care Insurance',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        $this->db->table('insurance')->insertBatch($insurance);
        
        echo "✅ Financial sample data created successfully\n";
    }
}
