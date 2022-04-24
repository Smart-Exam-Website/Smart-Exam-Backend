<?php

namespace Database\Seeders;

use App\Models\Formula;
use Illuminate\Database\Seeder;

class FormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Formula::factory(10)->create();
    }
}
