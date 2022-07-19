<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];
    }

    /**
     * Generate a piece with a description.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function description() {
        return $this->state(function (array $attributes) {
            return [
                'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
            ];
        });
    }

    /**
     * Generate a project that is hidden.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hidden() {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => 0,
            ];
        });
    }
}
