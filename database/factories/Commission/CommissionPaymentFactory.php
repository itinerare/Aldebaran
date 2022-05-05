<?php

namespace Database\Factories\Commission;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'cost' => mt_rand(1, 50),
            'tip'  => 0.00,
        ];
    }

    /**
     * Generate a paid payment.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_paid' => 1,
                'paid_at' => Carbon::now(),
            ];
        });
    }
}
