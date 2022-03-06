<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;
use App\Models\QuestionOption;

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
                if ($i == 0) {
                    QuestionOption::factory()->create([
                        'question_id' => $q->id,
                        'isCorrect' => true,
                        'id' => Option::all()->random()->id
                    ]);
                } else {
                    QuestionOption::factory()->create([
                        'question_id' => $q->id,
                        'isCorrect' => false,
                        'id' => Option::all()->random()->id
                    ]);
                }
            }
        });
    }
}
