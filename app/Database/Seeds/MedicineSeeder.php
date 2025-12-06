<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $today = date('Y-m-d');
        $expired = date('Y-m-d', strtotime('-2 months'));
        $recentlyExpired = date('Y-m-d', strtotime('-10 days'));
        $future = date('Y-m-d', strtotime('+8 months'));
        $nearFuture = date('Y-m-d', strtotime('+20 days'));

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
                'expiry_date' => $future,
                'description' => 'Broad-spectrum antibiotic for bacterial infections.',
                'created_at' => $now,
            ],
            [
                'id' => 'MED-002',
                'barcode' => 'RX-1002',
                'name' => 'Cetirizine 10mg Tablet',
                'brand' => 'AllerFree',
                'category' => 'Antihistamine',
                'stock' => 0, // Stock out scenario
                'unit_price' => 2.10,
                'retail_price' => 3.75,
                'manufactured_date' => date('Y-m-d', strtotime('-4 months')),
                'expiry_date' => $nearFuture,
                'description' => 'Antihistamine for allergy relief. Stock intentionally depleted.',
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
                'expiry_date' => $today,
                'description' => 'Immune support supplement (expires today).',
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
                'expiry_date' => $recentlyExpired, // Already expired
                'description' => 'Pain reliever and anti-inflammatory (expired sample).',
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
                'manufactured_date' => $expired,
                'expiry_date' => $expired, // Long expired, should be excluded by inventory view
                'description' => 'Oral rehydration salts (do not restock to inventory due to expiry).',
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
