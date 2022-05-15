<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\User;
use App\Models\ExamStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ConsoleTVs\Charts\Facades\Charts;

class StatisticsController extends Controller
{
    public function index(Exam $exam)
    {
        $error = "";
        if (!$exam) {
            $error = "No exam found with this id!";
        }
        $questions = $exam->questions;

        $questionsData = [];


        foreach ($questions as $question) {
            $answers = Answer::where(['exam_id' => $exam->id, 'question_id' => $question->id])->get();
            $questionData = ['good' => 0, 'bad' => 0, 'very bad' => 0];
            foreach ($answers as $answer) {
                if ($answer->isMarked == true) {
                    if ($answer->questionMark == 0) {
                        $questionData['very bad']++;
                    } else if ($answer->questionMark < $question->pivot->mark) {
                        $questionData['good']++;
                    }
                }
            }
            array_push($questionsData, $questionData);
        }


        //bar Chart

        $marks = [];

        $ExamStudents = ExamStudent::where(['exam_id' => $exam->id])->get();
        $examMark = $exam->totalMark;
        $mark100 = ExamStudent::where('totalMark', '>', 80)->where('totalMark', '<=', 100)->count();
        $mark80 = ExamStudent::where('totalMark', '>', 60)->where('totalMark', '<=', 80)->count();
        $mark60 = ExamStudent::where('totalMark', '>', 40)->where('totalMark', '<=', 60)->count();
        $mark40 = ExamStudent::where('totalMark', '>', 20)->where('totalMark', '<=', 40)->count();
        $mark20 = ExamStudent::where('totalMark', '>', 0)->where('totalMark', '<=', 20)->count();
        $mark0 = ExamStudent::where('totalMark', 0)->count();

        // $students = User::where('type','student')->get();
        // $instructors = User::where('type','instructor')->get();
        // $admins = User::where('type','admin')->get();
        // $student_count = count($students);
        // $instructor_count = count($instructors);
        // $admin_count = count($admins);
        // $data =[];
        // $data['students'] = $student_count;
        // $data['instructors'] = $instructor_count;
        // $data['admins'] = $admin_count;

        return view('charts', compact('mark0', 'mark20', 'mark40', 'mark60', 'mark80', 'mark100', 'questionsData', 'error'));
    }
}
