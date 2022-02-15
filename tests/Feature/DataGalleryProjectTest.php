<?php

namespace Tests\Feature;

use App\Models\Gallery\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PROJECTS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a project for editing, etc. purposes
        $this->project = Project::factory()->create();
        $this->dataProject = Project::factory()
            ->description()->hidden()->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->text = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test project index access.
     */
    public function testCanGetProjectIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects')
            ->assertStatus(200);
    }

    /**
     * Test project create access.
     */
    public function testCanGetCreateProject()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects/create')
            ->assertStatus(200);
    }

    /**
     * Test project edit access.
     */
    public function testCanGetEditProject()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects/edit/'.$this->project->id)
            ->assertStatus(200);
    }

    /**
     * Test project creation.
     *
     * @dataProvider projectProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     */
    public function testCanPostCreateProject($hasData, $hasDescription, $isVisible)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/create', [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
            ]);

        $this->assertDatabaseHas('projects', [
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
        ]);
    }

    /**
     * Test project editing.
     *
     * @dataProvider projectProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     */
    public function testCanPostEditProject($hasData, $hasDescription, $isVisible)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/edit/'.($hasData ? $this->dataProject->id : $this->project->id), [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
            ]);

        $this->assertDatabaseHas('projects', [
            'id'          => $hasData ? $this->dataProject->id : $this->project->id,
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
        ]);
    }

    public function projectProvider()
    {
        return $this->booleanSequences(3);
    }

    /**
     * Test project delete access.
     */
    public function testCanGetDeleteProject()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects/delete/'.$this->project->id)
            ->assertStatus(200);
    }

    /**
     * Test project deletion.
     */
    public function testCanPostDeleteProject()
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/delete/'.$this->project->id);

        $this->assertDeleted($this->project);
    }
}
