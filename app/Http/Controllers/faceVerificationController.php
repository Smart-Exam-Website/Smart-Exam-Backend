<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class faceVerificationController extends Controller
{
    public function faceVerification(Request $request)
    {
        if (auth()->user()->type != 'student') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }
        $rules = [
            'image1' => 'required',
            'examId' => 'required',
        ];

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'This exam does not exist!'], 400);
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

        // $verified = rand(0,1);
        // if (!$verified) {
        //     $image = $request->image1;
        //     // list($baseType, $image) = explode(';', $imageEncoded);
        //     // list(, $image) = explode(',', $image);
        //     $imageDecoded = base64_decode($image);
        //     $imageName = Str::random(30) . '.jpg';
        //     $path = Storage::disk('s3')->put('uploads/' . $imageName, $imageDecoded);
        //     $path = Storage::disk('s3')->url($path);

        //     return response()->json(['message' => 'Success!', 'verified' => $verified, 'image' => $imageName]);
        // }

        // return response()->json(['message' => 'Success!', 'verified' => $verified]);
        $route = 'machinelearning.api.smart-exam.ml/m2/verify';
        if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
            $route = 'http://'.$route;
            dd($route);
        } else {
            $route = 'https://'.$route;
            dd($route);
        }


        $response = Http::post($route, [
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
                if (!$verified) {
                    $image = $request->image1;
                    // list($baseType, $image) = explode(';', $imageEncoded);
                    $imageData = explode(',', $image);
                    $imageDecoded = base64_decode($imageData[1]);
                    $imageName = Str::random(30) . '.jpg';
                    $path = Storage::disk('s3')->put('uploads/' . $imageName, $imageDecoded);
                    $path = Storage::disk('s3')->url($path);

                    return response()->json(['message' => 'Success!', 'verified' => $verified, 'image' => $imageName]);
                }

                return response()->json(['message' => 'Success!', 'verified' => $verified]);
            }
        } else {
            return response()->json(['message' => 'Failed to verify'], 400);
        }
    }
}
