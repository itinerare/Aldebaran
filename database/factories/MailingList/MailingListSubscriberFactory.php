<?php

namespace Database\Factories\MailingList;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MailingListSubscriberFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'email' => $this->faker->email(),
            'token' => randomString(15),
        ];
    }

    /**
     * Generate a subscriber for a specific mailng list. Required.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function mailingList($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'mailing_list_id' => $id,
            ];
        });
    }

    /**
     * Generate a verified subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function verified() {
        return $this->state(function (array $attributes) {
            return [
                'is_verified' => 1,
            ];
        });
    }
}
