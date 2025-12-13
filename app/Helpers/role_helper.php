<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if current user has permission for a feature
     * 
     * @param string|array $feature Feature name or array of features
     * @return bool
     */
    function hasPermission($feature): bool
    {
        $role = session()->get('role');
        
        // Role permissions mapping
        $permissions = [
            'admin' => [
                'dashboard', 'patients', 'appointments', 'billing', 'laboratory', 
                'pharmacy', 'inventory', 'rooms', 'reports', 'administration', 'staff_management'
            ],
            'doctor' => [
                'dashboard', 'patients', 'appointments', 'laboratory', 'schedule', 'reports'
            ],
            'nurse' => [
                'dashboard', 'patients', 'appointments', 'laboratory','admissions', 'rooms', 'reports'
            ],
            'pharmacist' => [
                'dashboard', 'pharmacy', 'inventory', 'prescriptions', 'transactions', 'reports'
            ],
            'accountant' => [
                'dashboard', 'billing', 'reports', 'transactions'
            ],
            'accounting' => [
                'dashboard', 'billing', 'reports', 'transactions'
            ],
            'labstaff' => [
                'dashboard', 'laboratory', 'test_results', 'reports'
            ],
            'itstaff' => [
                'dashboard', 'administration', 'reports'
            ],
            'receptionist' => [
                'dashboard', 'patients', 'appointments', 'rooms', 'admissions', 'reports'
            ],
        ];
        
        $userPermissions = $permissions[$role] ?? [];
        
        if (is_array($feature)) {
            return !empty(array_intersect($feature, $userPermissions));
        }
        
        return in_array($feature, $userPermissions, true);
    }
}

if (!function_exists('canAccess')) {
    /**
     * Alias for hasPermission - Laravel-style naming
     * 
     * @param string|array $feature
     * @return bool
     */
    function canAccess($feature): bool
    {
        return hasPermission($feature);
    }
}

if (!function_exists('getRoleName')) {
    /**
     * Get display name for role
     * 
     * @return string
     */
    function getRoleName(): string
    {
        $role = session()->get('role');
        $names = [
            'admin' => 'Administrator',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'pharmacist' => 'Pharmacist',
            'accounting' => 'Accounting',
            'labstaff' => 'Lab Staff',
            'itstaff' => 'IT Staff',
            'receptionist' => 'Receptionist',
        ];
        
        return $names[$role] ?? ucfirst($role);
    }
}

