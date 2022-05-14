<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];
    }

    /**
     * Generate a program that is hidden.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hidden()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => 0,
            ];
        });
    }
}
