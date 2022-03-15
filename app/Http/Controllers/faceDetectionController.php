<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class faceDetectionController extends Controller
{

    // Face Detection

    public function faceDetection(Request $request)
    {
        if (auth()->user()->type != 'student') {
            return response()->json(['message' => 'Unauthorized!'], 400);
        }
        $rules = [
            'image' => 'required',
            'examId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error validating request body'], 400);
        }

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'This exam does not Exist!'], 400);
        }

        $config = $exam->config;

        if (!$config->faceDetection) {
            return response()->json(['message' => 'This exam does not support face detection!'], 400);
        }




        $response = Http::post('http://3.142.238.250/m1/detect', [
            'image_encode' => $request->image,
        ]);

        // return response()->json(['message' => 'Success!', 'verified' => $response->object()]);

        if ($response->ok()) {
            if ($response->status() != 200) {
                return response()->json(['message' => 'Failed to send image!'], 400);
            } else {
                $numberOfFaces = $response->object()->number_of_faces;

                return response()->json(['message' => 'Success!', 'numberOfFaces' => $numberOfFaces]);
            }
        } else {
            return response()->json(['message' => 'An error occurred!'], 400);
        }
    }
}
