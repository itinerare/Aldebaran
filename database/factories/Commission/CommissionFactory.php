<?php

namespace Database\Factories\Commission;

use App\Models\Commission\Commission;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $commissioner = Commissioner::factory()->create();
        $commissionType = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();

        return [
            //
            'commissioner_id' => $commissioner->id,
            'commission_type' => $commissionType->id,
            'status'          => 'Pending',
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Commission $commission) {
            //
            $commission->update([
                'commission_key' => $commission->id.'_'.randomString(15),
            ]);
        })->afterCreating(function (Commission $commission) {
            //
            $commission->update([
                'commission_key' => $commission->id.'_'.randomString(15),
            ]);
        });
    }

    /**
     * Generate a commission for a given commission type.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function type($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'commission_type' => $id,
            ];
        });
    }

    /**
     * Generate a commission with a given status.
     *
     * @param string $status
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function status($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }
}
