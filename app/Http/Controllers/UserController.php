<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Login
    public function login(Request $request)
    {

        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        //check email
        $user = User::where('email', $fields['email'])->first();

        //check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Email Or Password is incorrect'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 200);
    }

    // Log-out

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'You Have Successfully Logged Out'
        ];
    }

    // Change Password


    public function changePassword(Request $request)
    {
        $user = auth()->user();
        $fields = $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required'
        ]);

        if (password_verify($fields['currentPassword'], $user->password)) {
            $user->password = Hash::make($fields['newPassword']);
            $user->save();
            return response([
                'message' => 'Password Updated Successfully'
            ], 201);
        } else {
            return response([
                'message' => 'User Password is incorrect'
            ], 401);
        }
    }

    // Forgot password

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;
        $user = User::where('email', $email);
        if (!$user) {
            return response()->json(['message' => 'There is no user with that email.'], 400);
        }
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgotPassword', ['url' => 'http://smart-exam.ml/reset-password/', 'token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['status' => 'Reset link sent!'], 200);
    }

    // Reset password


    public function resetPassword(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string'
        ]);
        $user = User::where(['email' => $credentials['email']])->get()->first();
        if (!$user) {
            return response()->json(["msg" => "No user with that email"], 400);
        }
        $password = $credentials['password'];


        $tokenMatch = DB::table('password_resets')->where(['token' => $credentials['token']]);


        if (!$tokenMatch) {
            return response()->json(["msg" => "Invalid token provided"], 400);
        }
        $user->password = Hash::make($password);
        $user->save();

        return response()->json(["msg" => "Password has been successfully changed"], 200);
    }


    // Verify email
    public function verifyEmail(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);



        $code = $credentials['code'];
        $email = $credentials['email'];

        $dbEntry = DB::table('verification_codes')->where('code', $code)->get()->first();

        if (!$dbEntry or ($dbEntry and $dbEntry->email != $email)) {
            return response()->json(['message' => 'Wrong code!'], 400);
        } else {
            $user = User::where('email',  $email)->get()->first();
            $user->email_verified_at = now();
            $specialUser = $user->instructor;
            if(!$specialUser) {
                $specialUser = $user->student;
            } else {
                $specialUser->verified = "true";
            }

            $specialUser->save();
            return response()->json(['message' => 'Account verified!'], 200);
        }
    }

    // retrieve user image

    public function getImage() {

        $image2 = Storage::disk('s3')->get('uploads/AOGRQGLjcMhNCOZGiWs0faJRfsxCrcteVfWUwrfE.jpg');
        // image malformed issue
        return base64_encode($image2);
    }
}
