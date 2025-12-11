<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Dates for inventory display (expiry dates must be > 3 months from today)
        $future1 = date('Y-m-d', strtotime('+8 months'));  // MED-001
        $future2 = date('Y-m-d', strtotime('+6 months')); // MED-002 (out of stock but valid expiry)
        $future3 = date('Y-m-d', strtotime('+12 months')); // MED-003
        $future4 = date('Y-m-d', strtotime('+10 months')); // MED-004
        $future5 = date('Y-m-d', strtotime('+9 months'));  // MED-005

        // Dates for expired/expiring items (for Stock Out section testing)
        $expired = date('Y-m-d', strtotime('-2 months'));
        $recentlyExpired = date('Y-m-d', strtotime('-10 days'));
        $today = date('Y-m-d');
        $nearFuture = date('Y-m-d', strtotime('+20 days')); // Within 3 months

        $medicines = [
            [
                'id' => 'MED-001',
                'barcode' => 'RX-1001',
                'name' => 'Amoxicillin 500mg Capsule',
                'brand' => 'PharmaCare',
                'category' => 'Antibiotic',
                'stock' => 150,
                'unit_price' => 4.50,
                'retail_price' => 7.99,
                'manufactured_date' => date('Y-m-d', strtotime('-6 months')),
                'expiry_date' => $future1,
                'description' => 'Broad-spectrum antibiotic for bacterial infections.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-002',
                'barcode' => 'RX-1002',
                'name' => 'Cetirizine 10mg Tablet',
                'brand' => 'AllerFree',
                'category' => 'Antihistamine',
                'stock' => 0, // Stock out scenario - will show in "Out of Stock" section
                'unit_price' => 2.10,
                'retail_price' => 3.75,
                'manufactured_date' => date('Y-m-d', strtotime('-4 months')),
                'expiry_date' => $future2, // Valid expiry but stock = 0
                'description' => 'Antihistamine for allergy relief. Currently out of stock.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-003',
                'barcode' => 'RX-1003',
                'name' => 'Ascorbic Acid 500mg Tablet',
                'brand' => 'VitaBoost',
                'category' => 'Vitamin',
                'stock' => 320,
                'unit_price' => 1.25,
                'retail_price' => 2.20,
                'manufactured_date' => date('Y-m-d', strtotime('-3 months')),
                'expiry_date' => $future3,
                'description' => 'Immune support supplement with vitamin C.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-004',
                'barcode' => 'RX-1004',
                'name' => 'Ibuprofen 400mg Tablet',
                'brand' => 'PainAway',
                'category' => 'Analgesic',
                'stock' => 85,
                'unit_price' => 1.85,
                'retail_price' => 3.00,
                'manufactured_date' => date('Y-m-d', strtotime('-9 months')),
                'expiry_date' => $future4,
                'description' => 'Pain reliever and anti-inflammatory medication.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-005',
                'barcode' => 'RX-1005',
                'name' => 'Oral Rehydration Salts',
                'brand' => 'HydraPlus',
                'category' => 'Electrolyte',
                'stock' => 40,
                'unit_price' => 0.95,
                'retail_price' => 1.80,
                'manufactured_date' => date('Y-m-d', strtotime('-2 months')),
                'expiry_date' => $future5,
                'description' => 'Oral rehydration salts for dehydration treatment.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-006',
                'barcode' => 'RX-1006',
                'name' => 'Paracetamol 500mg Tablet',
                'brand' => 'Biogesic',
                'category' => 'Analgesic',
                'stock' => 3, // Low stock
                'unit_price' => 1.50,
                'retail_price' => 2.50,
                'manufactured_date' => date('Y-m-d', strtotime('-1 month')),
                'expiry_date' => date('Y-m-d', strtotime('+7 months')),
                'description' => 'Pain reliever and fever reducer.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-007',
                'barcode' => 'RX-1007',
                'name' => 'Loratadine 10mg Tablet',
                'brand' => 'Loratin',
                'category' => 'Antihistamine',
                'stock' => 200,
                'unit_price' => 2.75,
                'retail_price' => 4.50,
                'manufactured_date' => date('Y-m-d', strtotime('-2 months')),
                'expiry_date' => date('Y-m-d', strtotime('+11 months')),
                'description' => 'Non-drowsy antihistamine for allergy relief.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-008',
                'barcode' => 'RX-1008',
                'name' => 'Metformin 500mg Tablet',
                'brand' => 'RiteMed',
                'category' => 'Antibiotic',
                'stock' => 5, // Low stock
                'unit_price' => 3.25,
                'retail_price' => 5.00,
                'manufactured_date' => date('Y-m-d', strtotime('-5 months')),
                'expiry_date' => date('Y-m-d', strtotime('+6 months')),
                'description' => 'Antidiabetic medication for type 2 diabetes.',
                'created_at' => $now,
            ],
        ];

        $ids = array_column($medicines, 'id');
        if (! empty($ids)) {
            $this->db->table('medicines')->whereIn('id', $ids)->delete();
        }

        $this->db->table('medicines')->insertBatch($medicines);
    }
}
