<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataCommissionCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a commission category for editing, etc. purposes
        $this->category = CommissionCategory::factory()->create();
        $this->dataCategory = CommissionCategory::factory()
            ->inactive()->testData(true)->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        //$this->pageName = $this->faker->unique()->domainWord();

        // Add site settings so that these functions will be accessible
        $this->artisan('add-site-settings');
    }

    /**
     * Test commission category index access.
     */
    public function testGetCategoryIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-categories')
            ->assertStatus(200);
    }

    /**
     * Test category create access.
     */
    public function testGetCreateCategory()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-categories/create')
            ->assertStatus(200);
    }

    /**
     * Test category edit access.
     */
    public function testGetEditCategory()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-categories/edit/'.$this->category->id)
            ->assertStatus(200);
    }

    /**
     * Test category creation.
     *
     * @dataProvider createCategoryProvider
     *
     * @param bool $isActive
     */
    public function testPostCreateCategory($isActive)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-categories/create', [
                'name'      => $this->name,
                'is_active' => $isActive,
                'class_id'  => CommissionClass::factory()->create()->id,
            ]);

        $this->assertDatabaseHas('commission_categories', [
            'name'      => $this->name,
            'is_active' => $isActive,
        ]);
    }

    public function createCategoryProvider()
    {
        return [
            'active'   => [1],
            'inactive' => [0],
        ];
    }

    /**
     * Test category editing.
     *
     * @dataProvider editCategoryProvider
     *
     * @param bool       $hasData
     * @param array|null $fieldData
     * @param bool       $isActive
     * @param bool       $include
     */
    public function testPostEditCategory($hasData, $fieldData, $isActive, $include)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-categories/edit/'.($hasData ? $this->dataCategory->id : $this->category->id), [
                'name'          => $this->name,
                'is_active'     => $isActive,
                'class_id'      => $hasData ? $this->dataCategory->class_id : $this->category->class_id,
                'include_class' => $include ?? null,
                'field_key'     => isset($fieldData) ? [0 => 'test'] : null,
                'field_label'   => isset($fieldData) ? [0 => 'Test Field'] : null,
                'field_type'    => isset($fieldData) ? [0 => $fieldData[0]] : null,
                'field_rules'   => isset($fieldData) && $fieldData[1] ? [0 => 'required'] : null,
                'field_choices' => isset($fieldData) && $fieldData[2] ? [0 => 'option 1,option 2'] : null,
                'field_value'   => isset($fieldData) ? [0 => $fieldData[3]] : null,
                'field_help'    => isset($fieldData) ? [0 => $fieldData[4]] : null,
            ]);

        $this->assertDatabaseHas('commission_categories', [
            'id'        => $hasData ? $this->dataCategory->id : $this->category->id,
            'name'      => $this->name,
            'is_active' => $isActive,
            'data'      => isset($fieldData) ?
            '{"fields":{"test":{"label":"Test Field","type":"'.$fieldData[0].'","rules":'.(isset($fieldData) && $fieldData[1] ? '"required"' : 'null').',"choices":'.(isset($fieldData) && $fieldData[2] ? '["option 1","option 2"]' : 'null').',"value":'.($fieldData[3] ? '"'.$fieldData[3].'"' : 'null').',"help":'.($fieldData[4] ? '"'.$fieldData[4].'"' : 'null').'}},"include":{"class":'.$include.'}}' : '{"include":{"class":'.$include.'}}',
        ]);
    }

    public function editCategoryProvider()
    {
        return [
            'basic'                => [0, null, 1, 0],
            'inactive'             => [0, null, 0, 0],
            'include class fields' => [0, null, 1, 1],
            'basic with data'      => [1, null, 1, 0],
            'inactive with data'   => [1, null, 0, 0],
            'include with data'    => [1, null, 1, 1],

            // Field type tests
            // (string) type, (bool) rules, (bool) choices, value, (string) help
            'text field'            => [0, ['text', 0, 0, null, null], 1, 0],
            'text field with rule'  => [0, ['text', 1, 0, null, null], 1, 0],
            'text field with value' => [0, ['text', 0, 0, 'test', null], 1, 0],
            'text field with help'  => [0, ['text', 0, 0, null, 'test'], 1, 0],
            'textbox field'         => [0, ['textarea', 0, 0, null, null], 1, 0],
            'number field'          => [0, ['number', 0, 0, null, null], 1, 0],
            'checkbox field'        => [0, ['checkbox', 0, 0, null, null], 1, 0],
            'choose one field'      => [0, ['choice', 0, 0, null, null], 1, 0],
            'choose multiple field' => [0, ['multiple', 0, 0, null, null], 1, 0],
        ];
    }

    /**
     * Test category delete access.
     */
    public function testGetDeleteCategory()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-categories/delete/'.$this->category->id)
            ->assertStatus(200);
    }

    /**
     * Test category deletion.
     */
    public function testPostDeleteCategory()
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-categories/delete/'.$this->category->id);

        $this->assertDeleted($this->category);
    }
}
