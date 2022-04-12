<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\examSession;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    // Store Student's Answer
    public function store(Request $request)
    {
        $rules = [
            'option_id' => 'numeric',
            'question_id' => 'required|numeric',
            'exam_id' => 'required|numeric',
            'studentAnswer' => 'string'
        ];

        // check if question was already answered before..

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'failed to add answer'], 422);
        }

        $answerDetails = $request->only(['option_id', 'question_id', 'exam_id', 'studentAnswer']);
        $studentId = auth()->user()->id;
        $answerDetails['student_id'] = $studentId;

        // get exam session first.
        $examSession = examSession::where(['exam_id' => $request->exam_id, 'student_id' => $studentId])->orderBy('attempt', 'DESC')->get()->first();
        if(!$examSession) {
            return response()->json(['message' => 'Could not find a session for this student!'], 404);
        }
        $answerDetails['attempt'] = $examSession->attempt;
        $answer = Answer::where(['exam_id' => $request->exam_id, 'student_id' => $studentId, 'question_id' => $request->question_id, 'attempt' => $examSession->attempt])->get()->first();
        $question = Question::where(['id' => $request->question_id])->get()->first();
        if(!$request->option_id) {
            $option = Option::where('question_id', $question->id)->get()->first();
            $answerDetails['option_id'] = $option->id;
        } else {
            $option = Option::where(['question_id' => $question->id, 'id' => $request->option_id])->get()->first();
            if(!$option) {
                return response()->json(['message' => 'Wrong option id!'], 400);
            }
        }
        if ($answer) {
            if ($answer->option_id != $answerDetails['option_id'] || $answer->studentAnswer != $answerDetails['studentAnswer']) {
                Answer::where(['exam_id' => $request->exam_id, 'student_id' => $studentId, 'question_id' => $request->question_id, 'attempt' => $examSession->attempt])->update($answerDetails);
            } else {
                return response()->json(['message' => 'data stored successfully!']);
            }
            // $answer->update($answerDetails);
        } else {
            $answer = Answer::create($answerDetails);
            if (!$answer) {
                return response()->json(['message' => 'failed to add answer'], 400);
            }
        }



        return response()->json(['message' => 'data stored successfully']);
    }
}
