<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaQuestion;
use App\Models\FormulaVariable;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FormulaQuestionController extends Controller
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
        if (auth()->user()->type != 'instructor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $rules = [
            'questionText' => 'required',
            'image' => 'image',
            'type' => 'required|string',
            'tags'    => 'array',
            'tags.*'  => 'string|distinct',
            'formula' => 'required',
            'variables.*' => ['required'],
            'formulas.*' => ['required'],

        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            dd($validator->errors());
            return response()->json(['message' => 'the given data is invalid'], 400);
        }

        $imageName = $request->image? Str::random(30) . '.jpg': null;
        if ($request->image) {
            $path = Storage::disk('s3')->put('questionImages/', $imageName, $request->image);
            $path = Storage::disk('s3')->url($path);
        }

        $question = Question::create([
            'questionText' => $request->questionText,
            'image' => $imageName,
            'type' => $request->type,
            'isHidden' => false,
            'instructor_id' => auth()->user()->id
        ]);

        if(!$question) {
            return response()->json(['message' => 'Failed to create formula question!'], 400);
        }

        if($request->tags) {
            $tags = $request->tags;
            foreach($tags as $tag) {
                $existingTag = Tag::where(['name' => $tag])->get()->first();

                if(!$existingTag) {
                    $existingTag = Tag::create([
                        'name' => $tag
                    ]);

                    if(!$existingTag) {
                        return response()->json(['message' => 'Failed.'], 400);
                    }
                }

                $existingTag->questions()->attach($question);


            }
        }



        $formula = Formula::create([
            'question_id' => $question->id,
            'formula' => $request->formula
        ]);

        if (!$formula) {
            return response()->json(['message' => 'Failed to create formula!'], 400);
        }

        $variables = $request->variables;
        foreach ($variables as $variable) {

            $createdVar = FormulaVariable::create([
                'question_id' => $question->id,
                'variable' => $variable[0],
                'startVal' => $variable[1],
                'endVal' => $variable[2]
            ]);

            if (!$createdVar) {
                return response()->json(['message' => 'Failed to create formula!'], 400);
            }
        }

        $formulas = $request->formulas;
        foreach ($formulas as $formula) {

            $createdFormula = FormulaQuestion::create([
                'question_id' => $question->id,
                'formulaText' => $formula[0],
                'value' => $formula[1]

            ]);

            if (!$createdFormula) {
                return response()->json(['message' => 'Failed to create formula!'], 400);
            }
        }

        return response()->json(['message' => 'Created formula question successfully!'], 201);
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
