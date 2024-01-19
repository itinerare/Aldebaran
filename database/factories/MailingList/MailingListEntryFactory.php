<?php

namespace Database\Factories\MailingList;

use Carbon\Carbon;
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
     * @return Factory
     */
    public function mailingList($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'mailing_list_id' => $id,
            ];
        });
    }

    /**
     * Generate a mailng list that is sent.
     *
     * @return Factory
     */
    public function sent() {
        return $this->state(function (array $attributes) {
            return [
                'is_draft' => 0,
                'sent_at'  => Carbon::now(),
            ];
        });
    }
}
