<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request){
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        //check email
        $user = User::where('email', $fields['email'])->first();
        //check password
        if(!$user|| !Hash::check($fields['password'],$user->password)){
            return response([
                'message' => 'Email Or Password is incorrect'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response,200);

    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged Out'
        ];
    }

    public function changePassword(Request $request){
        $user = auth()->user();
        $fields = $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required'
        ]);
        
        if(password_verify($fields['currentPassword'], $user->password)){
            $user->password=Hash::make($fields['newPassword']);
            $user->save();
            return response([
                'message' => 'Password Updated Successfully'
            ], 201);
        }
        else{
            return response([
                'message' => 'User Password is incorrect'
            ], 401);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;
        $user = User::where('email', $email);
        if(!$user) {
            return response()->json(['message' => 'There is no user with that email.'], 400);
        }
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $request->email, 
            'token' => $token, 
            'created_at' => Carbon::now()
          ]);

        Mail::send('email.forgotPassword', ['url' => 'http://localhost:8080/','token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['status' => 'Reset link sent!'], 200);
    }


    public function resetPassword (Request $request) { 
        $credentials = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string'
        ]);
        $user = User::where(['email' => $credentials['email']])->get()->first();
        if(!$user) {
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
}
