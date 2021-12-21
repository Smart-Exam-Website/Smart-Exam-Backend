<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'startAt' => $this->faker->dateTime(),
            'endAt' => $this->faker->dateTime(),
            'duration' => $this->faker->time($format = 'H:i:s', $max = 'now'),
            'numberOfTrials' => $this->faker->randomDigit(),
            'totalMark' => $this->faker->randomNumber(2),
            'examSubject' => $this->faker->word(),
        ];
    }
}
