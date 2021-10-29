<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\AcademicInfo;
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
            'studentCode' => 'required|string|unique:students',
            'gradeYear' => 'required|string',
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

        //if academic info exist add it's id to the student
        $academics = AcademicInfo::all();
        $academic_info_id = 0;
        foreach ($academics as $academic) {
            if ($academic->department === $fields['department']) {
                $academic_info_id = $academic->id;
            }
        }

        //if academic info doesnot exist create academic info
        if ($academic_info_id == 0) {
            $academicInfo = AcademicInfo::create([
                'department' => $fields['department'],
                'school' => $fields['school'],
            ]);
            $academic_info_id = $academicInfo->id;
        }

        //create student
        $student = Student::create([
            'user_id' => $user->id,
            'studentCode' => $fields['studentCode'],
            'gradeYear' => $fields['gradeYear'],
            'academic_info_id' => $academic_info_id
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 200);
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
        $user_id = $student->user_id;

        //return Student::find($id)->user;

        return Student::join('users', 'users.id', '=', 'students.user_id')
            ->where(['user_id' => $user_id])
            ->join('academic_infos', 'academic_infos.id', '=', 'students.academic_info_id')
            ->get();
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
