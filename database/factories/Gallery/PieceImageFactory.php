<?php

namespace Database\Factories\Gallery;

use Illuminate\Database\Eloquent\Factories\Factory;

class PieceImageFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
            'hash'             => randomString(15),
            'fullsize_hash'    => randomString(15),
            'extension'        => 'png',
            'is_primary_image' => 0,
            'data'             => '{"scale":".30","opacity":"30","position":"bottom-right","color":null,"image_scale":null,"watermarked":0,"text_watermark":null,"text_opacity":".30"}',
            'is_visible'       => 1,
            'sort'             => 0,
        ];
    }

    /**
     * Generate an image for a specific piece. Required.
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
     * Generate an image with a caption.
     *
     * @return Factory
     */
    public function caption() {
        return $this->state(function (array $attributes) {
            return [
                'description' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a primary image.
     *
     * @return Factory
     */
    public function primary() {
        return $this->state(function (array $attributes) {
            return [
                'is_primary_image' => 1,
            ];
        });
    }

    /**
     * Generate an image that is hidden.
     *
     * @return Factory
     */
    public function hidden() {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => 0,
            ];
        });
    }
}
