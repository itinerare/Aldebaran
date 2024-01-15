<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class PieceTagFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
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
     * @return Factory
     */
    public function piece($id) {
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
     * @return Factory
     */
    public function tag($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'tag_id' => $id,
            ];
        });
    }
}
