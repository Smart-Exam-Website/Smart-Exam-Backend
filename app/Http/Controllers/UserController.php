<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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

        return response($response,201);

    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged Out'
        ];
    }

    public function changePassword(Request $request){
        $user = Auth::user();
        $fields = $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required'
        ]);
        if($fields['currentPassword'] == $user->password){
            $user->password=Hash::make($fields['newPassword']);
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
}
