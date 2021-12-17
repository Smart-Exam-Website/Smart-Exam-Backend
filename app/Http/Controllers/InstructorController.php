<?php

namespace App\Http\Controllers;

use App\Models\AcademicInfo;
use App\Models\Instructor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    /**
     * @OA\Get(
     *      path="/instructors",
     *      operationId="getInstructorsList",
     *      tags={"Instructors"},
     *      summary="Get list of Instructors",
     *      description="Returns list of Instructors",
     *      security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="instructors", type="array", @OA\Items(ref="#/components/schemas/Instructor"))
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
     *      path="/instructors/register",
     *      operationId="storeInstructor",
     *      tags={"Instructors"},
     *      summary="Sign up as instructor",
     *      description="Returns Instructor data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreInstructorRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Created instructor successfully"),
     * @OA\Property(property="instructor", type="object", ref="#/components/schemas/Instructor"),),
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
    public function store(Request $request)
    {
        $rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'description' => 'required',
            'gender' => 'required',
            'image' => 'required|url',
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
        Mail::send('email.verifyemail', ['url' => 'http://localhost:8080/', 'verificationCode' => $verificationCode], function ($message) use ($request) {
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

    /**
     * @OA\Get(
     *      path="/instructors/{instructor}",
     *      operationId="getInstructor",
     *      tags={"Instructor"},
     *      summary="Get instructor",
     *      description="Returns instructor",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="instructor", type="object", ref="#/components/schemas/Instructor")
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
    /**
     * @OA\Get(
     *      path="/instructors/me",
     *      operationId="getInstructorProfile",
     *      tags={"Instructor"},
     *      summary="Get instructor profile",
     *      description="Returns instructor profile",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="instructor", type="object", ref="#/components/schemas/Instructor")
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

    /**
     * @OA\Put(
     *      path="/instructors/me",
     *      operationId="editInstructor",
     *      tags={"Instructors"},
     *      summary="Edit instructor",
     *      description="Returns Instructor data",
     * security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreInstructorRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Updated instructor successfully"),
     * @OA\Property(property="instructor", type="object", ref="#/components/schemas/Instructor"),),
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     * @OA\Property(property="message", type="string", example="Failed to update instructor"),
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
            'image' => 'required|url',
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
