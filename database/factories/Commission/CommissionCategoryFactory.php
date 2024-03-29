<?php

namespace Database\Factories\Commission;

use App\Models\Commission\CommissionClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionCategoryFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        $class = CommissionClass::factory()->create();

        return [
            //
            'name'      => $this->faker->unique()->domainWord(),
            'is_active' => 1,
            'class_id'  => $class->id,
            'sort'      => 0,
        ];
    }

    /**
     * Generate a category for a specific class.
     *
     * @param int $id
     *
     * @return Factory
     */
    public function class($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'class_id' => $id,
            ];
        });
    }

    /**
     * Generate an inactive category.
     *
     * @return Factory
     */
    public function inactive() {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
            ];
        });
    }

    /**
     * Generate a category with test data.
     *
     * @param bool $include
     *
     * @return Factory
     */
    public function testData($include) {
        return $this->state(function (array $attributes) use ($include) {
            return [
                'data' => '{"fields":{"'.$this->faker->unique()->domainWord().'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":"null}},"include":{"class":'.($include ? 1 : 0).'}}',
            ];
        });
    }
}
