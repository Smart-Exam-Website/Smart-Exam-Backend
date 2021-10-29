<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepartmentInstructor;

class DepartmentInstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DepartmentInstructor::factory(10)->create();
    }
}
