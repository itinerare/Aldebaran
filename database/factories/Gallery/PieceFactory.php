<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Piece;
use App\Models\Gallery\Project;
use Carbon\Carbon;
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
     * Generate an piece in a specific project.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function project($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'project_id' => $id,
            ];
        });
    }

    /**
     * Generate a piece with a description.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function description()
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
            ];
        });
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

    /**
     * Generate a piece with a timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function timestamp()
    {
        return $this->state(function (array $attributes) {
            return [
                'timestamp' => Carbon::now(),
            ];
        });
    }
}
