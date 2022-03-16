<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Exam;
use App\Models\examSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    // Get all published exams api
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
                $examMark = DB::table('exam_students')->where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->get()->first();
                if ($examMark) {
                    $exam['isMarked'] = true;
                    $exam['mark'] = $examMark;
                } else {
                    $exam['isMarked'] = false;
                }
            }
            $isMarked = request('isMarked');
            if ($isMarked) {
                $filteredArray = array_filter($finalExams, function ($exam) use ($isMarked) {
                    $val = $exam['isMarked']? 'true': 'false';
                    return $val == $isMarked;
                });

                $finalExams = $filteredArray;
            }
        }
        return $finalExams;
    }



    // Create exam -- Step One
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
    // Create exam -- step two
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
    // Create exam -- Step Three
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
    // Create exam -- Step four
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

    // Show exam details

    
    public function show(Exam $exam)
    {
        return response()->json(['exam' => $exam]);
    }


    // Show exam questions
    public function showExamQuestions(Exam $exam)
    {

        $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();

        foreach ($questions as $question) {

            $answers = DB::table('question_option')->where('question_id', $question->id)->join('options', 'options.id', 'question_option.id')->select(['options.id', 'question_option.isCorrect', 'options.value'])->get();
            $question->answers = $answers;
        }
        return response()->json(['questions' => $questions]);
    }



    

    // get exam configurations
    public function showExamConfigurations(Exam $exam)
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

    // Edit exam -- Step One
    public function updateStepOne(Request $request, Exam $exam)
    {
        if (!$exam) {
            return response()->json(['message' => 'Exam not found!']);
        }
        if (auth()->user()->type != 'instructor' || auth()->user()->id != $exam->instructor_id) {
            return response()->json(['message' => 'Unauthorized to update exam!'], 403);
        }
        if ($exam->startAt < now()) {
            return response()->json(['message' => 'Cannot edit exam after it has started!'], 400);
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

    // Edit Exam -- Step Two
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

    // Edit Exam -- Step Three
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

    // Edit Exam -- Step Four
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

    // Delete Exam

    public function destroy(Exam $exam)
    {
        $user = auth()->user();
        if ($user->type != 'instructor' || $user->id != $exam->instructor_id) {
            return response()->json(['message' => 'Not authorized to delete exam']);
        }
        $exam->delete();
        return response()->json('Exam deleted successfully', 200);
    }
    // Publish Exam

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
}
