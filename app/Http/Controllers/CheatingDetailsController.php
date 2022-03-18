<?php

namespace App\Http\Controllers;

use App\Models\CheatingDetails;
use App\Models\Exam;
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


        $cheatingDetails = DB::table('cheating_details')->where([
            'exam_id' => $exam->id
        ])->whereNull('action_id')->get();

        if (!$cheatingDetails) {
            return response()->json(['message' => 'Failed to fetch cheating details!'], 400);
        } else {
            return response()->json(['message' => 'Fetched details successfully!', 'details' => $cheatingDetails]);
        }
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

        $exam = DB::table('exams')->where(['id' => $request->examId])->get()->first();

        if (!$exam) {
            return response()->json(['message' => 'No exam with this id!'], 404);
        }


        $cheatingDetails = DB::table('cheating_details')->insert([
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
            'id' => 'required',
            'type' => 'required',
            'action' => 'required',
            'minusMarks' => 'numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'The given data is invalid!'], 400);
        }
        $cheatingDetails = DB::table('cheating_details')->where(['id' => $request->id])->get()->first();

        if (!$cheatingDetails) {
            return response()->json(['message' => 'No cheating details found with this id'], 404);
        }
        $examSession = DB::table('examSession')->where(['exam_id' => $cheatingDetails->exam_id, 'student_id' => $cheatingDetails->student_id])->orderBy('attempt', 'DESC')->get()->first();;

        if (!$examSession) {
            return response()->json(['message' => 'No exam session with this id!'], 404);
        }

        
        if ($cheatingDetails->action_id) {
            return response()->json(['message' => 'Action already taken!'], 400);
        } else {
            $exam = DB::table('exams')->where(['id' => $request->examId])->get()->first();
            $action = DB::table('cheating_actions')->where(['name' => $request->action])->get()->first();
            if (!$action) {
                return response()->json(['message' => 'Wrong action name specified!'], 400);
            }
            DB::table('cheating_details')->where([
                'exam_id' => $request->examId,
                'student_id' => $request->studentId,
                'type' => $request->type,
            ])->update([
                'action_id' => $action->id,
                'minusMarks' => $action->id == 1 ? $exam->totalMark : ($action->id == 2 ? $request->minusMarks : 0),
            ]);
            if ($request->action != 'dismiss') {
                if (!$examSession->isCheater) {
                    DB::table('examSession')->where([
                        'exam_id' => $examSession->exam_id,
                        'student_id' => $examSession->student_id,
                        'attempt' => $examSession->attempt,
                    ])->update([
                        'isCheater' => true,
                    ]);
                } else {
                    return response()->json(['message' => 'Action against student already taken!'], 400);
                }
            }

            return response()->json(['message' => 'Action stored successfully!']);
        }
    }
}
