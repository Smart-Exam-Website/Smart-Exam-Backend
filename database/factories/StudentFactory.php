<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\AcademicInfo;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'gradeYear' => $this->faker->randomElement(['First', 'Second', 'Third','Fourth', 'Fifth', 'Sixth']),
            'studentCode' => $this->faker->unique()->randomNumber,
            'academic_info_id' => AcademicInfo::all()->random()->id
        ];
    }
}
