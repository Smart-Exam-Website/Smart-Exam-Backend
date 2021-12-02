<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\McqAnswer;

class McqAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        McqAnswer::factory(10)->create();
    }
}
