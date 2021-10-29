<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;
use App\Models\Instructor;

class DepartmentInstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'instructor_id' => Instructor::all()->random()->id,
            'department_id' => Department::all()->random()->id
        ];
    }
}
