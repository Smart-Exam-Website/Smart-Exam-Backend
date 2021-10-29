<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::all();


        $students->each(function ($student) {
            $student->user;
            $student->department;
            $student->department->school;
        });

        if (!$students) {
            return response()->json(['message' => "No Students Found"], 400);
        }

        return response($students, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $fields = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'gender' => 'required|in:male,female',
            'phone' => 'required|unique:users|digits:11',
            'department' => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'studentCode' => 'required|string|unique:students'
        ]);

        $user = User::create([
            'firstName' => $fields['firstName'],
            'lastName' => $fields['lastName'],
            'email' => $fields['email'],
            'email_verified_at' => date('Y-m-d H:i:s'),
            'password' => Hash::make($fields['password']),
            'gender' => $fields['gender'],
            'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
            'type' => 'student',
            'phone' => $fields['phone']
        ]);


        //if school already exist in database add it's id to the department
        $schools = School::all();
        $school_id = 0;
        foreach ($schools as $school) {
            if ($school->name === $fields['school']) {
                $school_id = $school->id;
            }
        }

        //if school doesnot exist create school
        if ($school_id == 0) {
            $school = School::create([
                'name' => $fields['school']
            ]);
            $school_id = $school->id;
        }


        //if department already exist in database add it's id to the student
        $departments = Department::all();
        $department_id = 0;
        foreach ($departments as $department) {
            if ($department->name === $fields['department']) {
                $department_id = $department->id;
            }
        }

        //if department doesnot exist create department
        if ($department_id == 0) {
            $department = Department::create([
                'name' => $fields['department'],
                'school_id' => $school_id,
            ]);
            $department_id = $department->id;
        }

        //create student
        $student = Student::create([
            'user_id' => $user->id,
            'studentCode' => $fields['studentCode'],
            'department_id' => $department_id
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::where(['id' => $id])->first();

        if (!$student) {
            return [
                'message' => 'No Student Found'
            ];
        }

        $student->user;
        $student->department;
        $student->department->school;

        return response($student, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
