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
use PhpParser\Node\Expr\BinaryOp\NotEqual;

class UserController extends Controller
{
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

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'You Have Successfully Logged Out'
        ];
    }

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

        /**
     * @OA\Post(
     *      path="/auth/forgotPassword",
     *      operationId="forgotPassword",
     *      tags={"Auth"},
     *      summary="Request to reset password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Reset link sent!"),
     * ),
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

        Mail::send('email.forgotPassword', ['url' => 'http://localhost:8080/', 'token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['status' => 'Reset link sent!'], 200);
    }

            /**
     * @OA\Put(
     *      path="/auth/forgotPassword",
     *      operationId="resetPassword",
     *      tags={"Auth"},
     *      summary="Resetting password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Password has been successfully changed!"),
     * ),
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

            /**
     * @OA\Post(
     *      path="/auth/verifyEmail",
     *      operationId="verifyEmail",
     *      tags={"Auth"},
     *      summary="Request to verify email",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/VerifyEmailRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Account verified!"),
     * ),
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
            $instructor = $user->instructor;
            $instructor->verified = "true";
            $instructor->save();
            return response()->json(['message' => 'Account verified!'], 200);
        }
    }
}
