<?php

namespace App\Controllers;

class Nurse extends BaseController
{
    public function index()
    {
        return view('nurse/dashboard');
    }
    
    public function reports()
    {
        return view('nurse/reports');
    }
}



