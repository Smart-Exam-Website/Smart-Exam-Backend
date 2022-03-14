<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CheatingAction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheatingActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = CheatingAction::all()->count();
        if (!$actions) {
            DB::table('cheating_actions')->insert([
                [
                    'name' => 'zero',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'minus',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'dismiss',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ]);
        }
    }
}
