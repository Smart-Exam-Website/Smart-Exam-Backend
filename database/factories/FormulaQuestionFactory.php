<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormulaQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'formulaText' => $this->faker->randomElement(['What is 1 + 2?' , 'What is 1 + 2 * 7?', 'What is 6 - 5?']),
            'value' => $this->faker->randomNumber(2)
        ];
    }
}
