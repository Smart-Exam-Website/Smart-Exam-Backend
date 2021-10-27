<?php

namespace App\Http\Controllers;

use App\Models\AcademicInfo;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instructors = Instructor::all();


        $instructors->each(function ($instructor) {
            $instructor->user;
            $instructor->academicInfos;
        });

        if (!$instructors) {
            return response()->json(['message' => "Error fetching instructors"], 400);
        }
        return response()->json($instructors, 200);
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
        $rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'gender' => 'required',
            'image' => 'required|url',
            'phone' => 'required|size:11',
            'type' => 'required',
            'degree' => 'required',
            'department' => 'required',
            'school' => 'required',
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
        $instructorDetails = $request->only('degree');
        $instructorDetails['user_id'] = $id;
        $instructorDetails['verified'] = 'false';
        $instructor = Instructor::create($instructorDetails);
        if (!$instructor) {
            return response()->json(['message' => 'Failed to create instructor'], 400);
        }

        $academicInfoDetails = $request->only(['department', 'school']);
        $academicInfo = AcademicInfo::find($academicInfoDetails);
        if(!$academicInfo) {
            $academicInfo = AcademicInfo::create($academicInfoDetails);
        }
        $academicInfo->instructors()->attach($instructor->id);
        return response()->json(['message' => 'Created instructor successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function show(Instructor $instructor)
    {
        if (!$instructor) {
            return response()->json(['message' => "No instructor was found"], 400);
        }
        $instructor->academicInfos;
        $userDetails = $instructor->user()->first();
        

        $collectI = collect($instructor);
        $collectU = collect($userDetails);

        $collectI = $collectI->merge($collectU);

        return response()->json(['instructor' => $collectI], 200);
    }
    /**
     * Display the specified resource's profile.
     *
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function showProfile()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => "No user was found"], 400);
        }
        // dd($user);
        $instructor = $user->instructor()->first();
        $instructor->academicInfos;
        $collectI = collect($instructor);
        $collectU = collect($user);

        $collectI = $collectI->merge($collectU);

        return response()->json(['instructor' => $collectI], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function edit(Instructor $instructor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instructor $instructor)
    {
        //
    }
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
            'email' => 'email|unique:users',
            'gender' => 'required',
            'image' => 'required|url',
            'phone' => 'required|size:11',
            'degree' => 'required',
            'department' => 'required',
            'school' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }
        $userDetails = $request->only(['firstName', 'lastName', 'email', 'gender', 'image', 'phone', 'type']);
        $user->update($userDetails);
        if (!$user) {
            return response()->json(['message' => 'Failed to update instructor'], 400);
        }
        $instructorDetails = $request->only('degree');
        $instructor = $user->instructor()->first();
        if (!$instructor) {
            return response()->json(['message' => 'Failed to find instructor'], 400);
        }
        $instructor->update($instructorDetails);

        $academicInfoDetails = $request->only(['department', 'school']);
        $academicInfo = AcademicInfo::find($academicInfoDetails);
        if(!$academicInfo) {
            $academicInfo = AcademicInfo::create($academicInfoDetails);
        }
        $instructor->academicInfos()->sync($academicInfo);
        return response()->json(['message' => 'Updated instructor successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instructor $instructor)
    {
        //
    }
}
