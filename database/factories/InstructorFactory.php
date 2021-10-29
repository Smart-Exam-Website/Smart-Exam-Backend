<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'degree' => $this->faker->word(),
            'description' => $this->faker->text(700),
            'verified' => $this->faker->randomElement(['true', 'false']),
        ];
    }
}
