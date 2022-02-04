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
    public function test_canGetTagIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags')
            ->assertStatus(200);
    }

    /**
     * Test tag create access.
     */
    public function test_canGetCreateTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/create')
            ->assertStatus(200);
    }

    /**
     * Test tag edit access.
     */
    public function test_canGetEditTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/edit/'.Tag::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test tag creation.
     */
    public function test_canPostCreateTag()
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
    public function test_canPostEditTag()
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
    public function test_canPostCreateTagWithDescription()
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
    public function test_canPostEditTagWithDescription()
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
    public function test_canPostCreateTagVisibility()
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
    public function test_canPostEditTagVisibility()
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
    public function test_canPostCreateTagActivity()
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
    public function test_canPostEditTagActivity()
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
    public function test_canGetDeleteTag()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/tags/delete/'.Tag::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test tag deletion.
     */
    public function test_canPostDeleteTag()
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
