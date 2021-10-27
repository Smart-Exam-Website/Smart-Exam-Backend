<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicInfo;

class AcademicInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AcademicInfo::factory(10)->create();
    }
}
