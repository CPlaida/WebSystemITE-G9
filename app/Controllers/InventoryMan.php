<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class InventoryMan extends BaseController
{
    public function PrescriptionDispencing()
    {
        $data = [
            'title' => 'Prescription Dispensing'
        ];
        
        return view('admin/InventoryMan/PrescriptionDispencing', $data);
    }
}
