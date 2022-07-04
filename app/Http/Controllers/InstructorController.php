<?php

namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\Instructor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    // List all instructors
    public function index()
    {
        $instructors = Instructor::all();


        $instructors->each(function ($instructor) {
            $instructor->user;
            $departments = $instructor->departments;

            foreach ($departments as $department) {
                $department->school;
            }
        });

        if (!$instructors) {
            return response()->json(['message' => "Error fetching instructors"], 400);
        }
        return response()->json(["instructors" => $instructors], 200);
    }

    // get all instructor's exams, whether published or not.
    public function showMyExams()
    {
        $exams = Exam::where(['instructor_id' => auth()->user()->id])->latest('created_at')->get();

        foreach ($exams as $exam) {
            $configs = $exam->config;
            $questions = $exam->questions;

            if (!$configs && !$questions) {
                $exam['status'] = 'No config or questions';
            } else if (!$configs) {
                $exam['status'] = 'No config';
            } else if (!$questions) {
                $exam['status'] = 'No questions';
            } else {
                $exam['status'] = 'Complete';
            }
        }

        return $exams;
    }

    // Signup as Instructor
    public function store(Request $request)
    {
        $rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'description' => 'required',
            'gender' => 'required',
            'image' => 'required',
            'phone' => 'required|size:11',
            'type' => 'required',
            'degree' => 'required',
            'departments.*.department_id' => ['required', 'numeric', 'exists:departments,id'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }
        $userDetails = $request->only(['firstName', 'lastName', 'email', 'gender', 'image', 'phone', 'type']);
        $password = $request->password;
        $hashedPass = Hash::make($password);
        $userDetails['password'] = $hashedPass;
        $user = User::create($userDetails);

        if (!$user) {
            return response()->json(['message' => 'Failed to create instructor'], 400);
        }
        $id = $user->id;
        $instructorDetails = $request->only('degree', 'description');
        $instructorDetails['id'] = $id;
        $instructorDetails['verified'] = 'false';
        try {
            $instructor = Instructor::create($instructorDetails);
        } catch (Throwable $e) {
            $user->delete();
            return response()->json(['message' => $e], 400);
        }
        if (!$instructor) {
            $user->delete();
            return response()->json(['message' => 'Failed to create instructor'], 400);
        }

        $departments = $request->departments;


        $instructor->departments()->attach($departments);



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


        return response()->json(['message' => 'Created instructor successfully', 'instructor' => $instructor], 201);
    }
    // Details of one instructor
    public function show(Instructor $instructor)
    {
        if (!$instructor) {
            return response()->json(['message' => "No instructor was found"], 400);
        }
        $departments = $instructor->departments;

        foreach ($departments as $department) {
            $department->school;
        }

        $instructor->user;

        return response()->json(['instructor' => $instructor], 200);
    }
    
    // get Instructor's Profile
    public function showProfile()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => "No user was found"], 400);
        }
        // dd($user);
        $instructor = $user->instructor()->first();
        $departments = $instructor->departments;

        foreach ($departments as $department) {
            $department->school;
        }
        $instructor->user;

        return response()->json(['instructor' => $instructor], 200);
    }

    // Edit Instructor's Profile.
    public function editProfile(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => "No user was found"], 400);
        }
        $this->authorize('update', $user->instructor);
        $rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'description' => 'required',
            'email' => 'email',
            'gender' => 'required',
            'image' => 'required',
            'phone' => 'required|size:11',
            'degree' => 'required',
            'departments.*.department_id' => ['required', 'numeric', 'exists:departments,id'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }
        $userDetails = $request->only(['firstName', 'lastName', 'email', 'gender', 'image', 'phone', 'type']);
        if ($user->email !=  $userDetails['email']) {
            $similarEmail = User::where('email', $userDetails['email'])->get();
            if ($similarEmail) {
                response()->json(['message' => 'Email already taken'], 400);
            }
        }

        $user->update($userDetails);
        if (!$user) {
            return response()->json(['message' => 'Failed to update instructor'], 400);
        }
        $instructorDetails = $request->only('degree', 'description');
        $instructor = $user->instructor()->first();
        if (!$instructor) {
            return response()->json(['message' => 'Failed to find instructor'], 400);
        }
        $instructor->update($instructorDetails);

        $departments = $request->departments;

        $instructor->departments()->sync($departments);
        return response()->json(['message' => 'Updated instructor successfully', 'instructor' => $instructor], 200);
    }
}
