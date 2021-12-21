<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'exam_id' => Exam::all()->random()->id,
            'faceRecognition' => $this->faker->randomElement([true, false]),
            'faceDetection' => $this->faker->randomElement([true, false]),
            'questionsRandomOrder' => $this->faker->randomElement([true, false]),
            'plagiarismCheck' => $this->faker->randomElement([true, false]),
            'disableSwitchBrowser' => $this->faker->randomElement([true, false]),
            'gradingMethod' => $this->faker->randomElement(['manual', 'automatic']),
        ];
    }
}
