<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            return response()->json(['message' => 'failed to add answer'], 400);
        }

        $answerDetails = $request->only(['option_id', 'question_id', 'exam_id', 'studentAnswer']);



        $answerDetails['student_id'] = auth()->user()->id;

        $answer = Answer::where(['exam_id' => $request->exam_id, 'student_id' => auth()->user()->id, 'question_id' => $request->question_id])->get()->first();
        $question = DB::table('questions')->where(['id' => $request->question_id])->get()->first();
        if(!$request->option_id) {
            $options = $question->QuestionOption;
            $option = $options[0]->option;
            $answerDetails['option_id'] = $option->id;
        }
        if ($answer) {
            if ($answer->option_id != $answerDetails['option_id'] || $answer->studentAnswer != $answerDetails['studentAnswer']) {
                DB::table('answers')->where(['exam_id' => $request->exam_id, 'student_id' => auth()->user()->id, 'question_id' => $request->question_id])->update(['option_id' => $answerDetails['option_id'], 'studentAnswer' => $answerDetails['studentAnswer']]);
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
