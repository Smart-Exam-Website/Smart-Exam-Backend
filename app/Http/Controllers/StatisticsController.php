<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\User;
use App\Models\ExamStudent;

class StatisticsController extends Controller
{
    public function index(Exam $exam)
    {
        $instructor = User::where(['id' => $exam->instructor_id])->get()->first();
        $error = "";
        if (!$exam) {
            $error = "No exam found with this id!";
        }
        $questions = $exam->questions;

        $markedExams = ExamStudent::where(['exam_id' => $exam->id])->get();
        if (!$markedExams->count()) {
            $error = "No marked exams yet! Check back later.";
        }

        $questionsData = [];


        foreach ($questions as $question) {
            $questionData = ['Good' => 0, 'Fair' => 0, 'Bad' => 0];
            if ($question->type != 'group') {
                $answers = Answer::where(['exam_id' => $exam->id, 'question_id' => $question->id])->get();
                foreach ($answers as $answer) {
                    if ($answer->isMarked == true) {
                        if ($answer->questionMark == 0) {
                            $questionData['Bad']++;
                        } else if ($answer->questionMark < $question->pivot->mark) {
                            $questionData['Fair']++;
                        } else {
                            $questionData['Good']++;
                        }
                    }
                }
                array_push($questionsData, $questionData);
            } else {
                $groupQs = $question->questions;
                foreach ($groupQs as $groupQ) {
                    $questionData = ['Good' => 0, 'Fair' => 0, 'Bad' => 0];
                    $answers = Answer::where(['exam_id' => $exam->id, 'question_id' => $groupQ->id])->get();
                    foreach ($answers as $answer) {
                        if ($answer->isMarked == true) {
                            if ($answer->questionMark == 0) {
                                $questionData['Bad']++;
                            } else if ($answer->questionMark < $question->pivot->mark) {
                                $questionData['Fair']++;
                            } else {
                                $questionData['Good']++;
                            }
                        }
                    }
                    array_push($questionsData, $questionData);
                }
            }
        }


        //bar Chart

        $marks = [];
        $examst = ExamStudent::where(['exam_id' => $exam->id])->get();
        $st_num = ExamStudent::where(['exam_id' => $exam->id])->count();
        $examMark = $exam->totalMark;
        $mark100 = $mark80 = $mark60 = $mark40 = $mark20 = $mark0 = 0;
        foreach ($examst as $st) {
            $totalMark = $st->totalMark;
            if ($totalMark > 0.8 * $examMark && $totalMark <= $examMark) {
                $mark100++;
            } elseif ($totalMark > 0.6 * $examMark && $totalMark <= 0.8 * $examMark) {
                $mark80++;
            } elseif ($totalMark > 0.4 * $examMark && $totalMark <= 0.6 * $examMark) {
                $mark60++;
            } elseif ($totalMark > 0.2 * $examMark && $totalMark <= 0.4 * $examMark) {
                $mark40++;
            } elseif ($totalMark > 0 * $examMark && $totalMark <= 0.2 * $examMark) {
                $mark20++;
            } elseif ($totalMark == 0) {
                $mark0++;
            }
        }


        return view('charts', compact('exam', 'instructor', 'st_num', 'mark0', 'mark20', 'mark40', 'mark60', 'mark80', 'mark100', 'questionsData', 'error'));
    }
}
