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
use Illuminate\Database\Eloquent\Builder;

class QuestionController extends Controller
{
    // get all questions
    public function index()
    {
        $user = auth()->user();
        $queryTag = request('tag');
        $myQuestions = request('myQuestions');
        $type = request('type');
        $searchText = request('search');
        $questions = [];
        $qs = [];

        if ($searchText) {
            $questions = Question::latest('created_at')->where(
                'questionText',
                'LIKE',
                '%' . $searchText . '%'
            )->where(['isHidden' => false]);
        } else {
            $questions = Question::latest('created_at')->where(['isHidden' => false]);
        }

        // if we're filtering by tag, it is easier to get questions associated
        // with a certain tag, rather than fetching all questions then filter by tag.
        if ($queryTag) {

            $questions = $questions->whereHas('tags', function (Builder $query) use ($queryTag) {
                $query->where('tags.name', $queryTag);
            });
        }

        $questions = $questions->get();

        if ($myQuestions) {
            $filteredQuestions = $questions->filter(function ($question, $key) use ($myQuestions, $user) {
                $myQuestions = filter_var($myQuestions, FILTER_VALIDATE_BOOLEAN);
                return $myQuestions ? $question->instructor_id == $user->id : $question->instructor_id != $user->id;
            });

            $questions = collect(array_values($filteredQuestions->all()));
        }

        if ($type) {

            $filteredQuestions = $questions->filter(function ($question, $key) use ($type) {
                return $question->type == $type;
            });

            $questions = collect(array_values($filteredQuestions->all()));
        }



        foreach ($questions as $q) {

            $instructorName = $q->instructor->user->firstName . ' ' . $q->instructor->user->lastName;
            $q->instructorName = $instructorName;
            $q->tags;
            $q->options;
            if ($q->type == "group") {
                $q->questions->each(function ($e) {
                    $e->tags;
                });
            } else if ($q->type == "formula") {
                $q->formulaQuestions;
            }
        }

        return $questions;
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

            $imageName = array_key_exists("image", $fields) ? Str::random(30) . '.jpg' : null;
            if (array_key_exists("image", $fields)) {
                $path = Storage::disk('s3')->put('questionImages/', $imageName, $fields['image']);
                $path = Storage::disk('s3')->url($path);
            }
            $question = Question::create([
                'questionText' => $fields['questionText'],
                'image' => $imageName,
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
        } else if ($question->type == "formula") {
            $question->formulaQuestions;
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

                $imageName = $fields['image'] ? Str::random(30) . '.jpg' : null;
                if (array_key_exists("image", $fields)) {
                    $path = Storage::disk('s3')->put('questionImages/', $imageName, $fields['image']);
                    $path = Storage::disk('s3')->url($path);
                }

                $question = Question::create([
                    'questionText' => array_key_exists("questionText", $fields) ? $fields['questionText'] : $questionn->questionText,
                    'image' => array_key_exists("image", $fields) ? $imageName : $questionn->image,
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

                $correct = "";
                foreach ($answers as $a) {
                    if ($a->isCorrect == 1) {
                        $correct_answer = $a->value;
                    }
                }
                $correct_answer = array_key_exists("correctAnswer", $fields) ? $fields['correctAnswer'] : $correct;


                if ($questionn->type == 'mcq') {

                    for ($i = 0; $i < $answers->count(); $i++) {
                        $correctAnswerid = 0;
                        $op = Option::where(['id' => (int)($answers[$i]->id)])->first();

                        if ($op->value == $correct_answer)
                            $correctAnswerid = $op->id;

                        if (isset(((object)$request)->answers[$i])) {
                            array_push(
                                $newanswers,
                                ((object)$request)->answers[$i]
                            );
                            $option = Option::where(['id' => $answers[$i]->id])->first();

                            $answers[$i]->update([
                                'value' => ((object)$request)->answers[$i]
                            ]);

                            if (isset(((object)$request)->correctAnswer)) {
                                $answers[$i]->update([
                                    'isCorrect' => (int)($answers[$i]->value == $correct_answer)
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
                                    'isCorrect' => (int)($option->id == Option::where(['value' => $correct_answer, 'question_id' => $id])->first()->id)
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
                return $answers;
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
