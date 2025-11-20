<?php namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'name', 'brand', 'category', 'stock', 'price', 'expiry_date'
    ];
    
    // Dynamically check if image column exists and add it to allowedFields
    protected function initialize()
    {
        parent::initialize();
        try {
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames($this->table);
            if (in_array('image', $fields)) {
                $this->allowedFields[] = 'image';
            }
        } catch (\Exception $e) {
            // If table doesn't exist or error, just continue without image field
        }
    }

    protected $beforeInsert = ['generateId'];

    protected function generateId(array $data)
    {
        if (!empty($data['data']['id'])) return $data;
        $db = \Config\Database::connect();
        $row = $db->table($this->table)
            ->select('id')
            ->like('id', 'MED-', 'after')
            ->orderBy('id', 'DESC')
            ->get(1)->getRowArray();
        $next = 1;
        if ($row && isset($row['id'])) {
            $num = (int)substr($row['id'], 4);
            if ($num > 0) $next = $num + 1;
        }
        $data['data']['id'] = 'MED-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        return $data;
    }
}
