<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DepartmentManagementController extends Controller
{
    //
    public function dashboard()
    {
        return view('department.dashboard');
    }
}
