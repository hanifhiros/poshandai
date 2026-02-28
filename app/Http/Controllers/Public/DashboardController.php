<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('reseller.dashboard'); // Buat Blade view-nya juga
    }
}
