<?php

namespace Database\Seeders;

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
        Question::factory(10)->create()->each(function ($q) {
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
        });
    }
}
