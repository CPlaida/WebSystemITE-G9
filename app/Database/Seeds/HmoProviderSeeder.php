<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HmoProviderSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $providers = [
            [
                'name' => 'Maxicare Healthcare Corporation',
                'contact_person' => 'Provider Relations Team',
                'hotline' => '(02) 8582-1900',
                'email' => 'providerrelations@maxicare.com.ph',
                'notes' => 'Nationwide; requires LOA for all elective procedures',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Intellicare (Asalus Corporation)',
                'contact_person' => 'Network Management',
                'hotline' => '(02) 789-4000',
                'email' => 'network@intellicare.net.ph',
                'notes' => 'Accepts e-LOA via portal',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Medicard Philippines',
                'contact_person' => 'Provider Support',
                'hotline' => '(02) 8898-7000',
                'email' => 'providersupport@medicardphils.com',
                'notes' => 'Some outpatient services covered without LOA',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kaiser International Healthgroup',
                'contact_person' => 'Provider Relations',
                'hotline' => '(02) 8847-5214',
                'email' => 'providers@kaiserhealthgroup.com',
                'notes' => 'Requires pre-approval for confinement over 24 hours',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PhilCare',
                'contact_person' => 'Provider Care Desk',
                'hotline' => '(02) 8462-1800',
                'email' => 'providerdesk@philcare.com.ph',
                'notes' => 'Supports online LOA requests',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('hmo_providers')->insertBatch($providers);
    }
}
