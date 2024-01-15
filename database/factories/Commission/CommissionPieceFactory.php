<?php

namespace Database\Factories\Commission;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionPieceFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
        ];
    }

    /**
     * Generate a commission piece for a specific commission.
     *
     * @param int $id
     *
     * @return Factory
     */
    public function commission($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'commission_id' => $id,
            ];
        });
    }

    /**
     * Generate a commission piece for a specific piece.
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
}
