<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamQuestion;
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
            'option_id' => 'numeric|exists:options,id',
            'question_id' => 'required|numeric|exists:questions,id',
            'exam_id' => 'required|numeric|exists:exams,id',
            'studentAnswer' => 'string'
        ];

        // check if question was already answered before..

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'the data is invalid'], 422);
        }

        $exam = Exam::where(['id' => $request->exam_id])->first();
        if(!$exam) {
            return response()->json(['message' => 'No exam found with this id!'], 404);
        }

        $answerDetails = $request->only(['option_id', 'question_id', 'exam_id', 'studentAnswer']);
        $studentId = auth()->user()->id;
        $answerDetails['student_id'] = $studentId;

        // get exam session first.
        $examSession = examSession::where(['exam_id' => $request->exam_id, 'student_id' => $studentId])->orderBy('attempt', 'DESC')->get()->first();
        if (!$examSession) {
            return response()->json(['message' => 'Could not find a session for this student!'], 404);
        }
        $answerDetails['attempt'] = $examSession->attempt;
        $answer = Answer::where(['exam_id' => $request->exam_id, 'student_id' => $studentId, 'question_id' => $request->question_id, 'attempt' => $examSession->attempt])->get()->first();
        $question = Question::where(['id' => $request->question_id])->get()->first();
        $examQuestions = ExamQuestion::where(['exam_id' => $request->exam_id])->get();
        $found = false;
        foreach ($examQuestions as $examQ) {
            $q = Question::where(['id' => $examQ->question_id])->get()->first();
            if($q->type == 'group') {
                $groupQs = $q->questions;
                // dd($groupQs);
                foreach($groupQs as $gQ) {
                    if($gQ->id == $question->id) {
                        $found = true;
                        break;
                    }
                }

            } else {
                if($q->id == $question->id) {
                    $found = true;
                    break;
                }
            }
        }
        if(!$found) {
            return response()->json(['message' => 'Question not related to exam!'], 422);
        }
        if (!$request->option_id) {
            if($question->type != 'formula') {
                $option = Option::where('question_id', $question->id)->get()->first();
                $answerDetails['option_id'] = $option->id;
            } else {
                $answerDetails['option_id'] = null;
            }
        } else {
            $option = Option::where(['question_id' => $question->id, 'id' => $request->option_id])->get()->first();
            if (!$option) {
                return response()->json(['message' => 'Wrong option id!'], 400);
            }
        }
        if(!$request->studentAnswer) {
            $answerDetails['studentAnswer'] = "";
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
