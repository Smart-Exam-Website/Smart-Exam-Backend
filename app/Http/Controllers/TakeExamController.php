<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\examSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TakeExamController extends Controller
{
    // Start Exam
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
        if(!$exam->isPublished) {
            return response()->json(['message' => 'Exam not published yet!'], 400);
        }

        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->latest()->get()->first();
        if ($examSession) {
            if (!$examSession->isSubmitted) {
                return response()->json(['message' => 'You must submit the previous attempt first before starting a new attempt!'], 400);
            } else {
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
        $questions = $exam->questions;

        foreach ($questions as $question) {
            $question->options;
        }
        return response()->json(['questions' => $questions]);
    }


    // Submit Exam


    public function submitExam(Exam $exam)
    {

        $student = auth()->user();

        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => $student->id])->orderBy('attempt', 'DESC')->get()->first();
        if (!$examSession) {
            return response()->json(['message' => 'No exam session for this student!'], 400);
        }

        if($examSession->isCheater) {
            return response()->json(['message' => 'Cheater! You cannot submit the exam!'], 400);
        }

        if ($examSession->isSubmitted) {
            return response()->json(['message' => 'Exam already submitted!'], 400);
        }

        // check number of questions of exam
        $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();
        // check how many questions the student answered!
        $answers = DB::table('answers')->where(['exam_id' => $exam->id, 'student_id' => $student->id])->get();
        // cannot submit until they are all answered!
        // if ($answers->count() < $questions->count()) {
        //     return response()->json(['message' => 'You cannot submit yet!, you haven\'t answered all questions'], 400);
        // }

        $status = DB::table('examSession')->where(['exam_id' => $exam->id, 'student_id' => $student->id, 'attempt' => $examSession->attempt])->update(['isSubmitted' => true, 'submittedAt' => now()]);

        if ($status) {
            return response()->json(['message' => 'Submitted exam successfully!']);
        } else {
            return response()->json(['message' => 'Failed to submit exam!'], 400);
        }

    }

    // Get student Answers 


    public function showStudentAnswers(Exam $exam)
    {
        $duration = $exam->duration;
        $student = auth()->user();
        if ($student->type != 'student') {
            return response()->json(['message' => 'Not a student!'], 400);
        }
        $studentId = $student->id;
        $examSession = examSession::where(['student_id' => $studentId, 'exam_id' => $exam->id])->orderBy('attempt', 'DESC')->get()->first();
        if(!$examSession) {
            return response()->json(['message' => 'No exam session for this student!'], 400);
        }
        $startTime = $examSession->startTime;
        $currentTime = now();
        $startTime = strtotime($startTime);
        $currentTime = strtotime($currentTime);
        $difference = $currentTime - $startTime;
        $duration = strtotime($duration);
        $difference = $duration - $difference;
        $timeLeft = date('H:i:s', $difference);
        $answers = Answer::where(['student_id' => $studentId, 'exam_id' => $exam->id])->get();

        if (!$answers) {
            return response()->json(['message' => 'No answers found!'], 400);
        }

        return response()->json(['message' => 'Success!', 'answers' => $answers, 'timeLeft' => $timeLeft]);
    }




}

