<?php

namespace Tests\Feature;

use App\Models\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminChangelogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        CHANGELOG
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a changelog for editing, etc. purposes
        $this->log = Changelog::factory()->create();

        // Generate title and text values
        $this->title = $this->faker()->unique()->domainWord();
        $this->text = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test changelog index access.
     */
    public function testCanGetChangelogIndex()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/changelog')
            ->assertStatus(200);
    }

    /**
     * Test changelog create access.
     */
    public function testCanGetCreateChangelog()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/changelog/create')
            ->assertStatus(200);
    }

    /**
     * Test changelog edit access.
     */
    public function testCanGetEditChangelog()
    {
        $log = Changelog::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/changelog/edit/'.$log->id)
            ->assertStatus(200);
    }

    /**
     * Test changelog creation.
     *
     * @dataProvider changelogProvider
     *
     * @param bool $title
     * @param bool $isVisible
     */
    public function testCanPostCreateChangelog($title, $isVisible)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/changelog/create', [
                'name'       => $title ? $this->title : null,
                'text'       => $this->text,
                'is_visible' => $isVisible,
            ]);

        $this->assertDatabaseHas('changelog_entries', [
            'name'       => null,
            'name'       => $title ? $this->title : null,
            'text'       => $this->text,
            'is_visible' => $isVisible,
        ]);
    }

    /**
     * Test changelog editing.
     *
     * @dataProvider changelogProvider
     *
     * @param bool $title
     * @param bool $isVisible
     */
    public function testCanPostEditChangelog($title, $isVisible)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/changelog/edit/'.$this->log->id, [
                'name'       => $title ? $this->title : null,
                'text'       => $this->text,
                'is_visible' => $isVisible,
            ]);

        $this->assertDatabaseHas('changelog_entries', [
            'id'         => $this->log->id,
            'name'       => $title ? $this->title : null,
            'text'       => $this->text,
            'is_visible' => $isVisible,
        ]);
    }

    public function changelogProvider()
    {
        return [
            'minimal'           => [0, 1],
            'title'             => [1, 1],
            'visible'           => [0, 1],
            'hidden'            => [0, 0],
            'hidden with title' => [1, 0],
        ];
    }

    /**
     * Test changelog editing, removing the a title.
     */
    public function testCanPostEditChangelogWithoutTitle()
    {
        $log = Changelog::factory()->title()->create();

        $this
            ->actingAs($this->user)
            ->post('/admin/changelog/edit/'.$log->id, [
                'name'       => null,
                'text'       => $this->text,
                'is_visible' => 1,
            ]);

        $this->assertDatabaseHas('changelog_entries', [
            'id'         => $log->id,
            'name'       => null,
            'text'       => $this->text,
            'is_visible' => 1,
        ]);
    }

    /**
     * Test changelog delete access.
     */
    public function testCanGetDeleteChangelog()
    {
        $this->actingAs($this->user)
            ->get('/admin/changelog/delete/'.$this->log->id)
            ->assertStatus(200);
    }

    /**
     * Test changelog deletion.
     */
    public function testCanPostDeleteChangelog()
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/changelog/delete/'.$this->log->id);

        $this->assertDeleted($this->log);
    }
}
