<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\McqAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamStudent;
use App\Models\Student;
use Illuminate\Http\Request;

class MarkMCQController extends Controller
{
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

            $answer = Answer::where(['student_id' => $fields['student_id'], 'exam_id' => $fields['exam_id'], 'question_id' => $fields['question_id']])->get();
            $cnt = $answer->count();
            if ($cnt == 0) {
                $a = Answer::create([
                    'student_id' => $fields['student_id'],
                    'exam_id' => $fields['exam_id'],
                    'question_id' => $fields['question_id'],
                    'questionMark' => $fields['questionMark'],
                ]);
            } else {
                $a = $answer->first();
                $a['questionMark'] = $fields['questionMark'];
            }
        } else {
            return response()->json(['message' => 'There is no logged in Instructor'], 400);
        }
        return response()->json(['message' => 'The Mark is Saved Successfully', 'answer' => $a], 200);
    }

    public function MarkOneStudentExam(Exam $exam, Student $s)
    {
        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }

        $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id])->get();

        $totalMark = 0;

        foreach ($answers as $a) {

            $m = McqAnswer::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
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

        $res = ExamStudent::where(['exam_id' => $exam->id, 'student_id' => $s->id])->first();

        return response()->json(['studentMark' => $res->totalMark, 'message' => 'successfully Calculated Exam Total Marks for This Student']);
    }

    public function MarkAllStudentsExam(Exam $exam)
    {
        if (date('Y-m-d H:i:s') <= $exam->endAt) {
            return response()->json(['message' => 'Cannot mark exam yet!'], 400);
        }
        $students = Student::all();

        foreach ($students as $s) {

            $answers = Answer::where(['exam_id' => $exam->id, 'student_id' => $s->id])->get();

            $totalMark = 0;

            foreach ($answers as $a) {

                $m = McqAnswer::where(['id' => $a['option_id'], 'question_id' => $a['question_id']])->first();
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
            $report = Answer::where(['student_id' => $user->id, 'exam_id' => $exam->id])->select('question_id', 'option_id', 'questionMark')->get();

            $report->each(function ($e) {
                $e->option;
                $e->question->mcqAnswer;
            });

            return response()->json(['report' => $report]);
        } else {
            return response()->json(['message' => 'There is no logged in Student'], 400);
        }
    }
}
