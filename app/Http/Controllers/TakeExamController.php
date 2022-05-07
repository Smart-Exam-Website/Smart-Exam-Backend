<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\CheatingDetails;
use App\Models\Exam;
use App\Models\examSession;
use App\Models\ExamStudent;
use App\Models\CheatingAction;
use App\Models\FormulaQuestion;
use App\Models\FormulaStudent;
use DateTime;
use Illuminate\Http\Request;
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
            'numberOfFaces' => 'required',
            'isVerified' => 'required'
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
        if (!$exam->isPublished) {
            return response()->json(['message' => 'Exam not published yet!'], 400);
        }

        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id])->orderBy('attempt', 'DESC')->get()->first();

        if ($examSession) {
            if ($examSession->isCheater) {
                return response()->json(['message' => 'You cannot retake the exam, you are a cheater!'], 400);
            }
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
                    return response()->json(['message' => 'Exceeded number of attempts!'], 400);
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
            if ($question->type == 'group') {
                $question->questions->each(function ($question) {
                    $question->options;
                    $question->tags;
                }); 
            } else if ($question->type == 'formula') {
                $formulaQ = FormulaQuestion::where([
                    'question_id' => $question->id,
                ])->inRandomOrder()->get()->first();

                $question->questionText = $formulaQ->formulaText;

                $studentFormulaQ = FormulaStudent::where([
                    'student_id' => auth()->user()->id,
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                ])->get()->first();
                if (!$studentFormulaQ) {
                    $studentFormulaQ = FormulaStudent::create([
                        'student_id' => auth()->user()->id,
                        'formula_question_id' => $formulaQ->id,
                        'exam_id' => $exam->id,
                        'question_id' => $question->id
                    ]);
                    if (!$studentFormulaQ) {
                        return response()->json(['message' => 'Failed.'], 400);
                    }
                } else {
                    FormulaStudent::where([
                        'student_id' => auth()->user()->id,
                        'exam_id' => $exam->id,
                        'question_id' => $question->id,
                    ])->update([
                        'formula_question_id' => $formulaQ->id
                    ]);
                }
            } else {
                $question->options;
            }
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

        if ($examSession->isCheater) {
            //Cheating Actions
            $ch_details = CheatingDetails::orderBy('action_id')->where(['student_id' => $student->id, 'exam_id' => $exam->id])->where('action_id', '!=', 3)->get();
            if (count($ch_details) != 0 && examSession::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first()->isCheater) {
                $totalMark = 0;
                $cheatingDetails = CheatingDetails::where(['student_id' => $student->id, 'exam_id' => $exam->id])->whereNotNull('action_id')->get();
                foreach ($cheatingDetails as $c) {
                    $action = CheatingAction::where(['id' => $c->action_id])->first();
                    if ($action->name == "zero") {
                        $totalMark = 0;
                        break;
                    } else if ($action->name == "minus") {
                        $totalMark = $totalMark - $c->minusMarks;
                    }
                }
                if (ExamStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first() == NULL) {
                    ExamStudent::create([
                        'student_id' => $student->id,
                        'exam_id' => $exam->id,
                        'totalMark' => $totalMark
                    ]);
                } else {
                    $exst = ExamStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first();
                    $exst->update(['totalMark' => $totalMark]);
                }
            }
        }

        if ($examSession->isSubmitted) {
            return response()->json(['message' => 'Exam already submitted!'], 400);
        }


        $status = examSession::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'attempt' => $examSession->attempt])->update(['isSubmitted' => true, 'submittedAt' => now()]);

        // if a new attempt is submitted, we should remove all the old attempts and its answers, as they are now irrelevant.

        examSession::where(['exam_id' => $exam->id, 'student_id' => $student->id])->where('attempt', '!=', $examSession->attempt)->delete();
        Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id])->where('attempt', '!=', $examSession->attempt)->delete();


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
        if (!$examSession) {
            return response()->json(['message' => 'No exam session for this student!'], 400);
        }
        $startTime = new DateTime($examSession->startTime);
        [$hours, $minutes, $seconds] = explode(":", $duration);
        $startTime->modify('+' . $hours . ' hours ' . $minutes . ' minutes ' . $seconds . ' seconds');
        $answers = Answer::where(['student_id' => $studentId, 'exam_id' => $exam->id, 'attempt' => $examSession->attempt])->get();

        if (!$answers) {
            return response()->json(['message' => 'No answers found!'], 400);
        }

        return response()->json(['message' => 'Success!', 'answers' => $answers, 'endTime' => $startTime->format('Y-m-d H:i:s')]);
    }

    public function checkCheaterStatus(Exam $exam)
    {
        $user = auth()->user();
        if ($user->type != 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $examSession = examSession::where([
            'exam_id' => $exam->id,
            'student_id' => $user->id
        ])->orderBy('attempt', 'DESC')->get()->first();

        if (!$examSession) {
            return response()->json(['message' => 'No exam session found!'], 400);
        }
        $cheaterStatus = false;

        $cheatingDetails = CheatingDetails::where([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'action_id' => 1,
        ])->get();

        if (!$cheatingDetails) {
            $cheaterStatus = false;
        } else if ($cheatingDetails && $examSession->isCheater) {
            $cheaterStatus = true;
        }


        return response()->json(['message' => 'Cheater status fetched successfully!', 'cheaterStatus' => $cheaterStatus]);
    }
}
