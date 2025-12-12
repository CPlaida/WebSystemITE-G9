<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Core reference data
        $this->call('RolesSeeder');
        $this->call('BedsSeeder');
        $this->call('LaboratoryServicesSeeder');
        $this->call('PhilHealthCaseRatesSeeder');
        $this->call('HmoProviderSeeder');
        $this->call('MedicineSeeder');
        
        // Staff and users (must run before patients/appointments that reference staff)
        $this->call('UserSeeder'); // Creates admin users
        $this->call('StaffProfilesSeeder'); // Creates non-doctor staff with user accounts
        $this->call('DoctorSeeder'); // Creates doctors with user accounts and staff_profiles
        
        // Patient data (depends on staff being created)
        $this->call('PatientSeeder');
    }
}
