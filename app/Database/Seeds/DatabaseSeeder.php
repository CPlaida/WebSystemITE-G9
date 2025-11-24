<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('RolesSeeder');
        $this->call('UserSeeder');
        $this->call('BedsSeeder');
        $this->call('LaboratoryServicesSeeder');
        $this->call('PhilHealthCaseRatesSeeder');
        $this->call('HmoProviderSeeder');
    }
}
