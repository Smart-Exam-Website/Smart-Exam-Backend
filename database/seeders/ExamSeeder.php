<?php

namespace Database\Seeders;

use App\Models\Configuration;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exams = Exam::factory(10)->create();
        foreach($exams as $exam) {
            Configuration::factory()->create([
                'exam_id' => $exam->id
            ]);
            $questions = Question::all()->random(4);
            $exam->questions()->attach($questions);
        }
    }
}
