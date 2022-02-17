<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionClass;
use App\Models\TextPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class DataCommissionClassTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Generate a text page for testing data removal
        $this->page = TextPage::factory()->create();

        // Create a commission class for editing, etc. purposes
        $this->class = CommissionClass::factory()->create();
        $this->dataClass = CommissionClass::factory()
            ->inactive()->testData($this->page)->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->pageName = $this->faker->unique()->domainWord();

        // Add site settings so that these functions will be accessible
        $this->artisan('add-site-settings');
    }

    /**
     * Test commission class index access.
     */
    public function testGetClassIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-classes')
            ->assertStatus(200);
    }

    /**
     * Test class create access.
     */
    public function testGetCreateClass()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-classes/create')
            ->assertStatus(200);
    }

    /**
     * Test class edit access.
     */
    public function testGetEditClass()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-classes/edit/'.$this->class->id)
            ->assertStatus(200);
    }

    /**
     * Test class creation.
     *
     * @dataProvider createClassProvider
     *
     * @param bool $isActive
     */
    public function testPostCreateClass($isActive)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-classes/create', [
                'name'      => $this->name,
                'is_active' => $isActive,
            ]);

        $this->assertDatabaseHas('commission_classes', [
            'name'      => $this->name,
            'is_active' => $isActive,
        ]);

        // Verify that default commission class text pages are created
        foreach (['tos', 'info'] as $page) {
            $this->assertDatabaseHas('text_pages', [
                'name' => $this->name.' Commission '.($page == 'tos' ? 'Terms of Service' : 'Info'),
                'key'  => Str::lower($this->name).$page,
            ]);
        }

        // Verify that commission class settings are created
        foreach (['comms_open', 'overall_slots', 'full', 'status'] as $setting) {
            $this->assertDatabaseHas('site_settings', [
                'key' => Str::lower($this->name).'_'.$setting,
            ]);
        }
    }

    public function createClassProvider()
    {
        return [
            'active'   => [1],
            'inactive' => [0],
        ];
    }

    /**
     * Test class editing.
     *
     * @dataProvider editClassProvider
     *
     * @param bool       $hasData
     * @param bool       $hasPage
     * @param array|null $fieldData
     * @param bool       $isActive
     */
    public function testPostEditClass($hasData, $hasPage, $fieldData, $isActive)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-classes/edit/'.($hasData ? $this->dataClass->id : $this->class->id), [
                'name'          => $this->name,
                'is_active'     => $isActive,
                'page_id'       => $hasPage ? [0 => null] + ($hasData ? [1 => $this->page->id] : []) : null,
                'page_title'    => $hasPage ? [0 => $this->pageName] + ($hasData ? [1 => $this->page->name] : []) : null,
                'page_key'      => $hasPage ? [0 => Str::lower($this->pageName)] + ($hasData ? [1 => $this->page->key] : []) : null,
                'field_key'     => isset($fieldData) ? [0 => 'test'] : null,
                'field_label'   => isset($fieldData) ? [0 => 'Test Field'] : null,
                'field_type'    => isset($fieldData) ? [0 => $fieldData[0]] : null,
                'field_rules'   => isset($fieldData) && $fieldData[1] ? [0 => 'required'] : null,
                'field_choices' => isset($fieldData) && $fieldData[2] ? [0 => 'option 1,option 2'] : null,
                'field_value'   => isset($fieldData) ? [0 => $fieldData[3]] : null,
                'field_help'    => isset($fieldData) ? [0 => $fieldData[4]] : null,
            ]);

        $page = TextPage::where('name', $this->pageName)->first();

        $this->assertDatabaseHas('commission_classes', [
            'id'        => $hasData ? $this->dataClass->id : $this->class->id,
            'name'      => $this->name,
            'is_active' => $isActive,
            'data'      => $hasPage || isset($fieldData) ?
            '{'.(isset($fieldData) ? '"fields":{"test":{"label":"Test Field","type":"'.$fieldData[0].'","rules":'.(isset($fieldData) && $fieldData[1] ? '"required"' : 'null').',"choices":'.(isset($fieldData) && $fieldData[2] ? '["option 1","option 2"]' : 'null').',"value":'.($fieldData[3] ? '"'.$fieldData[3].'"' : 'null').',"help":'.($fieldData[4] ? '"'.$fieldData[4].'"' : 'null').'}}' : '').
            ($hasPage ? '"pages":{"'.$page->id.'":{"key":"'.$page->key.'","title":"'.$page->name.'"}'.($hasData ? ',"'.$this->page->id.'":{"key":"'.$this->page->key.'","title":"'.$this->page->name.'"}' : '').'}' : '').'}' : null,
        ]);

        if ($hasPage) {
            $this->assertDatabaseHas('text_pages', [
                'name' => $this->pageName,
                'key'  => Str::lower($this->pageName),
            ]);
        }

        if ($hasData) {
            if (!$hasPage) {
                // If an existing page should be removed,
                // check that it is
                $this->assertDeleted($this->page);
            } elseif ($hasPage) {
                // Else check that it has been preserved
                $this->assertModelExists($this->page);
            }
        }
    }

    public function editClassProvider()
    {
        return [
            'basic'           => [0, 0, null, 1],
            'inactive'        => [0, 0, null, 0],
            'with page'       => [0, 1, null, 1],
            'additional page' => [1, 1, null, 1],

            // Field type tests
            // (string) type, (bool) rules, (bool) choices, value, (string) help
            'text field'            => [0, 0, ['text', 0, 0, null, null], 1],
            'text field with rule'  => [0, 0, ['text', 1, 0, null, null], 1],
            'text field with value' => [0, 0, ['text', 0, 0, 'test', null], 1],
            'text field with help'  => [0, 0, ['text', 0, 0, null, 'test'], 1],
            'textbox field'         => [0, 0, ['textarea', 0, 0, null, null], 1],
            'number field'          => [0, 0, ['number', 0, 0, null, null], 1],
            'checkbox field'        => [0, 0, ['checkbox', 0, 0, null, null], 1],
            'choose one field'      => [0, 0, ['choice', 0, 0, null, null], 1],
            'choose multiple field' => [0, 0, ['multiple', 0, 0, null, null], 1],

            // Does not work due to test environment issues:
            //'remove page' => [1, 0, null, 1],
        ];
    }

    /**
     * Test class delete access.
     */
    public function testGetDeleteClass()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/commission-classes/delete/'.$this->class->id)
            ->assertStatus(200);
    }

    /**
     * Test class deletion.
     */
    public function testPostDeleteClass()
    {
        $className = $this->class->name;
        $classSlug = $this->class->slug;

        $this
            ->actingAs($this->user)
            ->post('/admin/data/commission-classes/delete/'.$this->class->id);

        $this->assertDeleted($this->class);

        // Verify that default commission class text pages are deleted
        foreach (['tos', 'info'] as $page) {
            $this->assertDatabaseMissing('text_pages', [
                'name' => $className.' Commission '.($page == 'tos' ? 'Terms of Service' : 'Info'),
                'key'  => $classSlug.$page,
            ]);
        }

        // Verify that commission class settings are deleted
        foreach (['comms_open', 'overall_slots', 'full', 'status'] as $setting) {
            $this->assertDatabaseMissing('site_settings', [
                'key' => $classSlug.'_'.$setting,
            ]);
        }
    }
}
