<?php
// app/Http/Controllers/OrganizationalStructureController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\School;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Support\Facades\DB;

class OrganizationalStructureController extends Controller
{
    public function index()
    {
        $stats = [
            'colleges' => College::count(),
            'schools' => School::count(),
            'departments' => Department::count(),
            'divisions' => Division::count(),
        ];
        
        return view('organization.index', compact('stats'));
    }

    public function hierarchy()
    {
        $colleges = College::with(['schools.departments.divisions'])->get();
        return view('organization.hierarchy', compact('colleges'));
    }

    public function statistics()
    {
        $stats = [
            'total_faculty' => DB::table('users')->where('user_type', 'faculty')->count(),
            'total_students' => DB::table('users')->where('user_type', 'student')->count(),
            'departments_with_heads' => Department::whereNotNull('head_id')->count(),
            'active_departments' => Department::where('is_active', true)->count(),
        ];
        
        return view('organization.statistics', compact('stats'));
    }
}