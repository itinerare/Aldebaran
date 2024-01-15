<?php

namespace Database\Factories\Commission;

use App\Models\Commission\CommissionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionTypeFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        $category = CommissionCategory::factory()->create();

        return [
            //
            'category_id'   => $category->id,
            'name'          => $this->faker->unique()->domainWord(),
            'key'           => randomString(10),
            'availability'  => 0,
            'is_visible'    => 1,
            'data'          => '{"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
            'sort'          => 0,
            'show_examples' => 1,
        ];
    }

    /**
     * Generate a type for a specific category.
     *
     * @param int $id
     *
     * @return Factory
     */
    public function category($id) {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'category_id' => $id,
            ];
        });
    }

    /**
     * Generate a hidden type.
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

    /**
     * Generate a type with a set number of slots.
     *
     * @param int $slots
     *
     * @return Factory
     */
    public function slots($slots) {
        return $this->state(function (array $attributes) use ($slots) {
            return [
                'availability' => $slots,
            ];
        });
    }

    /**
     * Generate a type with test data.
     *
     * @param array       $pricing
     * @param int         $tag
     * @param bool        $includeClass
     * @param bool        $includeCategory
     * @param string|null $extras
     *
     * @return Factory
     */
    public function testData($pricing, $tag = null, $includeClass = 0, $includeCategory = 0, $extras = null) {
        return $this->state(function (array $attributes) use ($pricing, $extras, $tag, $includeClass, $includeCategory) {
            return [
                'data' => '{"fields":{"test":{"label":"Test","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":'.($includeClass ? 1 : 0).',"category":'.($includeCategory ? 1 : 0).'},"pricing":{"type":"'.($pricing['type'] ?? 'flat').'",'.(isset($pricing['type']) && $pricing['type'] == 'range' ? '"range":{"min":"'.$pricing['min'].'","max":"'.$pricing['max'].'"}' : '"cost":"'.$pricing['cost'].'"').'},"extras":'.(isset($extras) ? '"'.$extras.'"' : 'null').',"tags":'.($tag ? '['.$tag.']' : 'null').'}',
            ];
        });
    }
}
