<?php

namespace Database\Seeders;

use App\Models\AcademicInfo;
use App\Models\Instructor;
use Database\Factories\AcademicInfoInstructorFactory;
use Illuminate\Database\Seeder;

class AcademicInfoInstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $academicInfos = AcademicInfo::get();
        $instructors = Instructor::get();

        $academicInfos->each(function ($academicInfos) use ($instructors) {
            $academicInfos->instructors()->save($instructors->random());
        });
    }
}
