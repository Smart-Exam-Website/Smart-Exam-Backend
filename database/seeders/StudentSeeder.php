<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::all()->count();
        if (!$students) {
            DB::table('students')->insert([
                [
                    'studentCode' => '1122',
                    'department_id' => 1,
                    'id' => 5,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ]);
        }


        // Student::factory(10)->create();
    }
}
