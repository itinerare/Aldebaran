<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataCommissionTypeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSION DATA: TYPES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test tag
        $this->tag = Tag::factory()->create();

        // Create a commission type for editing, etc. purposes
        $this->type = CommissionType::factory()->create();
        $this->dataType = CommissionType::factory()
            ->hidden()->testData(['type' => 'flat', 'cost' => 10], true, $this->tag->id, true, true, $this->faker->unique()->domainWord())->slots(5)->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->text = $this->faker->unique()->domainWord();

        // Enable commission components
        config(['aldebaran.settings.commissions.enabled' => 1]);
    }

    /**
     * Test commission type index access.
     *
     * @dataProvider typeIndexProvider
     *
     * @param bool       $withType
     * @param array|null $search
     */
    public function testGetTypeIndex($withType, $search)
    {
        // Remove testing types if not in use
        if (!$withType) {
            CommissionType::query()->delete();
        }

        $url = '/admin/data/commission-types';
        // Set up urls for different search criteria / intended success
        if ($withType && $search) {
            $url = $url.'?'.$search[0].'=';
            switch ($search[0]) {
                case 'name':
                    $url = $url.($search[1] ? $this->type->name : $this->faker->unique()->domainWord());
                    break;
                case 'category_id':
                    $url = $url.($search[1] ? $this->type->category_id : CommissionCategory::factory()->create()->id);
                    break;
            }
        }

        $response = $this->actingAs($this->user)
            ->get($url)
            ->assertStatus(200);

        $response->assertViewHas('types', function ($types) use ($search, $withType) {
            if ($withType && (!$search || $search[1])) {
                return $types->contains($this->type);
            } else {
                return !$types->contains($this->type);
            }
        });
    }

    public function typeIndexProvider()
    {
        return [
            'basic'                             => [0, null],
            'with type'                         => [1, null],
            'search by name (successful)'       => [1, ['name', 1]],
            'search by name (unsuccessful)'     => [1, ['name', 0]],
            'search by category (successful)'   => [1, ['category_id', 1]],
            'search by category (unsuccessful)' => [1, ['category_id', 0]],
        ];
    }

    /**
     * Test type create access.
     */
    public function testGetCreateType()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-types/create')
            ->assertStatus(200);
    }

    /**
     * Test type edit access.
     */
    public function testGetEditType()
    {
        // This sidesteps casts not working correctly in tests,
        // for some reason
        $this->type->data = json_decode($this->type->data, true);
        $this->type->save();

        $this->actingAs($this->user)
            ->get('/admin/data/commission-types/edit/'.$this->type->id)
            ->assertStatus(200);
    }

    /**
     * Test type creation.
     *
     * @dataProvider createTypeProvider
     *
     * @param bool     $withDescription
     * @param array    $pricing
     * @param bool     $withExtras
     * @param bool     $isActive
     * @param bool     $isVisible
     * @param int|null $slots
     * @param bool     $withTag
     * @param bool     $showExamples
     */
    public function testPostCreateType($withDescription, $pricing, $withExtras, $isActive, $isVisible, $slots, $withTag, $showExamples)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-types/create', [
                'name'          => $this->name,
                'category_id'   => $this->type->category_id,
                'description'   => $withDescription ? $this->text : null,
                'price_type'    => $pricing['type'],
                'flat_cost'     => $pricing['type'] == 'flat' ? $pricing['cost'] : null,
                'minimum_cost'  => $pricing['type'] == 'min' ? $pricing['cost'] : null,
                'rate'          => $pricing['type'] == 'rate' ? $pricing['cost'] : null,
                'cost_min'      => $pricing['type'] == 'range' ? $pricing['cost']['min'] : null,
                'cost_max'      => $pricing['type'] == 'range' ? $pricing['cost']['max'] : null,
                'extras'        => $withExtras ? $this->text : null,
                'is_active'     => $isActive,
                'is_visible'    => $isVisible,
                'availability'  => $slots,
                'tags'          => $withTag ? [0 => $this->tag->id] : null,
                'show_examples' => $showExamples,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('commission_types', [
            'category_id'   => $this->type->category_id,
            'name'          => $this->name,
            'description'   => $withDescription ? $this->text : null,
            'is_active'     => $isActive,
            'is_visible'    => $isVisible,
            'availability'  => $slots ?? 0,
            'show_examples' => $showExamples,
            'data'          => '{"include":{"class":0,"category":0},"pricing":{"type":"'.$pricing['type'].'",'.($pricing['type'] != 'range' ? '"cost":'.$pricing['cost'] : '"range":{"min":'.$pricing['cost']['min'].',"max":'.$pricing['cost']['max'].'}').'},"extras":'.($withExtras ? '"'.$this->text.'"' : 'null').',"tags":'.($withTag ? '['.$this->tag->id.']' : 'null').'}',
        ]);
    }

    public function createTypeProvider()
    {
        // $withDescription, $pricing, $withExtras, $isActive, $isVisible, $slots, $withTag, $showExamples
        return [
            'basic'            => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1],
            'with description' => [1, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1],
            'hidden'           => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 0, null, 0, 1],
            'inactive'         => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 0, 1, null, 0, 1],
            'with slots'       => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, mt_rand(1, 20), 0, 1],
            'with tag'         => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 1, 1],
            'no examples'      => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 0],

            // Different pricing types
            'flat cost'   => [1, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1],
            'range'       => [1, ['type' => 'range', 'cost' => ['min' => mt_rand(1, 50), 'max' => mt_rand(51, 100)]], 0, 1, 1, null, 0, 1],
            'minimum'     => [1, ['type' => 'min', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1],
            'hourly rate' => [1, ['type' => 'rate', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1],
        ];
    }

    /**
     * Test type editing.
     *
     * @dataProvider editTypeProvider
     *
     * @param bool       $withDescription
     * @param array      $pricing
     * @param bool       $withExtras
     * @param bool       $isActive
     * @param bool       $isVisible
     * @param int|null   $slots
     * @param bool       $withTag
     * @param bool       $showExamples
     * @param bool       $includeClass
     * @param bool       $includeCategory
     * @param array|null $fieldData
     */
    public function testPostEditType($withDescription, $pricing, $withExtras, $isActive, $isVisible, $slots, $withTag, $showExamples, $includeClass, $includeCategory, $fieldData)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-types/edit/'.$this->type->id, [
                'name'             => $this->name,
                'category_id'      => $this->type->category_id,
                'description'      => $withDescription ? $this->text : null,
                'price_type'       => $pricing['type'],
                'flat_cost'        => $pricing['type'] == 'flat' ? $pricing['cost'] : null,
                'minimum_cost'     => $pricing['type'] == 'min' ? $pricing['cost'] : null,
                'rate'             => $pricing['type'] == 'rate' ? $pricing['cost'] : null,
                'cost_min'         => $pricing['type'] == 'range' ? $pricing['cost']['min'] : null,
                'cost_max'         => $pricing['type'] == 'range' ? $pricing['cost']['max'] : null,
                'extras'           => $withExtras ? $this->text : null,
                'is_active'        => $isActive,
                'is_visible'       => $isVisible,
                'availability'     => $slots,
                'tags'             => $withTag ? [0 => $this->tag->id] : null,
                'show_examples'    => $showExamples,
                'include_class'    => $includeClass ?? null,
                'include_category' => $includeCategory ?? null,
                'field_key'        => isset($fieldData) ? [0 => 'test'] : null,
                'field_label'      => isset($fieldData) ? [0 => 'Test Field'] : null,
                'field_type'       => isset($fieldData) ? [0 => $fieldData[0]] : null,
                'field_rules'      => isset($fieldData) && $fieldData[1] ? [0 => 'required'] : null,
                'field_choices'    => isset($fieldData) && $fieldData[2] ? [0 => 'option 1,option 2'] : null,
                'field_value'      => isset($fieldData) ? [0 => $fieldData[3]] : null,
                'field_help'       => isset($fieldData) ? [0 => $fieldData[4]] : null,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('commission_types', [
            'id'            => $this->type->id,
            'category_id'   => $this->type->category_id,
            'name'          => $this->name,
            'description'   => $withDescription ? $this->text : null,
            'is_active'     => $isActive,
            'is_visible'    => $isVisible,
            'availability'  => $slots ?? 0,
            'show_examples' => $showExamples,
            'data'          => '{'.(isset($fieldData) ? '"fields":{"test":{"label":"Test Field","type":"'.$fieldData[0].'","rules":'.(isset($fieldData) && $fieldData[1] ? '"required"' : 'null').',"choices":'.(isset($fieldData) && $fieldData[2] ? '["option 1","option 2"]' : 'null').',"value":'.($fieldData[3] ? '"'.$fieldData[3].'"' : 'null').',"help":'.($fieldData[4] ? '"'.$fieldData[4].'"' : 'null').'}},' : '').'"include":{"class":'.$includeClass.',"category":'.$includeCategory.'},"pricing":{"type":"'.$pricing['type'].'",'.($pricing['type'] != 'range' ? '"cost":'.$pricing['cost'] : '"range":{"min":'.$pricing['cost']['min'].',"max":'.$pricing['cost']['max'].'}').'},"extras":'.($withExtras ? '"'.$this->text.'"' : 'null').',"tags":'.($withTag ? '['.$this->tag->id.']' : 'null').'}',
        ]);
    }

    public function editTypeProvider()
    {
        // $withDescription, $pricing, $withExtras, $isActive, $isVisible, $slots, $withTag, $showExamples, $includeClass, $includeCategory, $fieldData
        return [
            'basic'                      => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, null],

            'with description'           => [1, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, null],
            'hidden'                     => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 0, null, 0, 1, 0, 0, null],
            'inactive'                   => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 0, 1, null, 0, 1, 0, 0, null],
            'with slots'                 => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, mt_rand(1, 20), 0, 1, 0, 0, null],
            'with tag'                   => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 1, 1, 0, 0, null],
            'no examples'                => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 0, 0, 0, null],
            'include class'              => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 1, 0, null],
            'include category'           => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 1, null],
            'include class and category' => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 1, 1, null],

            // Different pricing types
            'flat cost'   => [1, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, null],
            'range'       => [1, ['type' => 'range', 'cost' => ['min' => mt_rand(1, 50), 'max' => mt_rand(51, 100)]], 0, 1, 1, null, 0, 1, 0, 0, null],
            'minimum'     => [1, ['type' => 'min', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, null],
            'hourly rate' => [1, ['type' => 'rate', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, null],

            // Field type tests
            // (string) type, (bool) rules, (bool) choices, value, (string) help
            'text field'            => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['text', 0, 0, null, null]],
            'text field with rule'  => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['text', 1, 0, null, null]],
            'text field with value' => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['text', 0, 0, 'test', null]],
            'text field with help'  => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['text', 0, 0, null, 'test']],
            'textbox field'         => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['textarea', 0, 0, null, null]],
            'number field'          => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['number', 0, 0, null, null]],
            'checkbox field'        => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['checkbox', 0, 0, null, null]],
            'choose one field'      => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['choice', 0, 0, null, null]],
            'choose multiple field' => [0, ['type' => 'flat', 'cost' => mt_rand(1, 100)], 0, 1, 1, null, 0, 1, 0, 0, ['multiple', 0, 0, null, null]],
        ];
    }

    /**
     * Test type delete access.
     */
    public function testGetDeleteType()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-types/delete/'.$this->type->id)
            ->assertStatus(200);
    }

    /**
     * Test type deletion.
     */
    public function testPostDeleteType()
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-types/delete/'.$this->type->id);

        $response->assertSessionHasNoErrors();
        $this->assertModelMissing($this->type);
    }
}
