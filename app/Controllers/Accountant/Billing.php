<?php

namespace App\Controllers\Accountant;

use App\Controllers\BaseController;

class Billing extends BaseController
{
    public function process()
    {
        $data = [
            'title' => 'Billing Process',
            // Add any other data you want to pass to the view
        ];

        return view('Roles/Accountant/Billing & payment/bill_process', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Bill Management',
            // Add any other data you want to pass to the view
        ];

        return view('Roles/Accountant/Billing & payment/billingmanagement', $data);
    }
}
