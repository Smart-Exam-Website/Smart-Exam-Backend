<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Answer;
use App\Models\McqAnswer;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Answer::factory(10)->create();
        // Answer::factory(40)->create()->each(function ($a) {
        //     McqAnswer::factory()->create([
        //         'answer_id' => $a->id,
        //     ]);
        // });
    }
}
