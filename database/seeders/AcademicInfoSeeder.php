<?php

namespace Database\Seeders;

use App\Models\AcademicInfo;
use Illuminate\Database\Seeder;

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
