<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\McqAnswer;
use Illuminate\Http\Request;
use App\Models\Question;

class McqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $fields = $request->validate([
            'questionText' => 'required|string|max:255',
            'mark' => 'required|string',
            'answers'    => 'required|array|min:2',
            'answers.*'  => 'required|string|distinct|min:2',
            'correctAnswer' => 'required|string',
        ]);


        $question = Question::create([
            'questionText' => $fields['questionText'],
            'mark' => $fields['mark'],
            'type' => 'mcq'
        ]);

        $answers = $fields['answers'];

        foreach ($answers as $a) {
            $answerss = Answer::create([
                'value' => $a,
                'type' => 'mcq'
            ]);

            if ($fields['correctAnswer'] == $answerss->value) {
                $mcqanswers = McqAnswer::create([
                    'question_id' => $question->id,
                    'answer_id' => $answerss->id,
                    'isCorrect' => true
                ]);
            } else {
                $mcqanswers = McqAnswer::create([
                    'question_id' => $question->id,
                    'answer_id' => $answerss->id,
                    'isCorrect' => false
                ]);
            }
        }



        return response($question, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
