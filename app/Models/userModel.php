<?php
namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['id','username','email','password','role','role_id','status','created_at','updated_at'];
    protected $beforeInsert = ['assignStringId'];

    protected function assignStringId(array $data)
    {
        if (!isset($data['data']['id']) || empty($data['data']['id'])) {
            $db = \Config\Database::connect();
            $prefix = 'USR';
            // Prefer role name if available
            $roleName = null;
            if (!empty($data['data']['role'])) {
                $roleName = strtolower((string)$data['data']['role']);
            } elseif (!empty($data['data']['role_id'])) {
                $row = $db->table('roles')->select('name')->where('id', $data['data']['role_id'])->get()->getRowArray();
                $roleName = strtolower($row['name'] ?? '');
            }
            $map = [
                'doctor' => 'DOC',
                'nurse' => 'NUR',
                'admin' => 'ADM',
                'pharmacist' => 'PHA',
                'labstaff' => 'LAB',
                'receptionist' => 'REC',
                'accounting' => 'ACC',
                'itstaff' => 'ITS',
            ];
            if ($roleName && isset($map[$roleName])) {
                $prefix = $map[$roleName];
            }
            $today = date('ymd');
            $like = $prefix . '-' . $today . '-%';
            $last = $this->where('id LIKE', $like)->orderBy('id', 'DESC')->first();
            $next = 1;
            if ($last && isset($last['id'])) {
                $parts = explode('-', $last['id']);
                $seq = end($parts);
                $next = (int)$seq + 1;
            }
            $data['data']['id'] = sprintf('%s-%s-%04d', $prefix, $today, $next);
        }
        return $data;
    }
}
