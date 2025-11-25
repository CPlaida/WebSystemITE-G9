<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffDepartmentModel extends Model
{
    protected $table            = 'staff_departments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name',
        'slug',
        'description',
        'applicable_to',
        'is_active',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[120]',
        'slug' => 'permit_empty|max_length[150]',
        'applicable_to' => 'in_list[doctor,nurse,all]',
        'is_active' => 'in_list[0,1]',
    ];

    protected $beforeInsert = ['ensureSlug'];
    protected $beforeUpdate = ['ensureSlug'];

    protected function ensureSlug(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }
        if (empty($data['data']['slug']) && !empty($data['data']['name'])) {
            $data['data']['slug'] = $this->slugify($data['data']['name']);
        }
        return $data;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim($value, '-') ?: uniqid('dept-');
    }
}
