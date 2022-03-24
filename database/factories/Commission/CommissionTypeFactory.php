<?php

namespace Database\Factories\Commission;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommissionType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $category = CommissionCategory::factory()->create();

        return [
            //
            'category_id'  => $category->id,
            'name'         => $this->faker->unique()->domainWord(),
            'key'          => randomString(10),
            'availability' => 0,
            'is_visible'   => 1,
            'data'         => '{"pricing":{"type":"flat","cost":"10"},"extras":null,"show_examples":"1","tags":null}',
            'sort'         => 0,
        ];
    }

    /**
     * Generate a type for a specific category.
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function category($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'category_id' => $id,
            ];
        });
    }

    /**
     * Generate a hidden type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hidden()
    {
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
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function slots($slots)
    {
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
     * @param bool        $showExamples
     * @param int         $tag
     * @param bool        $includeClass
     * @param bool        $includeCategory
     * @param string|null $extras
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function testData($pricing, $showExamples, $tag = null, $includeClass = 0, $includeCategory = 0, $extras = null)
    {
        return $this->state(function (array $attributes) use ($pricing, $extras, $showExamples, $tag, $includeClass, $includeCategory) {
            return [
                'data' => '{"fields":{"test":{"label":"Test","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":'.($includeClass ? 1 : 0).',"category":'.($includeCategory ? 1 : 0).'},"pricing":{"type":"'.(isset($pricing['type']) ? $pricing['type'] : 'flat').'",'.(isset($pricing['type']) && $pricing['type'] == 'range' ? '"range":{"min":"'.$pricing['min'].'","max":"'.$pricing['max'].'"}' : '"cost":"'.$pricing['cost'].'"').'},"extras":'.(isset($extras) ? '"'.$extras.'"' : 'null').',"show_examples":"'.($showExamples ? 1 : 0).'","tags":'.($tag ? '['.$tag.']' : 'null').'}',
            ];
        });
    }
}
