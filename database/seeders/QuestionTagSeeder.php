<?php

namespace Database\Seeders;

use App\Models\QuestionTag;
use Illuminate\Database\Seeder;

class QuestionTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        QuestionTag::factory(10)->create();
    }
}
