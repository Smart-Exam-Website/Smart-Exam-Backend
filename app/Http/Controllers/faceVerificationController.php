<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class faceVerificationController extends Controller
{

    /**
     * @OA\Post(
     *      path="/faceVerification",
     *      operationId="faceVerificationAPI",
     *      tags={"ML Models"},
     *      summary="face Verification",
     *      description="Returns number of faces",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/FaceVerificationRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Success!"),
     * @OA\Property(property="verified", type="bool", example=true),)
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

    public function faceVerification(Request $request)
    {
        if (auth()->user()->type != 'student') {
            return response()->json(['message' => 'Unauthorized!'], 400);
        }
        $studentId = auth()->user()->id;
        $rules = [
            'image1' => 'required',
            'image2' => 'required',
            'examId' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'No image added!'], 400);
        }

        $response = Http::post('http://3.142.238.250:5000/verify', [
            'img' => json_encode([
                'img1' => $request->image1,
                'img2' => $request->image2,
            ])
        ]);

        return response()->json(['message' => 'Success!', 'verified' => $response->object()]);

        if ($response->ok()) {
            if ($response->status() != 200) {
                return response()->json(['message' => 'Failed to send images!'], 400);
            } else {
                $verified = $response->object()->verified;
                $examId = $request->examId;
                $status = DB::table('examSession')->update(['exam_id' => $examId, 'student_id' => $studentId, 'isVerified' => $verified]);
                if ($status) {
                    return response()->json(['message' => 'Success!', 'verified' => $verified]);
                } else {
                    return response()->json(['message' => 'Error!'], 400);
                }
            }
        }  else {
            return response()->json(['message' => 'An error occurred!'], 400);
        }
    }
}
