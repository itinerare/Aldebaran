<?php

namespace Tests\Feature;

use App\Models\Gallery\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryTagTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: TAGS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a couple tags for editing, etc. purposes
        $this->tag = Tag::factory()->create();
        $this->dataTag = Tag::factory()
            ->description()->hidden()->inactive()->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->text = $this->faker->unique()->domainWord();
    }

    /**
     * Test tag index access.
     */
    public function testCanGetTagIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags')
            ->assertStatus(200);
    }

    /**
     * Test tag create access.
     */
    public function testCanGetCreateTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/create')
            ->assertStatus(200);
    }

    /**
     * Test tag edit access.
     */
    public function testCanGetEditTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/edit/'.$this->tag->id)
            ->assertStatus(200);
    }

    /**
     * Test tag creation.
     *
     * @dataProvider tagProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     * @param bool $isActive
     */
    public function testCanPostCreateTag($hasData, $hasDescription, $isVisible, $isActive)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/create', [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
                'is_active'   => $isActive,
            ]);

        $this->assertDatabaseHas('tags', [
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
            'is_active'   => $isActive,
        ]);
    }

    /**
     * Test tag editing.
     *
     * @dataProvider tagProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     * @param bool $isActive
     */
    public function testCanPostEditTag($hasData, $hasDescription, $isVisible, $isActive)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/edit/'.($hasData ? $this->dataTag->id : $this->tag->id), [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
                'is_active'   => $isActive,
            ]);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'id'          => $hasData ? $this->dataTag->id : $this->tag->id,
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
            'is_active'   => $isActive,
        ]);
    }

    public function tagProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(4);
    }

    /**
     * Test tag delete access.
     */
    public function testCanGetDeleteTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/delete/'.$this->tag->id)
            ->assertStatus(200);
    }

    /**
     * Test tag deletion.
     */
    public function testCanPostDeleteTag()
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/delete/'.$this->tag->id);

        $this->assertDeleted($this->tag);
    }
}
