<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\CheatingAction;
use App\Models\CheatingDetails;
use App\Models\Exam;
use App\Models\examSession;
use App\Models\ExamQuestion;
use App\Models\ExamStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheatingDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Exam $exam)
    {
        // get all cheating details for a certain exam
        if (!$exam) {
            return response()->json(['message' => 'No exams found with this id.'], 400);
        }


        $cheatingDetails = CheatingDetails::where([
            'exam_id' => $exam->id
        ])->whereNull('action_id')->get();

        $studentIdsZero = CheatingDetails::where([
            'exam_id' => $exam->id,
            'action_id' => 1
        ])->pluck('student_id')->toArray();
        
        if (!$cheatingDetails) {
            return response()->json(['message' => 'Failed to fetch cheating details!'], 400);
        }

        $cheatingDetails->filter(function ($detail, $key) use ($studentIdsZero) {
            return $studentIdsZero.indexOf($detail->student_id) == -1;
        });
        foreach ($cheatingDetails as $cheatingDetail) {
            // first, check if zero action was taken against student
            // if it was, remove all records regarding that student.
            $student = User::where(['id' => $cheatingDetail->student_id])->get()->first();

            $studentName = $student->firstName . ' ' . $student->lastName;
            $profileImage = $student->image;
            $cheatingDetail['studentName'] = $studentName;
            $cheatingDetail['profileImage'] = $profileImage;
        }

            return response()->json(['message' => 'Fetched details successfully!', 'details' => $cheatingDetails]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (auth()->user()->type != 'student') {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }
        $rules = [
            'examId' => 'required',
            'image' => 'string',
            'type' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $exam = Exam::where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'No exam with this id!'], 404);
        }

        $cheatingDetailAction = CheatingDetails::where(['exam_id' => $exam->id, 'student_id' => auth()->user()->id, 'action_id' => 1])->get()->first();
        if ($cheatingDetailAction) {
            return response()->json(['message' => 'Action already taken against student. Cannot send more requests.']);
        }


        $cheatingDetails = CheatingDetails::create([
            'exam_id' => $exam->id,
            'student_id' => auth()->user()->id,
            'image' => ($request->image) ? $request->image : '',
            'type' => $request->type,
        ]);


        if (!$cheatingDetails) {
            return response()->json(['message' => 'Failed to store cheating details!'], 400);
        } else {
            return response()->json(['message' => 'Stored cheating details successfully!'], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CheatingDetails  $cheatingDetails
     * @return \Illuminate\Http\Response
     */
    public function show(CheatingDetails $cheatingDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CheatingDetails  $cheatingDetails
     * @return \Illuminate\Http\Response
     */
    public function edit(CheatingDetails $cheatingDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CheatingDetails  $cheatingDetails
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CheatingDetails $cheatingDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CheatingDetails  $cheatingDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(CheatingDetails $cheatingDetails)
    {
        //
    }

    public function takeAction(Request $request)
    {
        $rules = [
            'cheatingDetailId' => 'required',
            'type' => 'required',
            'action' => 'required',
            'minusMarks' => 'numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'The given data is invalid!'], 400);
        }
        $cheatingDetails = CheatingDetails::where(['id' => $request->cheatingDetailId])->get()->first();

        if (!$cheatingDetails) {
            return response()->json(['message' => 'No cheating details found with this id'], 404);
        }
        $examSession = examSession::where(['exam_id' => $cheatingDetails->exam_id, 'student_id' => $cheatingDetails->student_id])->orderBy('attempt', 'DESC')->get()->first();;

        if (!$examSession) {
            return response()->json(['message' => 'No exam session with this id!'], 404);
        }

        $zeroAction = CheatingDetails::where(['action_id' => 1, 'student_id' => $cheatingDetails->student_id, 'exam_id' => $cheatingDetails->exam_id])->get()->first();

        if ($zeroAction) {
            return response()->json(['message' => 'Exam already revoked, cannot take more actions against student!'], 400);
        }


        if ($cheatingDetails->action_id) {
            return response()->json(['message' => 'Action already taken!'], 400);
        } else {
            $exam = Exam::where(['id' => $cheatingDetails->exam_id])->get()->first();
            $action = CheatingAction::where(['name' => $request->action])->get()->first();
            if (!$action) {
                return response()->json(['message' => 'Wrong action name specified!'], 400);
            }
            CheatingDetails::where([
                'id' => $request->cheatingDetailId,
                'type' => $request->type,
            ])->update([
                'action_id' => $action->id,
                'minusMarks' => $action->id == 1 ? $exam->totalMark : ($action->id == 2 ? $request->minusMarks : 0),
            ]);
            if ($request->action != 'dismiss') {
                if (!$examSession->isCheater) {
                    examSession::where([
                        'exam_id' => $examSession->exam_id,
                        'student_id' => $examSession->student_id,
                        'attempt' => $examSession->attempt,
                    ])->update([
                        'isCheater' => true,
                    ]);
                }
                $studentMark = ExamStudent::where(['exam_id' => $examSession->exam_id, 'student_id' => $examSession->student_id])->get()->first();
                if ($request->action == 'zero') {
                    // mark exam and set all marks = zero.
                    if ($studentMark) {
                        ExamStudent::where([
                            'exam_id' => $examSession->exam_id,
                            'student_id' => $examSession->student_id
                        ])->update(['totalMark' => 0]);
                    } else {
                        ExamStudent::create([
                            'exam_id' => $examSession->exam_id,
                            'student_id' => $examSession->student_id,
                            'totalMark' => 0
                        ]);
                        $examQuestions = ExamQuestion::where(['exam_id' => $cheatingDetails->exam_id])->get();
                        foreach ($examQuestions as $q) {
                            $a = Answer::where(['exam_id' => $cheatingDetails->exam_id, 'student_id' => $cheatingDetails->student_id, 'question_id' => $q->question_id])->first();
                            if (!$a) {
                                Answer::create([
                                    'exam_id' => $cheatingDetails->exam_id,
                                    'student_id' => $cheatingDetails->student_id,
                                    'question_id' => $q->question_id,
                                    'questionMark' => 0,
                                    'isMarked' => true
                                ]);
                            } else {
                                DB::table('answers')->where(['exam_id' => $cheatingDetails->exam_id, 'student_id' => $cheatingDetails->student_id, 'question_id' => $q->question_id])->update(['questionMark' => 0, 'isMarked' => true]);
                            }
                        }
                    }
                } else if ($request->action == 'minus') {
                    if ($studentMark) {
                        ExamStudent::where([
                            'exam_id' => $examSession->exam_id,
                            'student_id' => $examSession->student_id
                        ])->update(['totalMark' => $studentMark->totalMark - $request->minusMarks]);
                    } else {
                        ExamStudent::create([
                            'exam_id' => $examSession->exam_id,
                            'student_id' => $examSession->student_id,
                            'totalMark' => -1 * $request->minusMarks
                        ]);
                    }
                }
            }


            return response()->json(['message' => 'Action stored successfully!']);
        }
    }
}
