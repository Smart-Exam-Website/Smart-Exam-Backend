<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PlagiarismController extends Controller
{
    public function plagiarism(Request $request)
    {
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'There is no logged in instructor!'], 400);
        }

        $rules = [
            'examId' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error validating request body'], 400);
        }

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'This exam does not exist!'], 400);
        }

        $config = $exam->config;

        if (!$config->plagiarismCheck) {
            return response()->json(['message' => 'This exam does not support plagiarism check!'], 400);
        }

        $exqs = ExamQuestion::where(['exam_id' => $exam->id])->get();
        $essayqs = [];
        foreach ($exqs as $ex) {
            if ($ex->question->type == "essay") {
                array_push($essayqs, $ex->question_id);
            }
        }

        // loop over all essay questions on the exam

        foreach ($essayqs as $qid) {

            $q = Question::where(['id' => $qid])->get()->first();

            if (!$q) {
                return response()->json(['message' => 'This question does not Exist!'], 400);
            }

            $allanswers = Answer::where(['question_id' => $qid, 'exam_id' => $request->examId])->get();

            $list = [];
            $list[0] = $allanswers[0]->question->options[0]->value;
            foreach ($allanswers as $a) {
                $list[intval($a->student_id)] = $a->studentAnswer;
            }
            $response = Http::post('https://nlp.api.smart-exam.ml/plagiarism/predict', [
                'students_dict' => $list,
            ]);

            if ($response->ok()) {
                if ($response->status() != 200) {
                    return response()->json(['message' => 'Failed to send list!'], 400);
                } else {
                    $finalResponse = [];
                    $plagiarismResult = $response->object()->plagiarism_results;
                    foreach ($plagiarismResult as $result) {
                        $keys = array_keys((array) $result);
                        if (in_array(0, $keys)) {
                            continue;
                        }
                        $resultArray = json_decode(json_encode($result), true);
                        $studentId = $keys[0];
                        $student = User::where(['id' => $studentId])->first();
                        $studentName = $student->firstName . ' ' . $student->lastName;
                        $studentCode = $student->student->studentCode;
                        $currentStudent = ["id" => $studentId, "name" => $studentName,"studentCode" => $studentCode, "similarStudents" => []];
                        $similarStudents = $resultArray[$studentId];
                        $studentsIds = array_keys((array) $similarStudents);
                        foreach($studentsIds as $id) {
                            if ($id == 0) {
                                continue;
                            }
                            $similarStudent = User::where(['id' => $id])->first();
                            $similarStudentName = $similarStudent->firstName . ' ' . $similarStudent->lastName;
                            $similarStudentCode = $similarStudent->student->studentCode;
                            array_push($currentStudent["similarStudents"], ["id" => $id, "name" => $similarStudentName, "studentCode" => $similarStudentCode, "similarity" => $similarStudents[$id]]);
                        }
                        array_push($finalResponse, $currentStudent);
                    }
                    return response()->json(['message' => 'Plagiarism check done successfully!', "result" => $finalResponse]);
                }
            } else {
                return response()->json(['message' => 'An error occurred!'], 400);
            }
        }
    }
}
