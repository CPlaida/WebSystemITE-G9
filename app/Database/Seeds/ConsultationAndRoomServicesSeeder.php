<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ConsultationAndRoomServicesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Check if services table exists
        if (!$db->tableExists('services')) {
            $this->command->write('Services table does not exist. Please run migrations first.');
            return;
        }

        $services = [
            // Consultation Services
            [
                'code' => 'CONS-CONSULT',
                'name' => 'Consultation Fee',
                'category' => 'consultation',
                'unit' => 'per visit',
                'base_price' => 500.00,
                'active' => 1,
            ],
            [
                'code' => 'CONS-FOLLOWUP',
                'name' => 'Follow-up Consultation',
                'category' => 'consultation',
                'unit' => 'per visit',
                'base_price' => 350.00,
                'active' => 1,
            ],
            [
                'code' => 'CONS-EMERGENCY',
                'name' => 'Emergency Consultation',
                'category' => 'consultation',
                'unit' => 'per visit',
                'base_price' => 800.00,
                'active' => 1,
            ],
            [
                'code' => 'CONS-ROUTINE',
                'name' => 'Routine Checkup',
                'category' => 'consultation',
                'unit' => 'per visit',
                'base_price' => 400.00,
                'active' => 1,
            ],
            
            // Room Rate Services
            [
                'code' => 'ROOM-ICU',
                'name' => 'ICU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-NICU',
                'name' => 'NICU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-PICU',
                'name' => 'PICU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-CCU',
                'name' => 'CCU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-MICU',
                'name' => 'MICU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-SICU',
                'name' => 'SICU Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-PRIVATE',
                'name' => 'Private Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1100.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-SEMIPRIVATE',
                'name' => 'Semi-Private Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 800.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-ISOLATION',
                'name' => 'Isolation Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 1000.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-SDU',
                'name' => 'Step-Down Unit Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 800.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-ED',
                'name' => 'Emergency Department Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-LD',
                'name' => 'Labor & Delivery Room Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-WARD',
                'name' => 'General Ward Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 500.00,
                'active' => 1,
            ],
            [
                'code' => 'ROOM-PEDIA',
                'name' => 'Pediatric Ward Rate',
                'category' => 'room',
                'unit' => 'per day',
                'base_price' => 500.00,
                'active' => 1,
            ],
        ];

        $now = date('Y-m-d H:i:s');
        $inserted = 0;
        $updated = 0;

        foreach ($services as $service) {
            // Check if service with same code exists
            $existing = $db->table('services')
                ->where('code', $service['code'])
                ->get()
                ->getRowArray();

            if ($existing) {
                // Update existing service
                $service['updated_at'] = $now;
                $db->table('services')
                    ->where('code', $service['code'])
                    ->update($service);
                $updated++;
            } else {
                // Insert new service
                $service['created_at'] = $now;
                $service['updated_at'] = $now;
                $db->table('services')->insert($service);
                $inserted++;
            }
        }

        $this->command->write("Consultation and Room Services Seeder completed:");
        $this->command->write("  - Inserted: {$inserted} services");
        $this->command->write("  - Updated: {$updated} services");
    }
}


