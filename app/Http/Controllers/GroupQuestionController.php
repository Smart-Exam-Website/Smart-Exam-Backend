<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\GroupQuestion;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\Tag;
use App\Models\ExamQuestion;
use App\Models\Exam;
use App\Models\Option;
use Illuminate\Http\Request;

class GroupQuestionController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->type == 'instructor') {

            $fields = $request->validate([
                'questionText' => 'string|max:255',
                'image' => 'image',
                'type' => 'required|string',
                'questions'    => 'required|array',
                'questions.*'  => 'required|distinct',
                'tags'    => 'array',
                'tags.*'  => 'string|distinct'
            ]);

            if (array_key_exists("image", $fields)) {
                $path = Storage::disk('s3')->put('questionImages', $fields['image']);
                $path = Storage::disk('s3')->url($path);
            }
            $question = Question::create([
                'questionText' => array_key_exists("questionText", $fields) ? $fields['questionText'] : NULL,
                'image' => array_key_exists("image", $fields) ? $path : NULL,
                'type' => $fields['type'],
                'isHidden' => false,
                'instructor_id' => $user->id
            ]);

            if ($request->has('tags')) {
                $tags = $fields['tags'];
            } else {
                $tags = [];
            }

            foreach ($tags as $a) {
                $taggs = Tag::where(['name' => $a])->first();
                if ($taggs != null) {
                    $tid = $taggs->id;
                } else {
                    $t = Tag::create([
                        'name' => $a
                    ]);
                    $tid = $t->id;
                }

                $qtags = QuestionTag::where(['question_id' => $question->id, 'tag_id' => $tid])->first();

                if ($qtags == null) {

                    $t = QuestionTag::create([
                        'question_id' => $question->id,
                        'tag_id' => $tid
                    ]);
                }
            }
            $questions = $fields['questions'];
            foreach ($questions as $q) {
                GroupQuestion::create([
                    'group_id' => $question->id,
                    'question_id' => $q,
                ]);
            }
            $question = Question::where(['id' => $question->id])->first();
            $question->tags;
            $question->questions->each(function ($e) {
                $e->tags;
            });;
            return response($question, 201);
        } else {
            return response()->json(['message' => 'There is no logged in Instructor'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
