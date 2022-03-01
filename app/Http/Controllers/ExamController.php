<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\examSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * @OA\Get(
     *      path="/exams",
     *      operationId="getexamsList",
     *      tags={"Exams"},
     *      summary="Get list of exams",
     *      description="Returns list of exams",
     *      security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="exams", type="array", @OA\Items(ref="#/components/schemas/Exam"))
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index()
    {
        // Get list of published exams!
        $exams = Exam::latest('created_at')->get();
        $finalExams = [];

        foreach ($exams as $exam) {
            $exam->config;
            $exam->questions;

            if ($exam->isPublished) {
                array_push($finalExams, $exam);
            }
        }

        if (auth()->user()->type == 'student') {
            foreach ($finalExams as $exam) {
                $isSubmitted = false;
                $sessions = examSession::where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->get();
                if ($sessions) {
                    foreach ($sessions as $session) {

                        if ($session->isSubmitted) {
                            $isSubmitted = true;
                            break;
                        }
                    }
                }
                $exam['isSubmitted'] = $isSubmitted;
            }
            foreach ($finalExams as $exam) {
                $isMarked = false;
                $examMark = DB::table('exam_students')->where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->get()->first();
                if ($examMark) {
                    $exam['isMarked'] = true;
                    $exam['mark'] = $examMark;
                } else {
                    $exam['isMarked'] = false;
                }
            }
        }
        return $finalExams;
    }

    /**
     * @OA\Get(
     *      path="/instructors/myExams",
     *      operationId="getexamsList",
     *      tags={"Instructor"},
     *      summary="Get list of exams",
     *      description="Returns list of exams",
     *      security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="exams", type="array", @OA\Items(ref="#/components/schemas/Exam"))
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */

    public function indexInstructor()
    {
        // get all instructor's exams, whether published or not.
        $exams = Exam::where(['instructor_id' => auth()->user()->id])->latest('created_at')->get();

        foreach ($exams as $exam) {
            $configs = $exam->config;
            $questions = $exam->questions;

            if (!$configs && !$questions) {
                $exam['status'] = 'No config or questions';
            } else if (!$configs) {
                $exam['status'] = 'No config';
            } else if (!$questions) {
                $exam['status'] = 'No questions';
            } else {
                $exam['status'] = 'Complete';
            }
        }

        return $exams;
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
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
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
        $examDetails['instructor_id'] = auth()->user()->id;
        $examDetails['isPublished'] = false;
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
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
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
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
        // create exam
        $rules = [
            'examId' => 'required',
            'questions.*.question_id' => ['required', 'numeric', 'exists:questions,id'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        // divide marks and duration among questions.

        //link to questions

        $exam = Exam::where('id', $request->examId)->first();

        $questions = $request->questions;
        $mark = $exam->totalMark / count($questions);
        // $duration = $exam->duration / count($questions);

        $exam->questions()->attach($questions, ['mark' => $mark]);

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
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
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
     * @OA\Get(
     *      path="/exams/{exam}",
     *      operationId="getExamDetails",
     *      tags={"Exam"},
     *      summary="Get exam details",
     *      description="Returns exam details",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="exam", type="object", ref="#/components/schemas/Exam")
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function show(Exam $exam)
    {
        return response()->json(['exam' => $exam]);
    }


    /**
     * @OA\Get(
     *      path="/exams/{exam}/questions",
     *      operationId="getExamQuestions",
     *      tags={"Exam"},
     *      summary="Get exam questions",
     *      description="Returns question details",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="questions", type="array", @OA\Items(ref="#/components/schemas/Question"))
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function getExamQuestions(Exam $exam)
    {

        $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();

        foreach ($questions as $question) {
            $type = $question->type;

            if ($type == 'mcq') {
                $answers = DB::table('mcq_answers')->where('question_id', $question->id)->join('options', 'options.id', 'mcq_answers.id')->select(['options.id', 'mcq_answers.isCorrect', 'options.value'])->get();
            }
            $question->answers = $answers;
        }
        return response()->json(['questions' => $questions]);
    }


    /**
     * @OA\Post(
     *      path="/exams/{exam}/start",
     *      operationId="startExam",
     *      tags={"Exam"},
     *      summary="start exam",
     *      description="change exam status ",
     * security={ {"bearer": {} }},
     * @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *  @OA\Property(property="startTime", type="datetime", example="2022-02-22 02:45:00"),
     * @OA\Property(property="numberOfFaces", type="integer", example="5"),
     * @OA\Property(property="isVerified", type="boolean", example="true")
     * ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully started exam!"),
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




    public function startExam(Request $request, Exam $exam)
    {
        if (auth()->user()->type != 'student') {
            return response()->json(['message' => 'cannot take exam as instructor!'], 400);
        }
        $rules = [
            'startTime' => 'required|date',
            'numberOfFaces' => 'required|integer',
            'isVerified' => 'required|boolean'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        if ($exam->startAt > $request->startTime) {
            return response()->json(['message' => 'You cannot start this exam yet!', 400]);
        }

        if ($exam->endAt < $request->startTime) {
            return response()->json(['message' => 'Exam is closed now!'], 400);
        }

        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->latest()->get()->first();
        if ($examSession) {
            $attempt = $examSession->attempt + 1;
            if ($attempt <= $exam->numberOfTrials) {
                $examSession = examSession::create([
                    'exam_id' => $exam->id,
                    'student_id' => auth()->user()->id,
                    'startTime' => $request->startTime,
                    'attempt' => $attempt,
                    'isVerified' => $request->isVerified,
                    'numberOfFaces' => $request->numberOfFaces
                ]);
            } else {
                return response()->json(['message' => 'Exceeded number of attempts!']);
            }
        } else {
            $examSession = examSession::create([
                'exam_id' => $exam->id,
                'student_id' => auth()->user()->id,
                'startTime' => $request->startTime,
                'isVerified' => $request->isVerified,
                'numberOfFaces' => $request->numberOfFaces
            ]);
        }



        if (!$examSession) {
            return response()->json(['message' => 'Failed to add exam session'], 400);
        }


        //return all questions.
        $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();

        foreach ($questions as $question) {
            $type = $question->type;

            if ($type == 'mcq') {
                $answers = DB::table('mcq_answers')->where('question_id', $question->id)->join('options', 'options.id', 'mcq_answers.id')->select(['options.id', 'mcq_answers.isCorrect', 'options.value'])->get();
            }
            $question->answers = $answers;
        }
        return response()->json(['questions' => $questions]);
    }

    /**
     * @OA\Get(
     *      path="/exams/{exam}/configs",
     *      operationId="getExamConfigs",
     *      tags={"Exam"},
     *      summary="Get exam configs",
     *      description="Returns exam configurations",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="configuration", type="object", ref="#/components/schemas/Configuration")
     * ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */

    public function getExamConfigurations(Exam $exam)
    {
        if (!$exam) {
            return response()->json(['message' => 'No exam with this id!'], 404);
        }
        $config = Configuration::where('exam_id', $exam->id)->get()->first();

        if (!$config) {
            return response()->json(['message' => 'No configurations found for this exam!'], 400);
        }
        return response()->json(['configuration' => $config]);
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
     * @OA\Put(
     *      path="/exams/{exam}/step1",
     *      operationId="updateExamStepOne",
     *      tags={"Exam"},
     *      summary="Store Exam Data",
     *      description="Returns success message",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepOne")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
    public function updateStepOne(Request $request, Exam $exam)
    {
        if (!$exam) {
            return response()->json(['message' => 'Exam not found!']);
        }
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to update exam!'], 403);
        }
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

        // update exam details

        $examDetails = $request->only(['name', 'totalMark', 'description', 'startAt', 'endAt', 'duration', 'numberOfTrials', 'examSubject']);
        $examDetails['instructor_id'] = auth()->user()->id;
        $exam->update($examDetails);

        return response()->json(['message' => 'successfully updated exam!']);
    }


    /**
     * @OA\Put(
     *      path="/exams/{exam}/step2",
     *      operationId="updateExamStepTwo",
     *      tags={"Exam"},
     *      summary="Store Exam Data",
     *      description="Returns success message",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepTwo")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
    public function updateStepTwo(Request $request, Exam $exam)
    {
        if (!$exam) {
            return response()->json(['message' => 'Exam not found!']);
        }
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to update exam!'], 403);
        }
        // create exam
        $rules = [
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


        $options = $request->only(['faceRecognition', 'faceDetection', 'questionsRandomOrder', 'plagiarismCheck', 'disableSwitchBrowser', 'gradingMethod']);

        //add its options

        $option = $exam->config;
        $option->update($options);

        return response()->json(['message' => 'successfully adjusted exam options!']);
    }
    /**
     * @OA\Put(
     *      path="/exams/{exam}/step3",
     *      operationId="updateExamStepThree",
     *      tags={"Exam"},
     *      summary="Store Exam Data",
     *      description="Returns success message",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepThree")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
    public function updateStepThree(Request $request, Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
        // update exam
        $rules = [
            'questions.*.question_id' => ['required', 'numeric', 'exists:questions,id'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }


        $questions = $request->questions;
        $mark = $exam->totalMark / count($questions);
        // $duration = $exam->duration / count($questions);
        $exam->questions()->detach();
        $exam->questions()->attach($questions, ['mark' => $mark]);
        // $exam->questions()->sync($questions, ['mark' => $mark]);
        return response()->json(['message' => 'successfully added questions to exam!']);
    }
    /**
     * @OA\Put(
     *      path="/exams/{exam}/step4",
     *      operationId="updateExamStepFour",
     *      tags={"Exam"},
     *      summary="Store Exam Data",
     *      description="Returns success message",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreExamStepFour")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
    public function updateStepFour(Request $request, Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized to create exam!'], 403);
        }
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


        $questions = $request->questions;

        $exam->questions()->sync($questions);

        return response()->json(['message' => 'successfully created exam!']);
    }

    /**
     * @OA\Delete(
     *      path="/exams/{exam}",
     *      operationId="deleteExam",
     *      tags={"Exam"},
     *      summary="delete exam",
     *      description="deletes exam",
     *      security={ {"bearer": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="successfully deleted exam",
     *
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function destroy(Exam $exam)
    {
        $user = auth()->user();
        if ($user->type != 'instructor') {
            return response()->json(['message' => 'Not authorized to delete exam']);
        }
        $exam->delete();
        return response()->json('Exam deleted successfully', 200);
    }

    /**
     * @OA\Get(
     *      path="/exams/{exam}/answers",
     *      operationId="getStudentAnswers",
     *      tags={"Exam"},
     *      summary="get student answers",
     *      description="returns all student answers of a certain exam",
     *      security={ {"bearer": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="success!",
     *          @OA\JsonContent(
     *              @OA\Property(property="studentAnswer", type="object", ref="#/components/schemas/StudentAnswer")
     *          ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */


    public function getStudentAnswers(Exam $exam)
    {

        $student = auth()->user();
        if ($student->type != 'student') {
            return response()->json(['message' => 'Not a student!'], 400);
        }
        $studentId = $student->id;

        $answers = Answer::where(['student_id' => $studentId, 'exam_id' => $exam->id])->get();

        if (!$answers) {
            return response()->json(['message' => 'No answers found!'], 400);
        }

        return response()->json(['message' => 'Success!', 'answers' => $answers]);
    }


    /**
     * @OA\Post(
     *      path="/exams/{exam}/submit",
     *      operationId="submitExam",
     *      tags={"Exam"},
     *      summary="submit exam",
     *      description="change exam status for student to submitted",
     * security={ {"bearer": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully submitted exam!"),
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


    public function submitExam(Exam $exam)
    {

        // attempt???

        $student = auth()->user();

        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => $student->id])->latest()->get()->first();
        if (!$examSession) {
            return response()->json(['message' => 'No exam session for this student!'], 400);
        }

        if ($examSession->isSubmitted) {
            return response()->json(['message' => 'Exam already submitted!'], 400);
        }

        // check number of questions of exam
        $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();
        // check how many questions the student answered!
        $answers = DB::table('answers')->where(['exam_id' => $exam->id, 'student_id' => $student->id])->get();
        // cannot submit until they are all answered!
        if ($answers->count() < $questions->count()) {
            return response()->json(['message' => 'You cannot submit yet!, you haven\'t answered all questions'], 400);
        }

        $status = DB::table('examsession')->update(['exam_id' => $exam->id, 'student_id' => $student->id, 'isSubmitted' => true, 'submittedAt' => now()]);

        if ($status) {
            return response()->json(['message' => 'Submitted exam successfully!']);
        } else {
            return response()->json(['message' => 'Failed to submit exam!'], 400);
        }

        // if marking is automatic, should i mark the exam?



    }


    /**
     * @OA\Post(
     *      path="/exams/{exam}/publish",
     *      operationId="publishExam",
     *      tags={"Exam"},
     *      summary="publish exam",
     *      description="change exam status ",
     * security={ {"bearer": {} }},
     * @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *  @OA\Property(property="isPublished", type="boolean", example="true"),),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="successfully published exam!"),
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



    public function publishExam(Request $request, Exam $exam)
    {
        $rules = [
            'isPublished' => 'boolean|required'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data is invalid!'], 400);
        }

        if ($exam->isPublished && $request->isPublished) {
            return response()->json(['message' => 'Exam already published!'], 400);
        }

        $exam->isPublished = $request->isPublished;

        $exam->save();

        return response()->json(['message' => 'Exam publish settings set successfully!']);
    }


    public function getExamSolutions(Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }
        // get list of all students who solved the exam
        $solvedExams = DB::table('examsession')->where(['exam_id' => $exam->id, 'isSubmitted' => true])->get();
        if (!$solvedExams) {
            return response()->json(['message' => 'No solutions found for this exam!'], 400);
        }
        foreach ($solvedExams as $solvedExam) {
            $user = DB::table('users')->where(['id' => $solvedExam->student_id])->get()->first();
            $solvedExam->name = $user->firstName . ' ' . $user->lastName;
            $solvedExam->studentCode = $user->student->studentCode;
            // if exam is marked, get mark and send it with the request.
            $foundExam = DB::table('exam_students')->where(['exam_id' => $exam->id, 'student_id' => $solvedExam->student_id])->get()->first();
            if ($foundExam) {
                $solvedExam->isMarked = true;
                $solvedExam->mark = $foundExam->totalMark;
            } else {
                $solvedExam->isMarked = false;
                $solvedExam->mark = 0;
            }
        }

        return response()->json(['message' => 'Successfully fetched solutions!', 'solvedExams' => $solvedExams]);
    }


    public function getDetailedExamSolution(Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }

        $studentId = request('student_id');
        $user = DB::table('users')->where(['id' => $studentId])->get()->first();
        $studentName = $user->firstName . ' ' . $user->lastName;
        // $studentCode = $user->student->studentCode;

        $session = DB::table('examsession')->where(['exam_id' => $exam->id, 'student_id' => $studentId, 'isSubmitted' => true])->get()->first();
        if (!$session) {
            return response()->json(['message' => 'No session found for this student!'], 400);
        }

        $solutions = DB::table('answers')->where(['exam_id' => $exam->id, 'student_id' => $studentId])->get();

        if (!$solutions) {
            return response()->json(['message' => 'Failed to fetch student solutions!'], 400);
        }



        foreach ($solutions as $solution) {
            $solution->question = DB::table('questions')->where(['id' => $solution->question_id])->get()->first();
            if($solution->question->type == 'mcq') {
                $answers = DB::table('mcq_answers')->where(['question_id' => $solution->question->id])->join('options', 'options.id','mcq_answers.id')->get();
                // $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();

                $solution->question->answers = $answers;
            }

        }

        $examConfig = DB::table('configs')->where(['exam_id' => $exam->id])->get()->first();


        $numberOfFaces = ($examConfig->faceDetection) ? $session->numberOfFaces : null;
        $isVerified = ($examConfig->faceDetection) ? $session->isVerified : null;

        return response()->json(['message' => 'Fetched solution successfully', 'studentName' => $studentName, 'solution' => $solutions, 'numberOfFaces' => $numberOfFaces, 'isVerified' => $isVerified]);
    }
}
