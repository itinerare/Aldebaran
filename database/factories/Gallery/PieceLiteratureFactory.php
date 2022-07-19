<?php

namespace Database\Factories\Gallery;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PieceLiteratureFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            //
            'text'       => $this->faker->realText(),
            'is_primary' => 0,
            'is_visible' => 1,
            'sort'       => 0,
        ];
    }

    /**
     * Generate a literature for a specific piece. Required.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function piece($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'piece_id' => $id,
            ];
        });
    }

    /**
     * Generate an literature with a thumbnail.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function thumbnail() {
        return $this->state(function (array $attributes) {
            return [
                'hash'      => randomString(15),
                'extension' => 'png',
            ];
        });
    }

    /**
     * Generate a primary literature.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function primary() {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => 1,
            ];
        });
    }

    /**
     * Generate a literature that is hidden.
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
