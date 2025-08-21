<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('Home/index');
    }

    public function services()
    {
        return view('Home/services');
    }

    public function doctors()
    {
        return view('Home/doctors');
    }

    public function contact()
    {
        return view('Home/contact');
    }
}
