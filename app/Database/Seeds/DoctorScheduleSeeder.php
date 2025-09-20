<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run()
    {
        // Get doctor IDs from users table
        $doctors = $this->db->table('users')->where('role', 'doctor')->get()->getResultArray();
        
        if (empty($doctors)) {
            echo "No doctors found. Please run DoctorUserSeeder first.\n";
            return;
        }

        $departments = ['Emergency', 'General', 'Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics'];
        $shiftTypes = ['morning', 'afternoon', 'night'];
        $shiftTimes = [
            'morning' => ['06:00:00', '14:00:00'],
            'afternoon' => ['10:00:00', '16:00:00'],
            'night' => ['16:00:00', '22:00:00']
        ];

        $schedules = [];
        
        // Create schedules for the next 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            
            // Create 2-4 random schedules per day
            $schedulesPerDay = rand(2, 4);
            
            for ($j = 0; $j < $schedulesPerDay; $j++) {
                $doctor = $doctors[array_rand($doctors)];
                $shiftType = $shiftTypes[array_rand($shiftTypes)];
                $department = $departments[array_rand($departments)];
                $times = $shiftTimes[$shiftType];
                
                $schedules[] = [
                    'doctor_id' => $doctor['id'],
                    'doctor_name' => ucfirst(str_replace('dr.', 'Dr. ', $doctor['username'])),
                    'department' => $department,
                    'shift_type' => $shiftType,
                    'shift_date' => $date,
                    'start_time' => $times[0],
                    'end_time' => $times[1],
                    'status' => 'scheduled',
                    'notes' => 'Regular scheduled shift',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        // Insert schedules in batches
        $batchSize = 50;
        $batches = array_chunk($schedules, $batchSize);
        
        foreach ($batches as $batch) {
            $this->db->table('doctor_schedules')->insertBatch($batch);
        }

        echo "Doctor schedules seeded successfully! Created " . count($schedules) . " schedules.\n";
    }
}