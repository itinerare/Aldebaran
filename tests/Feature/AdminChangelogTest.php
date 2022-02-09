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
        $this->dataLog = Changelog::factory()
            ->title()->hidden()->create();

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
     * @param bool $hasData
     * @param bool $title
     * @param bool $isVisible
     */
    public function testCanPostCreateChangelog($hasData, $title, $isVisible)
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
     * @param bool $hasData
     * @param bool $title
     * @param bool $isVisible
     */
    public function testCanPostEditChangelog($hasData, $title, $isVisible)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/changelog/edit/'.($hasData ? $this->dataLog->id : $this->log->id), [
                'name'       => $title ? $this->title : null,
                'text'       => $this->text,
                'is_visible' => $isVisible,
            ]);

        $this->assertDatabaseHas('changelog_entries', [
            'id'         => $hasData ? $this->dataLog->id : $this->log->id,
            'name'       => $title ? $this->title : null,
            'text'       => $this->text,
            'is_visible' => $isVisible,
        ]);
    }

    public function changelogProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(3);
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
