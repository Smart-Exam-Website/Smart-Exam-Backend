<?php

namespace Database\Seeders;

use App\Models\McqAnswer;
use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Question::factory(10)->create();
        Question::factory(10)->create()->each(function ($q) {
            for ($i = 0; $i < 4; $i++) {
                McqAnswer::factory()->create([
                    'question_id' => $q->id,
                    'answer_id' => $i + 1
                ]);
            }
        });
    }
}
