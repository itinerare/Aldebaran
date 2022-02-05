<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class PieceTagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PieceTag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'tag_id' => Tag::factory()->create()->id,
        ];
    }

    /**
     * Generate a piece tag for a specific piece. Required.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function piece($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'piece_id' => $id,
            ];
        });
    }

    /**
     * Generate a piece tag using a specific tag.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function tag($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'tag_id' => $id,
            ];
        });
    }
}
