<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'name',
        'category',
        'unit',
        'base_price',
        'active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByCodeOrName(string $term): ?array
    {
        $term = trim($term);
        if ($term === '') return null;
        return $this->builder()
            ->groupStart()
                ->where('code', $term)
                ->orWhere('name', $term)
            ->groupEnd()
            ->where('active', 1)
            ->orderBy('id', 'ASC')
            ->get()->getRowArray() ?: null;
    }
}
