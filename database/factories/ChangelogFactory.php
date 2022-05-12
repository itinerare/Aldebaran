<?php

namespace Database\Factories;

use App\Models\Changelog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChangelogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Changelog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
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
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function title()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a changelog that is hidden.
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
