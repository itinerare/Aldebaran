<?php

namespace Database\Factories\MailingList;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MailingListEntryFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            //
            'subject'  => $this->faker->domainWord(),
            'text'     => $this->faker->domainWord(),
            'is_draft' => 1,
        ];
    }

    /**
     * Generate an entry for a specific mailng list. Required.
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
}
