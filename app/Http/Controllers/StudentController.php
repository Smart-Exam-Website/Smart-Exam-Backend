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

    /**
     * @OA\Get(
     *      path="/students",
     *      operationId="getStudentsList",
     *      tags={"Students"},
     *      summary="Get list of Students",
     *      description="Returns list of Students",
     *      security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="students", type="array", @OA\Items(ref="#/components/schemas/Student"))
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */


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
     * @OA\Post(
     *      path="/students/register",
     *      operationId="storeStudent",
     *      tags={"Students"},
     *      summary="Sign up as Student",
     *      description="Returns Student data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreStudentRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Created student successfully"),
     * @OA\Property(property="student", type="object", ref="#/components/schemas/Student"),),
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */



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
            //'department' => 'string|max:255',
            'department_id' => 'required|numeric|exists:departments,id',
            //'school' => 'required|string|max:255',
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

        $dep_id = $fields['department_id'];

        // //if school already exist in database add it's id to the department
        // $schools = School::all();
        // $school_id = 0;
        // foreach ($schools as $school) {
        //     if ($school->name === $fields['school']) {
        //         $school_id = $school->id;
        //     }
        // }

        // //if school doesnot exist create school
        // if ($school_id == 0) {
        //     $school = School::create([
        //         'name' => $fields['school']
        //     ]);
        //     $school_id = $school->id;
        // }


        // //if department already exist in database add it's id to the student
        // $departments = Department::all();
        // $department_id = 0;
        // foreach ($departments as $department) {
        //     if ($department->name === $fields['department']) {
        //         $department_id = $department->id;
        //     }
        // }

        // //if department doesnot exist create department
        // if ($department_id == 0) {
        //     $department = Department::create([
        //         'name' => $fields['department'],
        //         'school_id' => $school_id,
        //     ]);
        //     $department_id = $department->id;
        // }

        //create student
        $student = Student::create([
            'id' => $user->id,
            'studentCode' => $fields['studentCode'],
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
        Mail::send('email.verifyemail', ['url' => 'http://13.58.190.211', 'verificationCode' => $verificationCode], function ($message) use ($request) {
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


    /**
     * @OA\Get(
     *      path="/students/{id}",
     *      operationId="getStudent",
     *      tags={"Student"},
     *      summary="Get student",
     *      description="Returns student",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="student", type="object", ref="#/components/schemas/Student")
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */




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
     * @OA\Get(
     *      path="/students/me",
     *      operationId="getStudentProfile",
     *      tags={"Student"},
     *      summary="Get Student profile",
     *      description="Returns student profile",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="student", type="object", ref="#/components/schemas/Student")
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\Property(property="message", type="string", example="Unauthenticated"),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

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



    /**
     * @OA\Put(
     *      path="/students/me",
     *      operationId="editStudent",
     *      tags={"Students"},
     *      summary="Edit student",
     *      description="Returns Student data",
     * security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreStudentRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Updated student successfully"),
     * @OA\Property(property="student", type="object", ref="#/components/schemas/Student"),),
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     * @OA\Property(property="message", type="string", example="Failed to update student"),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\Property(property="message", type="string", example="Unauthenticated"),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

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
