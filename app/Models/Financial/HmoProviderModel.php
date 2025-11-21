<?php

namespace App\Models\Financial;

use CodeIgniter\Model;

class HmoProviderModel extends Model
{
    protected $table = 'hmo_providers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'contact_person',
        'hotline',
        'email',
        'notes',
        'active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
