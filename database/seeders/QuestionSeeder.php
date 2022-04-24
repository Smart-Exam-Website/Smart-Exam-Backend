<?php

namespace Database\Seeders;

use App\Models\Formula;
use App\Models\FormulaQuestion;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;

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
        Question::factory(20)->create()->each(function ($q) {
            if($q->type == 'mcq') {
                for ($i = 0; $i < 4; $i++) {
                    if ($i == 0) {
                        Option::factory()->create([
                            'question_id' => $q->id,
                            'isCorrect' => true,
                        ]);
                    } else {
                        Option::factory()->create([
                            'question_id' => $q->id,
                            'isCorrect' => false,
                        ]);
                    }
                }
            } else if($q->type == 'essay') {
                Option::factory()->create([
                    'question_id' => $q->id,
                    'isCorrect' => true,
                ]);
            } else if ($q->type == 'formula') {
                Formula::factory()->create([
                    'question_id' => $q->id
                ]);
                FormulaQuestion::factory()->create([
                    'question_id' => $q->id,
                ]);
            }
        });
    }
}
