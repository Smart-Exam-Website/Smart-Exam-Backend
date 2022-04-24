<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormulaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'formula' => $this->faker->randomElement(['x + y' , 'x + 2 * y', 'x - y']),
        ];
    }
}
