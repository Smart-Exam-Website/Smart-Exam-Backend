<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     *      path="/exams/step1",
     *      operationId="storeExamStepOne",
     *      tags={"Exams"},
     *      summary="Store Exam Data",
     *      description="Returns created exam id",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepOne")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Created exam successfully"),
     * @OA\Property(property="examId", type="integer"),),
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
    public function storeStepOne(Request $request)
    {
        // create exam
        $rules = [
            'name' => 'required|string',
            'numberOfTrials' => 'required',
            'description' => 'required',
            'totalMark' => 'required',
            'duration' => 'required',
            'startAt' => 'required|date',
            'endAt' => 'required|date',
            'examSubject' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        //create the exam

        $examDetails = $request->only(['name', 'totalMark', 'description', 'startAt', 'endAt', 'duration', 'numberOfTrials', 'examSubject']);
        $exam = Exam::create($examDetails);
        if (!$exam) {
            return response()->json(['message' => 'failed to create exam'], 400);
        }

        return response()->json(['message' => 'successfully created exam!', 'examId' => $exam->id]);
    }

    /**
     * @OA\Post(
     *      path="/exams/step2",
     *      operationId="storeExamStepTwo",
     *      tags={"Exams"},
     *      summary="Store Exam Data",
     *      description="Add Exam Options",
     * security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepTwo")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully added exam options!"),
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
    public function storeStepTwo(Request $request)
    {
        // create exam
        $rules = [
            'examId' => 'required',
            'faceRecognition' => 'required|boolean',
            'faceDetection' => 'required|boolean',
            'questionsRandomOrder' => 'required|boolean',
            'plagiarismCheck' => 'required|boolean',
            'disableSwitchBrowser' => 'required|boolean',
            'gradingMethod' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        //create the exam

        $options = $request->only(['faceRecognition', 'faceDetection', 'questionsRandomOrder', 'plagiarismCheck', 'disableSwitchBrowser', 'gradingMethod']);

        //add its options

        $options['exam_id'] = $request->examId;
        $option = Configuration::create($options);
        if (!$option) {
            return response()->json(['message' => 'failed to create exam'], 400);
        }
        //link to questions

        return response()->json(['message' => 'successfully added exam options!']);
    }


    /**
     * @OA\Post(
     *      path="/exams/step3",
     *      operationId="storeExamStepThree",
     *      tags={"Exams"},
     *      summary="Store Exam Data",
     *      description="Add Questions",
     * security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepThree")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully added questions to exam!"),
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


    public function storeStepThree(Request $request)
    {
        // create exam
        $rules = [
            'examId' => 'required',
            'questions.*.question_id' => ['required', 'numeric', 'exists:questions,id'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        //link to questions

        $exam = Exam::where('id', $request->examId)->first();

        $questions = $request->questions;

        $exam->questions()->attach($questions);

        return response()->json(['message' => 'successfully added questions to exam!']);
    }


    /**
     * @OA\Post(
     *      path="/exams/step4",
     *      operationId="storeExamStepFour",
     *      tags={"Exams"},
     *      summary="Store Exam Data",
     *      description="Add question marks and time",
     * security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepFour")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully created exam!"),
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
    public function storeStepFour(Request $request)
    {
        // create exam
        $rules = [
            'examId' => 'required',
            'questions.*.question_id' => ['required', 'numeric', 'exists:questions,id'],
            'questions.*.mark' => 'required',
            'questions.*.time' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        $exam = Exam::where('id', $request->examId)->first();



        $questions = $request->questions;

        $exam->questions()->sync($questions);

        return response()->json(['message' => 'successfully created exam!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
