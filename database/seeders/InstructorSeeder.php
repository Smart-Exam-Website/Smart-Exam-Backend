<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $Instructors = Instructor::all()->count();
        if (!$Instructors) {
            DB::table('instructors')->insert([
                [
                    'degree' => 'Phd',
                    'verified' => true,
                    'description' => 'This is the root instructor',
                    'user_id' => 4,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ]);
        }

        // Instructor::factory(10)->create();
    }
}
