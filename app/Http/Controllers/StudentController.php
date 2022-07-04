<?php

namespace App\Http\Controllers;

use App\Http\Requests\Students\UpdateProfileRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StudentController extends Controller
{

    // Get all students
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
    // Signup
    public function store(Request $request)
    {

        $rules = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'gender' => 'required|in:male,female',
            'image' => 'required',
            'phone' => 'required|unique:users|digits:11',
            'departments.*.department_id' => ['required', 'numeric', 'exists:departments,id'],
            'studentCode' => 'required|string|unique:students'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $groupedErrors = implode("<br>", $validator->messages()->all());
            return response()->json(["message" => "The data is invalid. \r\n" . $groupedErrors], 400);
        }


        $deps = $request->departments;
        $dep_id = $deps[0]['department_id'];

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'image' => $request->image,
            'type' => 'student',
            'phone' => $request->phone
        ]);

        //create student
        $student = Student::create([
            'id' => $user->id,
            'studentCode' => $request->studentCode,
            'department_id' => $dep_id
        ]);

        $student->id = $user->id;

        $student->user;
        $student->department;
        $student->department->school;

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'student' => $student,
            'token' => $token
        ];

        $verificationCode = Str::random(6);
        Mail::send('email.verifyemail', ['url' => 'http://api.smart-exam.ml', 'verificationCode' => $verificationCode], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Verify your email!');
        });

        DB::table('verification_codes')->insert([
            'email' => $request->email,
            'code' => $verificationCode,
            'created_at' => Carbon::now()
        ]);


        return response($response, 201);
    }

    // Show student details
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



    // Show profile

    public function showProfile()
    {
        $user = auth()->user();

        if (!$user) {
            return response([
                'message' => 'No User Was Found'
            ], 400);
        }

        $student = Student::where(['id' => $user->id])->first();
        $student->user;
        $student->department;
        $student->department->school;

        return response(['student' => $student], 200);
    }

    public function edit($id)
    {
        //
    }



    // Edit Profile

    public function editProfile(UpdateProfileRequest $request)
    {

        $user = auth()->user();
        $student = Student::where(['id' => $user->id])->first();
        $departments = Department::all();
        $schools = School::all();

        if (!$user) {
            return response([
                'message' => 'No User Was Found'
            ], 400);
        }

        $school_id = 0;


        foreach ($schools as $school) {
            if ($school->name === $request['school']) {
                $school_id = $school->id;
            }
        }

        //if school doesnot exist create school
        if ($school_id == 0) {
            $school = School::create([
                'name' => $request['school'] ? $request['school'] : $student->department->school->name
            ]);
            $school_id = $school->id;
        }


        $department_id = 0;

        //if department already exist in database add it's id to the student

        foreach ($departments as $dep) {
            if ($dep->name == $request->department && $dep->school->name == $request->school) {
                $department_id = $dep->id;
            }
        }

        //if department doesnot exist create department
        if ($department_id == 0) {
            $department = Department::create([
                'name' => $request['department'] ? $request['department'] : $student->department->name,
                'school_id' => $school_id ? $school_id : $student->department->school_id,
            ]);
            $department_id = $department->id;
        }



        $user->update([
            'firstName' => $request->firstName ? $request->firstName : $user->firstName,
            'lastName' => $request->lastName ? $request->lastName : $user->lastName,
            'email' => $request->email ? $request->email : $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'gender' => $request->gender ? $request->gender : $user->gender,
            'phone' => $request->phone ? $request->phone : $user->phone
        ]);

        $student->update([
            'department_id' => $department_id ? $department_id : $student->department_id,
            'studentCode' => $request->studentCode ? $request->studentCode : $student->studentCode
        ]);

        return response([
            'message' => 'Student Profile Updated Successfully'
        ], 200);
    }
}
