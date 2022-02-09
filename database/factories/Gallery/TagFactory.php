<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

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
            'is_active'  => 1,
        ];
    }

    /**
     * Generate a tag with a description.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function description()
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a tag that is hidden.
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

    /**
     * Generate a tag that is hidden in the gallery.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
            ];
        });
    }
}
