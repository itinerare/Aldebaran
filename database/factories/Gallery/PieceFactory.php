<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Piece;
use App\Models\Gallery\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PieceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Piece::class;

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
            'project_id' => Project::factory()->create()->id,
            'is_visible' => 1,
        ];
    }

    /**
     * Generate a piece that is hidden.
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
     * Generate a piece that is marked as a good example.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function goodExample()
    {
        return $this->state(function (array $attributes) {
            return [
                'good_example' => 1,
            ];
        });
    }
}
