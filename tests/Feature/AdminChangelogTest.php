<?php

namespace Tests\Feature;

use App\Models\Changelog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminChangelogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        CHANGELOG
    *******************************************************************************/

    /**
     * Test changelog index access.
     */
    public function testCanGetChangelogIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/changelog')
            ->assertStatus(200);
    }

    /**
     * Test changelog create access.
     */
    public function testCanGetCreateChangelog()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/changelog/create')
            ->assertStatus(200);
    }

    /**
     * Test changelog edit access.
     */
    public function testCanGetEditChangelog()
    {
        $log = Changelog::factory()->create();

        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/changelog/edit/'.$log->id)
            ->assertStatus(200);
    }

    /**
     * Test changelog creation.
     */
    public function testCanPostCreateChangelog()
    {
        // Define some basic data
        $data = [
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'name' => null,
            'text' => $data['text'],
        ]);
    }

    /**
     * Test changelog editing.
     */
    public function testCanPostEditChangelog()
    {
        $log = Changelog::factory()->create();

        // Define some basic data
        $data = [
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/edit/'.$log->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'id'   => $log->id,
            'name' => null,
            'text' => $data['text'],
        ]);
    }

    /**
     * Test changelog creation with title.
     */
    public function testCanPostCreateChangelogWithTitle()
    {
        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'name' => $data['name'],
            'text' => $data['text'],
        ]);
    }

    /**
     * Test changelog editing with title.
     */
    public function testCanPostEditChangelogWithTitle()
    {
        $log = Changelog::factory()->create();

        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/edit/'.$log->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'id'   => $log->id,
            'name' => $data['name'],
            'text' => $data['text'],
        ]);
    }

    /**
     * Test changelog editing with a removed title.
     */
    public function testCanPostEditChangelogWithoutTitle()
    {
        $log = Changelog::factory()->title()->create();

        // Define some basic data
        $data = [
            'name' => null,
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/edit/'.$log->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'id'   => $log->id,
            'name' => $data['name'],
            'text' => $data['text'],
        ]);
    }

    /**
     * Test changelog creation with visibility.
     */
    public function testCanPostCreateChangelogVisibility()
    {
        // Define some basic data
        $data = [
            'text'       => '<p>'.$this->faker->unique()->domainWord().'</p>',
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'text'       => $data['text'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test changelog editing with visibility.
     */
    public function testCanPostEditChangelogVisibility()
    {
        $log = Changelog::factory()->hidden()->create();

        // Define some basic data
        $data = [
            'text'       => '<p>'.$this->faker->unique()->domainWord().'</p>',
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/edit/'.$log->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('changelog_entries', [
            'id'         => $log->id,
            'text'       => $data['text'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test changelog delete access.
     */
    public function testCanGetDeleteChangelog()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/changelog/delete/'.Changelog::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test changelog deletion.
     */
    public function testCanPostDeleteChangelog()
    {
        // Create a category to delete
        $log = Changelog::factory()->create();

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/changelog/delete/'.$log->id);

        // Check that there are fewer categories than before
        $this->assertDeleted($log);
    }
}
