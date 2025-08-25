<?php

namespace App\Controllers;

class Doctor extends BaseController
{
    public function index()
    {
        return view('doctor/dashboard');
    }
}
