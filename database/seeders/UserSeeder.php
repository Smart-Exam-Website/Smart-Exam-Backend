<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create()->each(function ($user){
            if($user->type == 'student'){
                Student::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });

    }
}
