<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\Option;
use App\Models\Configuration;
use App\Models\examSession;
use App\Models\ExamQuestion;
use App\Models\ExamStudent;
use App\Models\FormulaQuestion;
use App\Models\FormulaStudent;
use App\Models\Question;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MarkExamController extends Controller
{


    // Get all Exam Answers


    public function showExamAnswers(Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }
        // get list of all students who solved the exam
        $solvedExams = examSession::where(['exam_id' => $exam->id, 'isSubmitted' => true])->orderBy('attempt', 'DESC')->get()->unique('student_id');
        if (!$solvedExams) {
            return response()->json(['message' => 'No solutions found for this exam!'], 400);
        }
        foreach ($solvedExams as $solvedExam) {
            $user = User::where('id', $solvedExam->student_id)->get()->first();
            $student = Student::where(['id' => $solvedExam->student_id])->get()->first();
            $solvedExam->name = $user->firstName . ' ' . $user->lastName;
            $solvedExam->studentCode = $student->studentCode;
            $solvedExam->image = $user->image;
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


    // get Detailed Exam Answer


    public function showDetailedExamAnswer(Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }

        $studentId = request('student_id');
        if (!$studentId) {
            return response()->json(['message' => 'No student ID specified!']);
        }
        $user = User::where(['id' => $studentId])->get()->first();
        $studentName = $user->firstName . ' ' . $user->lastName;
        $studentImage = $user->image;
        // $studentCode = $user->student->studentCode;

        $session = examSession::where(['exam_id' => $exam->id, 'student_id' => $studentId, 'isSubmitted' => true])->orderBy('attempt', 'DESC')->get()->first();
        if (!$session) {
            return response()->json(['message' => 'No session found for this student!'], 400);
        }

        $questions = $exam->questions;

        foreach ($questions as $q) {
            $q->instructor->user;
            $q->tags;
            $q->options;
            if ($q->type == "group") {
                foreach ($q->questions as $e) {
                    $e->tags;
                    $e->options;
                    $answer = Answer::where(['student_id' => $user->id, 'question_id' => $e->id, 'exam_id' => $exam->id, 'attempt' => $session->attempt])->get()->first();
                    $e['answers'] = $answer;
                };
            } else if ($q->type == "formula") {
                $formula_ques = FormulaStudent::where(['student_id' => $user->id, 'exam_id' => $exam->id, 'question_id' => $q->id])->get()->first()->formula_question_id;
                $q->formula_questions = FormulaQuestion::where(['id' => $formula_ques])->get()->first();
                $q->formula;
                $q->variables;
            }
            if (!($q->type == "group")) {
                $answer = Answer::where(['student_id' => $studentId, 'question_id' => $q->id, 'exam_id' => $exam->id, 'attempt' => $session->attempt])->get()->first();
                $q['answer'] = $answer;
            }
        }

        $examConfig = Configuration::where(['exam_id' => $exam->id])->get()->first();


        $numberOfFaces = ($examConfig->faceDetection) ? $session->numberOfFaces : null;
        $isVerified = ($examConfig->faceDetection) ? $session->isVerified : null;

        return response()->json(['message' => 'Fetched solution successfully', 'studentName' => $studentName, 'image' => $studentImage, 'solution' => $questions, 'numberOfFaces' => $numberOfFaces, 'isVerified' => $isVerified]);
    }




    public function MarkExamManual(Request $request)
    {
        $user = auth()->user();

        if ($user->type == 'instructor') {

            $fields = $request->validate([
                'studentId' => 'required',
                'examId' => 'required',
                'questionId' => 'required',
                'questionMark' => 'required'
            ]);

            $examSession = examSession::where(['exam_id' => $request->examId, 'student_id' => $request->studentId])->orderBy('attempt', 'DESC')->get()->first();
            if (!$examSession) {
                return response()->json(['message' => 'No exam session found for this student'], 422);
            }
            $exam = Exam::where(['id' => $fields['examId']])->first();
            if (!$exam) {
                return response()->json(['message' => 'No exam with this id!'], 404);
            }

            if (date('Y-m-d H:i:s') <= $exam->endAt) {
                return response()->json(['message' => 'Cannot mark exam yet!'], 400);
            }

            if (ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first() != NULL) {
                $exst = ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first();
                $totalMark = $exst->totalMark;
            } else {
                $totalMark = 0;
            }

            $answer = Answer::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId'], 'attempt' => $examSession->attempt])->get();

            $cnt = $answer->count();
            if ($cnt == 0) {
                $a = Answer::create([
                    'student_id' => $fields['studentId'],
                    'exam_id' => $fields['examId'],
                    'question_id' => $fields['questionId'],
                    'questionMark' => $fields['questionMark'],
                    'isMarked' => true
                ]);
            } else {
                $ans = $answer->first();
                $qMark = $ans->questionMark;
                $totalMark = $totalMark - $qMark;
                Answer::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId']])->update(['questionMark' => $fields['questionMark'], 'isMarked' => true]);
                $a = Answer::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId']])->first();
            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first() == NULL) {

            $exst = ExamStudent::create([
                'student_id' => $fields['studentId'],
                'exam_id' => $fields['examId'],
                'totalMark' => $fields['questionMark']
            ]);
            $totalMark = $fields['questionMark'];
        } else {
            $exst = ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first();
            $totalMark = $totalMark + $fields['questionMark'];
            $exst->update(['totalMark' => $totalMark]);
        }

        return response()->json(['message' => 'Student mark saved successfully!', 'answer' => $a, 'totalStudentMark' => $totalMark], 200);
    }




    public function MarkOneStudentExam(Exam $exam, Student $student)
    {
        $user = auth()->user();
        $student_id = $student->id;
        //Automatic
        if ($user->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => $student->id])->orderBy('attempt', 'DESC')->get()->first();
        if (!$examSession) {
            return response()->json(['message' => 'No exam session found for this student'], 422);
        }


        $gradMethod = Configuration::where(['exam_id' => $exam->id])->first()->gradingMethod;
        if ($gradMethod == "manual") {
            return response()->json(['message' => 'This exam can only be marked manually!'], 400);
        }

        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }

        $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'attempt' => $examSession->attempt])->get();

        $mcqs = [];
        $essays = [];
        $formulas = [];
        $groups = [];

        foreach ($answers as $ans) {
            $ans->question;
            if (($ans->question->type == "mcq") && ($ans->isMarked == false)) {
                array_push($mcqs, $ans);
            } else if (($ans->question->type == "essay") && ($ans->isMarked == false)) {
                array_push($essays, $ans);
            } else if (($ans->question->type == "formula") && ($ans->isMarked == false)) {
                $formula_ques = FormulaStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id, 'question_id' => $ans->question->id])->get()->first()->formula_question_id;
                $ans->question->formula_questions = FormulaQuestion::where(['id' => $formula_ques])->get()->first();
                array_push($formulas, $ans);
            } else if (($ans->question->type == "group") && ($ans->isMarked == false)) {
                array_push($groups, [$ans->question->id => $ans->question->questions]);
            }
        }

        $exst = ExamStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first();
        $totalMark = $exst ? $exst->totalMark : 0;

        //for mcq Automatic Marking
        foreach ($mcqs as $a) {
            $m = Option::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
            if ($m != NULL && $m->isCorrect == 1) {

                $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();

                Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'option_id' => $a['option_id'], 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => $ex->mark, 'isMarked' => true]);

                $totalMark += $ex->mark;
            } else {
                Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => 0, 'isMarked' => true]);
            }
        }

        //for essay Automatic Marking
        $list = [];

        foreach ($essays as $essay) {

            $correctAnswer = $essay->question->options[0]->value;
            $studentAnswer = $essay->studentAnswer;

            $list[0] = $correctAnswer;
            $list[intval($essay->student_id)] = $studentAnswer;

            $response = Http::post('http://ec2-3-239-150-58.compute-1.amazonaws.com/grading/predict', [
                'students_dict' => $list,
            ]);


            if ($response->ok()) {
                if ($response->status() != 200) {
                    return response()->json(['message' => 'Failed to send Answers!'], 400);
                } else {
                    $percent = $response->object()->grades->$student_id;
                    $ex = ExamQuestion::where(['question_id' => $essay->question_id, 'exam_id' => $exam->id])->get()->first();
                    $totalquestionMark = $ex->mark;
                    $student_Mark = (float)$percent  * $totalquestionMark;
                    DB::table('answers')->where(['student_id' => $student->id, 'exam_id' => $exam->id, 'question_id' => $essay->question_id])->update(['questionMark' => $student_Mark, 'isMarked' => true]);
                    $totalMark += $student_Mark;
                }
            } else {
                return response()->json(['message' => 'An error occurred!'], 400);
            }
        }

        // For formula marking

        foreach ($formulas as $a) {
            if ($a->studentAnswer == $a->question->formula_questions->value) {
                $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();
                Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => $ex->mark, 'isMarked' => true]);
                $totalMark += $ex->mark;
            } else {
                Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => 0, 'isMarked' => true]);
            }
        }

        // For Question Group

        foreach ($groups as $g) {
            $all_questions = 0;
            foreach ($g as $q) {
                foreach ($q as $e) {
                    $all_questions += Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $e->id, 'attempt' => $examSession->attempt])->get()->first()->questionMark;
                };
            }
            Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => array_keys($g)[0], 'attempt' => $examSession->attempt])->update(['questionMark' => $all_questions, 'isMarked' => true]);
        }

        //Final Saving for the total Mark of the student

        if ($answers->count() != 0) {

            if (ExamStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first() == NULL) {

                $exst = ExamStudent::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'totalMark' => $totalMark
                ]);
            } else {

                $exst = ExamStudent::where(['student_id' => $student->id, 'exam_id' => $exam->id])->first();
                $exst->update(['totalMark' => $totalMark]);
            }
        }


        $res = ExamStudent::where(['exam_id' => $exam->id, 'student_id' => $student->id])->first();

        return response()->json(['studentMark' => $res->totalMark, 'message' => 'successfully Calculated Exam Total Marks for This Student']);
    }

    public function MarkAllStudentsExam(Exam $exam)
    {
        //Automatic
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'There is no Logged in Instructor!'], 400);
        }

        $gradMethod = Configuration::where(['exam_id' => $exam->id])->first()->gradingMethod;
        if ($gradMethod == "manual") {
            return response()->json(['message' => 'This Exam Can Only Be Marked Manually!'], 400);
        }
        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }

        $students = [];
        $es = ExamSession::where(['exam_id' => $exam->id])->get();
        foreach ($es as $e) {
            array_push($students, Student::find($e->student_id));
        };

        foreach ($students as $s) {
            $examSession = examSession::where(['exam_id' => $exam->id, 'student_id' => $s->id])->orderBy('attempt', 'DESC')->get()->first();
            if (!$examSession) {
                return response()->json(['message' => 'No exam session found for this student'], 422);
            }

            $student_id = $s->id;
            $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'attempt' => $examSession->attempt])->get();

            $mcqs = [];
            $essays = [];
            $formulas = [];
            $groups = [];

            foreach ($answers as $ans) {
                $ans->question;
                if (($ans->question->type == "mcq") && ($ans->isMarked == false)) {
                    array_push($mcqs, $ans);
                } else if (($ans->question->type == "essay") && ($ans->isMarked == false)) {
                    array_push($essays, $ans);
                } else if (($ans->question->type == "formula") && ($ans->isMarked == false)) {
                    $formula_ques = FormulaStudent::where(['student_id' => $s->id, 'exam_id' => $exam->id, 'question_id' => $ans->question->id])->get()->first()->formula_question_id;
                    $ans->question->formula_questions = FormulaQuestion::where(['id' => $formula_ques])->get()->first();
                    array_push($formulas, $ans);
                } else if (($ans->question->type == "group") && ($ans->isMarked == false)) {
                    array_push($groups, [$ans->question->id => $ans->question->questions]);
                }
            }

            $exst = ExamStudent::where(['student_id' => $s->id, 'exam_id' => $exam->id])->first();
            $totalMark = $exst ? $exst->totalMark : 0;
            //for mcq Automatic Marking
            foreach ($mcqs as $a) {

                $m = Option::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
                if ($m != NULL && $m->isCorrect == 1) {

                    $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();

                    Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'option_id' => $a['option_id'], 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => $ex->mark, 'isMarked' => true]);

                    $totalMark += $ex->mark;
                } else {
                    Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => 0, 'isMarked' => true]);
                }
            }

            //for essay Automatic Marking
            $list = [];
            foreach ($essays as $essay) {

                $correctAnswer = $essay->question->options[0]->value;
                $studentAnswer = $essay->studentAnswer;

                $list[0] = $correctAnswer;
                $list[intval($essay->student_id)] = $studentAnswer;

                $response = Http::post('http://ec2-3-239-150-58.compute-1.amazonaws.com/grading/predict', [
                    'students_dict' => $list,
                ]);


                if ($response->ok()) {
                    if ($response->status() != 200) {
                        return response()->json(['message' => 'Failed to send Answers!'], 400);
                    } else {
                        $percent = $response->object()->grades->$student_id;
                        $ex = ExamQuestion::where(['question_id' => $essay->question_id, 'exam_id' => $exam->id])->get()->first();
                        $totalquestionMark = $ex->mark;
                        $student_Mark = (float)$percent  * $totalquestionMark;
                        DB::table('answers')->where(['student_id' => $s->id, 'exam_id' => $exam->id, 'question_id' => $essay->question_id])->update(['questionMark' => $student_Mark, 'isMarked' => true]);
                        $totalMark += $student_Mark;
                    }
                } else {
                    return response()->json(['message' => 'An error occurred!'], 400);
                }
            }

            // For formula marking

            foreach ($formulas as $a) {
                if ($a->studentAnswer == $a->question->formula_questions->value) {
                    $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();
                    Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => $ex->mark, 'isMarked' => true]);
                    $totalMark += $ex->mark;
                } else {
                    Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'question_id' => $a->question_id, 'attempt' => $examSession->attempt])->update(['questionMark' => 0, 'isMarked' => true]);
                }
            }

            // For Question Group

            foreach ($groups as $g) {
                $all_questions = 0;
                foreach ($g as $q) {
                    foreach ($q as $e) {
                        $all_questions += Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'question_id' => $e->id, 'attempt' => $examSession->attempt])->get()->first()->questionMark;
                    };
                }
                Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'question_id' => array_keys($g)[0], 'attempt' => $examSession->attempt])->update(['questionMark' => $all_questions, 'isMarked' => true]);
            }

            //Final Saving for the total Mark of the student

            if ($answers->count() != 0) {

                if (ExamStudent::where(['student_id' => $s->id, 'exam_id' => $exam->id])->first() == NULL) {

                    $exst = ExamStudent::create([
                        'student_id' => $s->id,
                        'exam_id' => $exam->id,
                        'totalMark' => $totalMark
                    ]);
                } else {

                    $exst = ExamStudent::where(['student_id' => $s->id, 'exam_id' => $exam->id])->first();
                    $exst->update(['totalMark' => $totalMark]);
                }
            }
        }

        $res = ExamStudent::where(['exam_id' => $exam->id])->select('student_id', 'totalMark')->get();

        $res->each(function ($e) {
            $e->student;
            $e->student->user;
        });

        return response()->json(['studentsMark' => $res, 'message' => 'successfully Calculated Exam Total Marks for all students']);
    }

    public function ExamReportForStudent(Exam $exam)
    {
        $user = auth()->user();
        if ($user->type == 'student') {
            $session = examSession::where(['exam_id' => $exam->id, 'student_id' => $user->id, 'isSubmitted' => true])->get()->first();
            if (!$session) {
                return response()->json(['message' => 'There is no exam session for this student'], 404);
            }

            $solutions = Answer::where(['exam_id' => $exam->id, 'student_id' => $user->id, 'attempt' => $session->attempt])->get();
            if (!$solutions) {
                return response()->json(['message' => 'Failed to fetch your solutions!'], 422);
            }

            foreach ($solutions as $s) {
                $s->question = Question::where(['id' => $s->question_id])->get()->first();
                $s->totalQuestionMark = DB::table('exam_question')->where(['exam_id' => $exam->id, 'question_id' => $s->question_id])->get()->first()->mark;
                $answers = Option::where(['question_id' => $s->question->id])->get();
                $s->question->answers = $answers;
                if ($s->question->type == "group") {
                    $s->question->questions->each(function ($e) {
                        $answers = Option::where(['question_id' => $e->id])->get();
                        $e->answers = $answers;
                    });
                } else if ($s->question->type == "formula") {
                    $formula_ques = FormulaStudent::where(['student_id' => $user->id, 'exam_id' => $exam->id, 'question_id' => $s->question_id])->get()->first()->formula_question_id;
                    $s->question->formula_questions = FormulaQuestion::where(['id' => $formula_ques])->get()->first();
                    $s->question->formula;
                    $s->question->variables;
                }
            }

            return response()->json(['message' => 'Report generated successfully', 'solution' => $solutions]);
        } else {
            return response()->json(['message' => 'There is no logged in Student'], 400);
        }
    }
}
