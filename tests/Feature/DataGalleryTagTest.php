<?php

namespace Tests\Feature;

use App\Models\Gallery\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryTagTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: TAGS
    *******************************************************************************/

    /**
     * Test tag index access.
     */
    public function testCanGetTagIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags')
            ->assertStatus(200);
    }

    /**
     * Test tag create access.
     */
    public function testCanGetCreateTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/create')
            ->assertStatus(200);
    }

    /**
     * Test tag edit access.
     */
    public function testCanGetEditTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/edit/'.Tag::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test tag creation.
     */
    public function testCanPostCreateTag()
    {
        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'name' => $data['name'],
        ]);
    }

    /**
     * Test tag editing.
     */
    public function testCanPostEditTag()
    {
        $tag = Tag::factory()->create();

        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/edit/'.$tag->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'id'   => $tag->id,
            'name' => $data['name'],
        ]);
    }

    /**
     * Test tag creation with a description.
     */
    public function testCanPostCreateTagWithDescription()
    {
        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'description' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test tag editing with a description.
     */
    public function testCanPostEditTagWithDescription()
    {
        $tag = Tag::factory()->create();

        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'description' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/edit/'.$tag->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'id'          => $tag->id,
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test tag creation with visibility.
     */
    public function testCanPostCreateTagVisibility()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test tag editing with visibility.
     */
    public function testCanPostEditTagVisibility()
    {
        $tag = Tag::factory()->hidden()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/edit/'.$tag->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'id'         => $tag->id,
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test tag creation with active status.
     */
    public function testCanPostCreateTagActivity()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_active'  => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'name'       => $data['name'],
            'is_active'  => 1,
        ]);
    }

    /**
     * Test tag editing with active status.
     */
    public function testCanPostEditTagActivity()
    {
        $tag = Tag::factory()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_active'  => 0,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/edit/'.$tag->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('tags', [
            'id'         => $tag->id,
            'name'       => $data['name'],
            'is_active'  => 0,
        ]);
    }

    /**
     * Test tag delete access.
     */
    public function testCanGetDeleteTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/delete/'.Tag::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test tag deletion.
     */
    public function testCanPostDeleteTag()
    {
        // Create a category to delete
        $tag = Tag::factory()->create();

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/tags/delete/'.$tag->id);

        // Check that there are fewer categories than before
        $this->assertDeleted($tag);
    }
}
