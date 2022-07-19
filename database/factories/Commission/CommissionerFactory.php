<?php

namespace Database\Factories\Commission;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionerFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            //
            'email'   => $this->faker->safeEmail(),
            'contact' => $this->faker->domainWord(),
            'paypal'  => $this->faker->safeEmail(),
            'name'    => mt_rand(1, 2) == 2 ? $this->faker->domainWord() : null,
        ];
    }

    /**
     * Generate a banned commissioner.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function banned() {
        return $this->state(function (array $attributes) {
            return [
                'is_banned' => 1,
            ];
        });
    }
}
