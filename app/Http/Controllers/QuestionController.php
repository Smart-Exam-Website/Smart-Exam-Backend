<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\ExamQuestion;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionTag;
use App\Models\Tag;

class QuestionController extends Controller
{
    // get all questions
    public function index()
    {
        $user = auth()->user();
        $queryTag = request('tag');
        $myQuestions = request('myQuestions');
        $questions = [];
        $qs = [];
        if ($queryTag) {
            $tag = Tag::where('name', 'LIKE', $queryTag . '%')->get()->first();
            $questions = $tag->questions;
            if ($myQuestions != NULL) {
                if ($myQuestions == "true") {
                    foreach ($questions as $q) {
                        if (($q->instructor_id == $user->id) && ($q->isHidden == false)) {
                            array_push($qs, $q);
                        }
                    };
                } else if ($myQuestions == "false") {
                    foreach ($questions as $q) {
                        if (($q->instructor_id != $user->id) && ($q->isHidden == false)) {
                            array_push($qs, $q);
                        }
                    };
                }
            } else {
                foreach ($questions as $q) {
                    if ($q->isHidden == false) {
                        array_push($qs, $q);
                    }
                };
            }
        } else {
            if ($myQuestions != NULL) {
                if ($myQuestions == "true") {
                    $questions = Question::latest('created_at')->where(['instructor_id' => $user->id, 'isHidden' => false])->get();
                } else if ($myQuestions == "false") {
                    $questions = Question::latest('created_at')->where('instructor_id', '<>', $user->id)->where(['isHidden' => false])->get();
                }
            } else {
                $questions = Question::latest('created_at')->where(['isHidden' => false])->get();
            }
            $qs = $questions;
        }

        foreach ($questions as $q) {
            $q->instructor->user;
            $q->tags;
            $q->options;
            if ($q->type == "group") {
                $q->questions;
            }
        }

        return $qs;
    }

    // Create new question
    public function store(Request $request)
    {

        $user = auth()->user();
        if ($user->type == 'instructor') {

            $fields = $request->validate([
                'questionText' => 'required|string|max:255',
                'image' => 'image',
                'type' => 'required|string',
                'answers'    => 'required|array',
                'answers.*'  => 'required|string|distinct',
                'tags'    => 'array',
                'tags.*'  => 'string|distinct',
                'correctAnswer' => 'string',
            ]);

            if (array_key_exists("image", $fields)) {
                $path = Storage::disk('s3')->put('questionImages', $fields['image']);
                $path = Storage::disk('s3')->url($path);
            }
            $question = Question::create([
                'questionText' => $fields['questionText'],
                'image' => array_key_exists("image", $fields) ? $path : NULL,
                'type' => $fields['type'],
                'isHidden' => false,
                'instructor_id' => $user->id
            ]);

            $answers = $fields['answers'];
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

            if ($fields['type'] == 'mcq') {
                foreach ($answers as $a) {
                    if ($fields['correctAnswer'] == $a) {
                        Option::create([
                            'value' => $a,
                            'type' => $fields['type'],
                            'question_id' => $question->id,
                            'isCorrect' => true
                        ]);
                    } else {
                        Option::create([
                            'value' => $a,
                            'type' => $fields['type'],
                            'question_id' => $question->id,
                            'isCorrect' => false
                        ]);
                    }
                }
            } else if ($fields['type'] == 'essay') {
                foreach ($answers as $a) {
                    Option::create([
                        'question_id' => $question->id,
                        'value' => $a,
                        'type' => $fields['type'],
                        'isCorrect' => true
                    ]);
                }
            }

            $question->options;
            $question->tags;

            return response($question, 201);
        } else {
            return response()->json(['message' => 'There is no logged in Instructor'], 400);
        }
    }

    // Get question details
    public function show($id)
    {
        $question = Question::where('id', $id)->get()->first();
        $question->instructor->user;
        $question->tags;
        if ($question->type == "group") {
            $question->questions->each(function ($e) {
                $e->tags;
            });
        }
        $question->options;
        return response()->json(['question' => $question]);
    }

    // Edit Question
    public function update(Request $request, $id)
    {
        $questionn = Question::find($id);

        if (!$questionn) {
            return response()->json(['message' => 'No Question Found']);
        }
        $user = auth()->user();
        $examQuestions = ExamQuestion::where(['question_id' => $id])->get();
        $exams = [];
        foreach ($examQuestions as $exQ) {
            array_push($exams, Exam::find($exQ->exam_id));
        }
        usort($exams, function ($a, $b) {
            return strcmp($a->startAt, $b->startAt);
        });


        $now = date("Y-m-d H:i:s");
        if (count($exams) > 0)
            $start_time = $exams[0]->startAt;
        else $start_time = 0;
        //return response([$now, $start_time]);
        if ($start_time != 0 && $now >= $start_time) {
            //create New Question Because this question is found in another prev exam
            if ($user->type == 'instructor') {

                $fields = $request->validate([
                    'questionText' => 'string|max:255',
                    'image' => 'image',
                    'answers'    => 'array',
                    'answers.*'  => 'string|distinct',
                    'correctAnswer' => 'string'
                ]);

                if (array_key_exists("image", $fields)) {
                    $path = Storage::disk('s3')->put('questionImages', $fields['image']);
                    $path = Storage::disk('s3')->url($path);
                }

                $question = Question::create([
                    'questionText' => array_key_exists("questionText", $fields) ? $fields['questionText'] : $questionn->questionText,
                    'image' => array_key_exists("image", $fields) ? $path : $questionn->image,
                    'type' => $questionn->type,
                    'instructor_id' => $questionn->instructor_id
                ]);

                $answers = $questionn->options;
                $newanswers = [];

                for ($i = 0; $i < $answers->count(); $i++) {
                    array_push($newanswers, isset(((object)$request)->answers[$i]) ? $fields['answers'][$i] : $answers[$i]->value);
                }

                $correct = "";
                foreach ($answers as $a) {
                    if ($a->isCorrect == 1) {
                        $correct_answer = $a->value;
                    }
                }
                $correct_answer = array_key_exists("correctAnswer", $fields) ? $fields['correctAnswer'] : $correct;

                if ($questionn->type == 'mcq') {
                    foreach ($newanswers as $a) {
                        if ($correct_answer == $a) {
                            Option::create([
                                'value' => $a,
                                'type' => $questionn->type,
                                'question_id' => $question->id,
                                'isCorrect' => true
                            ]);
                        } else {
                            Option::create([
                                'value' => $a,
                                'type' => $questionn->type,
                                'question_id' => $question->id,
                                'isCorrect' => false
                            ]);
                        }
                    }
                } else if ($questionn->type == 'essay') {
                    foreach ($answers as $a) {
                        Option::create([
                            'value' => $a,
                            'type' => $questionn->type,
                            'question_id' => $question->id,
                            'isCorrect' => true
                        ]);
                    }
                }

                return response($question, 201);
            } else {
                return response()->json(['message' => 'There is no logged in Instructor'], 400);
            }
        } else {
            //we can update this question because it is not in one of the prev exams
            $questionn = Question::find($id);
            $answers = $questionn->options;

            if ($user->type == 'instructor') {

                $fields = $request->validate([
                    'questionText' => 'string|max:255',
                    'image' => 'image',
                    'answers'    => 'array',
                    'answers.*'  => 'string|distinct',
                    'correctAnswer' => 'string'
                ]);

                $newanswers = [];

                if ($questionn->type == 'mcq') {

                    for ($i = 0; $i < $answers->count(); $i++) {
                        $correctAnswerid = 0;
                        $op = Option::where(['id' => (int)($answers[$i]->id)])->first();

                        if ($op->value == $request['correctAnswer'])
                            $correctAnswerid = $op->id;

                        if (isset(((object)$request)->answers[$i])) {
                            array_push(
                                $newanswers,
                                ((object)$request)->answers[$i]
                            );
                            $option = Option::where(['id' => $answers[$i]->id])->first();
                            $option->update([
                                'value' => ((object)$request)->answers[$i]
                            ]);
                            if (isset(((object)$request)->correctAnswer)) {

                                $answers[$i]->update([
                                    'isCorrect' => (int)($option->id == $correctAnswerid)
                                ]);
                            }
                        } else {
                            array_push(
                                $newanswers,
                                Option::where(['id' => $answers[$i]->id])->first()->value
                            );
                            $option = Option::where(['id' => $answers[$i]->id])->first();
                            if (isset(((object)$request)->correctAnswer)) {
                                $answers[$i]->update([
                                    'isCorrect' => (int)($option->id == Option::where(['value' => $request['correctAnswer']])->first()->id)
                                ]);
                            }
                        }
                    }
                } else if ($questionn->type == 'essay') {

                    for ($i = 0; $i < $answers->count(); $i++) {

                        if (isset(((object)$request)->answers[$i])) {
                            array_push(
                                $newanswers,
                                ((object)$request)->answers[$i]
                            );
                            $option = Option::where(['id' => $answers[$i]->id])->first();

                            $option->update([
                                'value' => ((object)$request)->answers[$i]
                            ]);

                            $answers[$i]->update([
                                'isCorrect' => 1
                            ]);
                        } else {
                            array_push(
                                $newanswers,
                                Option::where(['id' => $answers[$i]->id])->first()->value
                            );
                            $option = Option::where(['id' => $answers[$i]->id])->first();
                            $answers[$i]->update([
                                'isCorrect' => 1
                            ]);
                        }
                    }
                }

                if (array_key_exists("image", $fields)) {
                    if ($questionn->image) {
                        $s = explode("/", $questionn->image);
                        Storage::disk('s3')->delete($s[3] . "/" . $s[4]);
                    }
                    $path = Storage::disk('s3')->put('questionImages', $fields['image']);
                    $path = Storage::disk('s3')->url($path);
                }

                $questionn->update([
                    'questionText' => array_key_exists("questionText", $fields) ? $request['questionText'] : $questionn->questionText,
                    'image' => array_key_exists("image", $fields) ? $path : $questionn->image
                ]);

                return response(['question' => $questionn], 200);
            } else {
                return response()->json(['message' => 'There is no logged in Instructor'], 400);
            }
        }
    }


    // Delete Question
    public function destroy($id)
    {

        $user = auth()->user();
        $examQuestions = ExamQuestion::where(['question_id' => $id])->get();
        $exams = [];
        foreach ($examQuestions as $exQ) {
            array_push($exams, Exam::find($exQ->exam_id));
        }
        usort($exams, function ($a, $b) {
            return strcmp($a->startAt, $b->startAt);
        });


        $now = date("Y-m-d H:i:s");
        if (count($exams) > 0)
            $start_time = $exams[0]->startAt;
        else $start_time = 0;

        if ($start_time != 0 && $now >= $start_time) {
            //We cannot delete only set is hidden to true
            if ($user->type == 'instructor') {
                $question = Question::where(['id' => $id])->first();
                if ($question == null) {
                    return response()->json(['message' => 'There is no Question with this id'], 200);
                }
                $question->update(['isHidden' => true]);
                $question->save();
                return response()->json(['message' => 'Question is Hidden'], 200);
            } else {
                return response()->json(['message' => 'There is no logged in Instructor'], 400);
            }
        } else {

            if ($user->type == 'instructor') {
                $question = Question::where(['id' => $id])->first();
                if ($question == null) {
                    return response()->json(['message' => 'There is no Question with this id'], 200);
                }
                if ($question->image) {
                    $s = explode("/", $question->image);
                    Storage::disk('s3')->delete($s[3] . "/" . $s[4]);
                }
                $question->delete();
                return response()->json(['message' => 'Question Deleted'], 200);
            } else {
                return response()->json(['message' => 'There is no logged in Instructor'], 400);
            }
        }
    }
}
