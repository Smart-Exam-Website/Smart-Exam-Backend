<?php

namespace Database\Factories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'questionText' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['mcq', 'essay' , 'group' , 'formula']),
            'isHidden' => false,
            'instructor_id' => Instructor::all()->random()->id
        ];
    }
}
