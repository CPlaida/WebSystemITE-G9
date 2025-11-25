<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffProfileModel extends Model
{
    protected $table            = 'staff_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'role_id',
        'department_id',
        'specialization_id',
        'address',
        'hire_date',
        'status',
        'ready_for_account',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name'  => 'required|min_length[2]|max_length[100]',
        'status'     => 'permit_empty|in_list[active,inactive,on_leave]',
        'role_id'    => 'permit_empty|is_natural_no_zero',
        'department_id' => 'permit_empty|is_natural_no_zero',
        'specialization_id' => 'permit_empty|is_natural_no_zero',
        'ready_for_account' => 'permit_empty|in_list[0,1]',
    ];

    protected $skipValidation = false;
}
