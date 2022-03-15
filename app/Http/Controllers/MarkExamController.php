<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\Configuration;
use App\Models\ExamQuestion;
use App\Models\ExamStudent;
use App\Models\QuestionOption;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MarkExamController extends Controller
{

    
    // Get all Exam Answers


    public function showExamAnswers(Exam $exam)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }
        // get list of all students who solved the exam
        $solvedExams = DB::table('examSession')->where(['exam_id' => $exam->id, 'isSubmitted' => true])->get();
        if (!$solvedExams) {
            return response()->json(['message' => 'No solutions found for this exam!'], 400);
        }
        foreach ($solvedExams as $solvedExam) {
            $user = DB::table('users')->where('id', $solvedExam->student_id)->get()->first();
            $student = DB::table('students')->where(['id' => $solvedExam->student_id])->get()->first();
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
        $user = DB::table('users')->where(['id' => $studentId])->get()->first();
        $studentName = $user->firstName . ' ' . $user->lastName;
        $studentImage = $user->image;
        // $studentCode = $user->student->studentCode;

        $session = DB::table('examSession')->where(['exam_id' => $exam->id, 'student_id' => $studentId, 'isSubmitted' => true])->get()->first();
        if (!$session) {
            return response()->json(['message' => 'No session found for this student!'], 400);
        }

        $solutions = DB::table('answers')->where(['exam_id' => $exam->id, 'student_id' => $studentId])->get();

        if (!$solutions) {
            return response()->json(['message' => 'Failed to fetch student solutions!'], 400);
        }



        foreach ($solutions as $solution) {
            $solution->question = DB::table('questions')->where(['id' => $solution->question_id])->get()->first();
            $solution->totalQuestionMark = DB::table('exam_question')->where(['exam_id' => $exam->id, 'question_id' => $solution->question_id])->get()->first()->mark;
            if ($solution->question->type == 'mcq') {
                $answers = DB::table('question_option')->where(['question_id' => $solution->question->id])->join('options', 'options.id', 'question_option.id')->get();
                // $questions = DB::table('exam_question')->where('exam_id', $exam->id)->join('questions', 'question_id', 'questions.id')->select(['questions.id', 'questions.questionText', 'exam_question.mark', 'questions.type'])->get();

                $solution->question->answers = $answers;
            }
        }

        $examConfig = DB::table('configs')->where(['exam_id' => $exam->id])->get()->first();


        $numberOfFaces = ($examConfig->faceDetection) ? $session->numberOfFaces : null;
        $isVerified = ($examConfig->faceDetection) ? $session->isVerified : null;

        return response()->json(['message' => 'Fetched solution successfully', 'studentName' => $studentName, 'image' => $studentImage, 'solution' => $solutions, 'numberOfFaces' => $numberOfFaces, 'isVerified' => $isVerified]);
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

            if (ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first() != NULL) {
                $exst = ExamStudent::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId']])->first();
                $totalMark = $exst->totalMark;
            } else {
                $totalMark = 0;
            }

            $answer = Answer::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId']])->get();

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
                DB::table('answers')->where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId']])->update(['questionMark' => $fields['questionMark'], 'isMarked' => true]);
                $a = Answer::where(['student_id' => $fields['studentId'], 'exam_id' => $fields['examId'], 'question_id' => $fields['questionId']])->first();
            }
        } else {
            return response()->json(['message' => 'There is no logged in Instructor'], 400);
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

        return response()->json(['message' => 'The Mark is Saved Successfully', 'answer' => $a, 'totalStudentMark' => $totalMark], 200);
    }

    public function MarkOneStudentExam(Exam $exam, Student $student)
    {
        //Automatic
        $gradMethod = Configuration::where(['exam_id' => $exam->id])->first()->gradingMethod;
        if ($gradMethod == "manual") {
            return response()->json(['message' => 'This Exam Can Only Be Marked Manually!'], 400);
        }

        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }

        $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id])->get();

        $totalMark = 0;

        foreach ($answers as $a) {

            $m = QuestionOption::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
            if ($m != NULL && $m->isCorrect == 1) {

                $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();

                Answer::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'option_id' => $a['option_id'], 'question_id' => $a->question_id])->update(['questionMark' => $ex->mark]);

                $totalMark += $ex->mark;
            }
        }

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
        $gradMethod = Configuration::where(['exam_id' => $exam->id])->first()->gradingMethod;
        if ($gradMethod == "manual") {
            return response()->json(['message' => 'This Exam Can Only Be Marked Manually!'], 400);
        }
        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }
        $students = Student::all();

        foreach ($students as $s) {

            $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id])->get();

            $totalMark = 0;

            foreach ($answers as $a) {

                $m = QuestionOption::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
                if ($m != NULL && $m->isCorrect == 1) {

                    $ex = ExamQuestion::where('exam_id', '=', $exam->id)->where('question_id', '=', $a->question_id)->first();

                    Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id, 'option_id' => $a['option_id'], 'question_id' => $a->question_id])->update(['questionMark' => $ex->mark]);

                    $totalMark += $ex->mark;
                }
            }

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
            $session = DB::table('examSession')->where(['exam_id' => $exam->id, 'student_id' => $user->id, 'isSubmitted' => true])->get()->first();
            if (!$session) {
                return response()->json(['message' => 'You Must Take Exam First!'], 400);
            }

            $solutions = DB::table('answers')->where(['exam_id' => $exam->id, 'student_id' => $user->id])->get();
            if (!$solutions) {
                return response()->json(['message' => 'Failed to fetch your solutions!'], 400);
            }

            foreach ($solutions as $s) {
                $s->question = DB::table('questions')->where(['id' => $s->question_id])->get()->first();
                $s->totalQuestionMark = DB::table('exam_question')->where(['exam_id' => $exam->id, 'question_id' => $s->question_id])->get()->first()->mark;
                $answers = DB::table('question_option')->where(['question_id' => $s->question->id])->join('options', 'options.id', 'question_option.id')->get();
                $s->question->answers = $answers;
            }

            return response()->json(['message' => 'Report Generated successfully', 'solution' => $solutions]);
        } else {
            return response()->json(['message' => 'There is no logged in Student'], 400);
        }
    }
}
