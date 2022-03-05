<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'question_id' => Question::all()->random()->id,
            'tag_id' => Tag::all()->random()->id
        ];
    }
}
