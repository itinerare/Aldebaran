<?php

namespace Database\Factories\MailingList;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MailingListFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            //
            'name'        => $this->faker->domainWord(),
            'description' => null,
            'is_open'     => 1,
        ];
    }

    /**
     * Generate a mailing list with a description.
     *
     * @return Factory
     */
    public function description() {
        return $this->state(function (array $attributes) {
            return [
                'description' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a mailng list that is hidden.
     *
     * @return Factory
     */
    public function closed() {
        return $this->state(function (array $attributes) {
            return [
                'is_open' => 0,
            ];
        });
    }
}
