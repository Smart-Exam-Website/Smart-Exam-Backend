<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $schools = School::all()->count();
        if (!$schools) {
            DB::table('schools')->insert([
                [
                    'name' => 'Ain Shams University',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ]);
        }


        School::factory(10)->create();
    }
}
