<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create()->each(function ($user) {
            if($user->type == 'instructor') {
                Instructor::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });

    }
}
