<?php

namespace Database\Factories;

use App\Models\AcademicInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'department' => $this->faker->word(),
            'school' => $this->faker->word(),
        ];
    }
}
