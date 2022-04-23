<?php

namespace Database\Seeders;

use App\Models\FormulaQuestion;
use Illuminate\Database\Seeder;

class FormulaQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FormulaQuestion::factory(20)->create();
    }
}
