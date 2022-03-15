<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{   
    // Get all departments
    public function index()
    {
        $departments = Department::all();

        return $departments;
    }
}
