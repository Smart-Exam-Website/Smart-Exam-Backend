<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        // $numberOfFaces = rand(1, 10);
        // if ($numberOfFaces > 1) {
        //     $image = $request->image;
        //     // list($baseType, $image) = explode(';', $imageEncoded);
        //     // list(, $image) = explode(',', $image);
        //     $imageDecoded = base64_decode($image);
        //     $imageName = Str::random(30) . '.jpg';
        //     $path = Storage::disk('s3')->put('uploads/' . $imageName, $imageDecoded);
        //     $path = Storage::disk('s3')->url($path);

        //     return response()->json(['message' => 'Success!', 'numberOfFaces' => $numberOfFaces, 'image' => $imageName]);
        // }

        // return response()->json(['message' => 'Success!', 'numberOfFaces' => $numberOfFaces]);




        $response = Http::post('http:/44.192.1.67/m1/detect', [
            'image_encode' => $request->image,
        ]);

        // return response()->json(['message' => 'Success!', 'verified' => $response->object()]);

        if ($response->ok()) {
            if ($response->status() != 200) {
                return response()->json(['message' => 'Failed to send image!'], 400);
            } else {
                $numberOfFaces = $response->object()->number_of_faces;
                if ($numberOfFaces > 1) {
                    $image = $response->object()->image_encode;
                    // list($baseType, $image) = explode(';', $imageEncoded);
                    // list(, $image) = explode(',', $image);
                    $imageDecoded = base64_decode($image);
                    $imageName = Str::random(30) . '.jpg';
                    $path = Storage::disk('s3')->put('uploads/' . $imageName, $imageDecoded);
                    $path = Storage::disk('s3')->url($path);

                    return response()->json(['message' => 'Success!', 'numberOfFaces' => $numberOfFaces, 'image' => $imageName]);
                }

                return response()->json(['message' => 'Success!', 'numberOfFaces' => $numberOfFaces]);
            }
        } else {
            return response()->json(['message' => 'An error occurred!'], 400);
        }
    }
}
