<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\McqAnswer;

class QuestionOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        QuestionOption::factory(10)->create();
    }
}
