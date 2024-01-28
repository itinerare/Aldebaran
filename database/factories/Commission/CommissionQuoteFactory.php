<?php

namespace Database\Factories\Commission;

use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionQuote;
use App\Models\Commission\CommissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CommissionQuoteFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        $commissioner = Commissioner::factory()->create();
        $commissionType = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();

        return [
            //
            'commissioner_id'    => $commissioner->id,
            'commission_type_id' => $commissionType->id,
            'status'             => 'Pending',
            'description'        => $this->faker->realText(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure() {
        return $this->afterMaking(function (CommissionQuote $quote) {
            //
            $quote->update([
                'quote_key' => $quote->id.'_'.randomString(15),
            ]);
        })->afterCreating(function (CommissionQuote $quote) {
            //
            $quote->update([
                'quote_key' => $quote->id.'_'.randomString(15),
            ]);
        });
    }

    /**
     * Generate a commission for a given commission type.
     *
     * @param int $id
     *
     * @return Factory
     */
    public function type($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'commission_type_id' => $id,
            ];
        });
    }

    /**
     * Generate a commission with a given status.
     *
     * @param string $status
     *
     * @return Factory
     */
    public function status($status) {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }
}
