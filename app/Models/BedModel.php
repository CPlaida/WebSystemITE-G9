<?php

namespace App\Models;

use CodeIgniter\Model;

class BedModel extends Model
{
    protected $table      = 'beds';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'bed_type',
        'ward',
        'room',
        'bed',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
