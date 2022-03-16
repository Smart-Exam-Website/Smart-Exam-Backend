<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users = User::all()->count();
        if (!$users) {
            DB::table('users')->insert([
                [
                    'firstName' => 'Nouran',
                    'lastName' => 'Ahmed',
                    'email' => 'adminRole1@gmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'female',
                    'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
                    'phone' => '01002345678',
                    'type' => 'admin',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Haidy',
                    'lastName' => 'Ahmed',
                    'email' => 'adminRole2@gmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'female',
                    'image' => '119963860_3178906825568575_2952121444913761005_o.jpg',
                    'phone' => '01002382678',
                    'type' => 'admin',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Nouran',
                    'lastName' => 'Ahmed',
                    'email' => 'adminRole3@gmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'female',
                    'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
                    'phone' => '01045682678',
                    'type' => 'admin',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Haidy',
                    'lastName' => 'Elnahass',
                    'email' => 'haidyelnahass@hotmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'female',
                    'image' => '119963860_3178906825568575_2952121444913761005_o.jpg',
                    'phone' => '01069682678',
                    'type' => 'instructor',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Nouran',
                    'lastName' => 'Ahmed',
                    'email' => 'nouran@gmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'female',
                    'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
                    'phone' => '01045634578',
                    'type' => 'student',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Hossam',
                    'lastName' => 'Sherif',
                    'email' => 'husam287@hotmail.com',
                    'email_verified_at' => now(),
                    'gender' => 'male',
                    'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
                    'phone' => '01045634578',
                    'type' => 'student',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                [
                    'firstName' => 'Hazem',
                    'lastName' => 'Ali',
                    'email' => 'hazemali100@outlook.com',
                    'email_verified_at' => now(),
                    'gender' => 'male',
                    'image' => 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png',
                    'phone' => '01045634578',
                    'type' => 'student',
                    'password' => Hash::make('secret'), // password
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            ]);
            $users = User::all();
            foreach($users as $user) {
                if($user->type == 'instructor') {
                    DB::table('instructors')->insert([
                        [
                            'degree' => 'Phd',
                            'verified' => 'true',
                            'description' => 'This is the root instructor',
                            'id' => $user->id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]
                    ]);
                } else if ($user->type == 'student') {
                    DB::table('students')->insert([
                        [
                            'studentCode' => '1122',
                            'department_id' => 1,
                            'id' => $user->id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]
                    ]);
                }
            }
        }

        User::factory(10)->create()->each(function ($user) {
            if ($user->type == 'instructor') {

                $Instructors = Instructor::all()->count();
                if (!$Instructors) {
                    DB::table('instructors')->insert([
                        [
                            'degree' => 'Phd',
                            'verified' => 'true',
                            'description' => 'This is the root instructor',
                            'id' => 4,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]
                    ]);
                }

                Instructor::factory()->create([
                    'id' => $user->id,
                ]);
            }
            if ($user->type == 'student') {

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

                Student::factory()->create([
                    'id' => $user->id,
                ]);
            }
        });
    }
}
