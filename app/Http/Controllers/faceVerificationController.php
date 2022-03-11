<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\examSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        $rules = [
            'image1' => 'required',
            'examId' => 'required',
        ];

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'This exam does not Exist!'], 400);
        }

        $config = $exam->config;

        if (!$config->faceRecognition) {
            return response()->json(['message' => 'This exam does not support face detection!'], 400);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'No image added!'], 400);
        }
        $imagePath = auth()->user()->image;

        $image2 = Storage::disk('s3')->get('uploads/' . $imagePath);
        $image2Enc = base64_encode($image2);

        $image2Encoded = 'data:image/jpeg;base64,' . $image2Enc;



        $response = Http::post('http://3.142.238.250:5000/verify', [
            'img' => [[
                'img1' => $request->image1,
                'img2' => $image2Encoded,
            ]]
        ]);


        if ($response->ok()) {
            if ($response->status() != 200) {
                return response()->json(['message' => 'Failed to send images!'], 400);
            } else {
                $verified = $response->object()->pair_1->verified;

                return response()->json(['message' => 'Success!', 'verified' => $verified]);
            }
        } else {
            return response()->json(['message' => 'An error occurred!'], 400);
        }
    }
}
