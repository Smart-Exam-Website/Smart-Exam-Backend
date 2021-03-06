<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;

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
            'studentCode' => $this->faker->unique()->randomNumber,
            'department_id' => Department::all()->random()->id
        ];
    }
}
