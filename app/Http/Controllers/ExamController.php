<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\McqAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamStudent;
use App\Models\Student;
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
        $exams = Exam::latest('created_at')->get();

        foreach($exams as $exam) {
            $configs = $exam->config;
            $questions = $exam->questions;

            if(!$configs && !$questions) {
                $exam['status'] = 'No config or questions';
            } else if(!$configs) {
                $exam['status'] = 'No config';
            } else if (!$questions) {
                $exam['status'] = 'No questions';
            }
            else {
                $exam['status'] = 'Complete';
            }
        }
        return $exams;
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

    public function startExam(Request $request, Exam $exam)
    {
        if(auth()->user()->type != 'student') {
            return response()->json(['message' => 'cannot take exam as instructor!'], 400);
        }
        $rules = [
            'startTime' => 'required|date'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        DB::table('examSession')->insert([
            'exam_id' => $exam->id,
            'student_id' => auth()->user()->id,
            'startTime' => $request->startTime
        ]);


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

    public function getExamConfigurations(Exam $exam) {
        if(!$exam) {
            return response()->json(['message' => 'No exam with this id!'], 404);
        }
        $config = Configuration::where('exam_id' , $exam->id)->get()->first();

        if(!$config) {
            return response()->json(['message' => 'No configurations found for this exam!'], 400);
        }
        return response()->json(['configuration' => $config]);
    }


    /**
     * @OA\Get(
     *      path="/exams/totalMark/{id}",
     *      operationId="getExamAllStudentMarks",
     *      tags={"Exam"},
     *      summary="Get students exam marks",
     *      description="Returns students exam marks",
     *      security={ {"bearer": {} }},
     *      @OA\Parameter(
     *          name="id",
     *          description="Exam id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Calculated Exam Total Marks for all students",
     *          @OA\JsonContent(
     *              @OA\Property(property="exam", type="object", ref="#/components/schemas/ExamStudent")
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


    public function getExamAllStudentMarks($id)
    {
        $students = Student::all();

        foreach ($students as $s) {

            $answers = Answer::where(['exam_id' => $id, 'student_id' => $s->id])->get();

            $totalMark = 0;

            foreach ($answers as $a) {

                $m = McqAnswer::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
                if ($m != NULL && $m->isCorrect == 1) {

                    $ex = ExamQuestion::where('exam_id', '=', $id)->where('question_id', '=', $a->question_id)->first();

                    Answer::where(['exam_id' => $id, 'student_id' => $s->id, 'option_id' => $a['option_id'], 'question_id' => $a->question_id])->update(['questionMark' => $ex->mark]);

                    $totalMark += $ex->mark;
                }
            }

            if ($answers->count() != 0) {

                if (ExamStudent::where(['student_id' => $s->id, 'exam_id' => $id])->first() == NULL) {

                    $exst = ExamStudent::create([
                        'student_id' => $s->id,
                        'exam_id' => $id,
                        'totalMark' => $totalMark
                    ]);
                } else {

                    $exst = ExamStudent::where(['student_id' => $s->id, 'exam_id' => $id])->first();
                    $exst->update(['totalMark' => $totalMark]);
                }
            }
        }

        $res = ExamStudent::where(['exam_id' => $id])->get();

        $res->each(function ($e) {
            $e->student;
            $e->student->user;
            $e->student->department;
            $e->student->department->school;
            $e->exam;
        });

        //return response($res);

        return response()->json(['studentsMark' => $res, 'message' => 'successfully Calculated Exam Total Marks for all students']);
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
     *          description="successfully Calculated Exam Total Marks for all students",
     *          @OA\JsonContent(
     *              @OA\Property(property="exam", type="object", ref="#/components/schemas/ExamStudent")
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
    public function destroy(Exam $exam)
    {
        $user = auth()->user();
        if($user-> type != 'instructor') {
            return response()->json(['message' => 'Not authorized to delete exam']);
        }
        $exam->delete();
        return response()->json('Exam deleted successfully', 200);
        
    }

    public function getStudentAnswers(Exam $exam) {

    }

    
}
