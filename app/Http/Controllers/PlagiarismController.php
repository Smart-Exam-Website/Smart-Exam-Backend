<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PlagiarismController extends Controller
{
    public function plagiarism(Request $request)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'There is no logged in instructor!'], 400);
        }

        $rules = [
            'examId' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error validating request body'], 400);
        }

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'This exam does not exist!'], 400);
        }

        $config = $exam->config;

        if (!$config->plagiarismCheck) {
            return response()->json(['message' => 'This exam does not support plagiarism check!'], 400);
        }

        $exqs = ExamQuestion::where(['exam_id' => $exam->id])->get();
        $essayqs = [];
        foreach ($exqs as $ex) {
            if ($ex->question->type == "essay") {
                array_push($essayqs, $ex->question_id);
            }
        }

        // loop over all essay questions on the exam

        foreach ($essayqs as $qid) {

            $q = Question::where(['id' => $qid])->get()->first();

            if (!$q) {
                return response()->json(['message' => 'This question does not Exist!'], 400);
            }

            $allanswers = Answer::where(['question_id' => $qid, 'exam_id' => $request->examId])->get();

            $list = ['correctAnswer' => $allanswers[0]->question->options[0]->value];
            foreach ($allanswers as $a) {
                array_push($list, [$a->student_id => $a->studentAnswer]);
            }
            //return $list;
            $response = Http::post('http://13.59.36.254/m1/plagiarism', [
                'list' => $list,
            ]);

            if ($response->ok()) {
                if ($response->status() != 200) {
                    return response()->json(['message' => 'Failed to send list!'], 400);
                } else {
                    $res = $response->object();
                    return response()->json(['message' => 'Plagiarism check done successfully!', 'res' => $res]);
                }
            } else {
                return response()->json(['message' => 'An error occurred!'], 400);
            }
        }
    }
}
