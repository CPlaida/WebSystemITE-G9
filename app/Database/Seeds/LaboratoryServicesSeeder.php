<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LaboratoryServicesSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            // Exactly the services requested
            ['code'=>'LAB-BLOOD','name'=>'Blood Test','category'=>'lab','unit'=>'test','base_price'=>350.00],
            ['code'=>'LAB-URINE','name'=>'Urine Test','category'=>'lab','unit'=>'test','base_price'=>200.00],
            ['code'=>'IMG-XRAY','name'=>'X-Ray','category'=>'lab','unit'=>'test','base_price'=>800.00],
            ['code'=>'IMG-MRI','name'=>'MRI Scan','category'=>'lab','unit'=>'test','base_price'=>5000.00],
            ['code'=>'IMG-CT','name'=>'CT Scan','category'=>'lab','unit'=>'test','base_price'=>4500.00],
            ['code'=>'IMG-US','name'=>'Ultrasound','category'=>'lab','unit'=>'test','base_price'=>1500.00],
            ['code'=>'CARD-ECG','name'=>'ECG','category'=>'lab','unit'=>'test','base_price'=>600.00],
        ];

        $builder = $this->db->table('services');
        foreach ($rows as $r) {
            $row = array_merge($r, [
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            // Upsert-like behavior on code
            $existing = null;
            if (!empty($row['code'])) {
                $existing = $builder->where('code', $row['code'])->get()->getRowArray();
            }
            if ($existing) {
                $builder->where('id', $existing['id'])->update([
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'unit' => $row['unit'],
                    'base_price' => $row['base_price'],
                    'active' => 1,
                    'updated_at' => $now,
                ]);
            } else {
                $builder->insert($row);
            }
        }
    }
}
