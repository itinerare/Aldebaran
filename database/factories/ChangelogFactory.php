<?php

namespace Database\Factories;

use App\Models\Changelog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChangelogFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
            'name'       => null,
            'text'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];
    }

    /**
     * Generate a changelog with a title.
     *
     * @return Factory
     */
    public function title() {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a changelog that is hidden.
     *
     * @return Factory
     */
    public function hidden() {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => 0,
            ];
        });
    }
}
