<?php

namespace Database\Factories;

use App\Models\User;
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
            'verified' => $this->faker->randomElement(['true', 'false']),
        ];
    }
}
