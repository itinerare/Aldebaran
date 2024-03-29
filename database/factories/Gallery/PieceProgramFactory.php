<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class PieceProgramFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
            'program_id' => Program::factory()->create()->id,
        ];
    }

    /**
     * Generate a piece program for a specific piece. Required.
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
     * Generate a piece program using a specific program.
     *
     * @param int $id
     *
     * @return Factory
     */
    public function program($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'program_id' => $id,
            ];
        });
    }
}
