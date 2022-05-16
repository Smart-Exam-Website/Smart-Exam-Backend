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
		if(!$markedExams) {
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

        $st_num = ExamStudent::where(['exam_id' => $exam->id])->count();
        $examMark = $exam->totalMark;
        $mark100 = ExamStudent::where('totalMark', '>', 0.8 * $examMark)->where('totalMark', '<=', $examMark)->count();
        $mark80 = ExamStudent::where('totalMark', '>', 0.6 * $examMark)->where('totalMark', '<=', 0.8 * $examMark)->count();
        $mark60 = ExamStudent::where('totalMark', '>', 0.4 * $examMark)->where('totalMark', '<=', 0.6 * $examMark)->count();
        $mark40 = ExamStudent::where('totalMark', '>', 0.2 * $examMark)->where('totalMark', '<=', 0.4 * $examMark)->count();
        $mark20 = ExamStudent::where('totalMark', '>', 0 * $examMark)->where('totalMark', '<=', 0.2 * $examMark)->count();
        $mark0 = ExamStudent::where('totalMark', 0)->count();

        return view('charts', compact('exam', 'instructor', 'st_num', 'mark0', 'mark20', 'mark40', 'mark60', 'mark80', 'mark100', 'questionsData', 'error'));
    }
}
