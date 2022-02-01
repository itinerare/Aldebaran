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
     * Generate a tag that is hidden in the gallery.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function galleryHide()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
            ];
        });
    }
}
