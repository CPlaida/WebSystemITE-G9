<?php

namespace App\Models;

use CodeIgniter\Model;

class BedModel extends Model
{
    protected $table      = 'beds';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'ward',
        'room',
        'bed',
        'status',
    ];
}
