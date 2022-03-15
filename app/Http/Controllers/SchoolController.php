<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    // Get all schools
    public function index()
    {
        $schools = School::all();
        return $schools;
    }
}
