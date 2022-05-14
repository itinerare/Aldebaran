<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
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
    public function testGetProjectIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects')
            ->assertStatus(200);
    }

    /**
     * Test project create access.
     */
    public function testGetCreateProject()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects/create')
            ->assertStatus(200);
    }

    /**
     * Test project edit access.
     */
    public function testGetEditProject()
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
    public function testPostCreateProject($hasData, $hasDescription, $isVisible)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/create', [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
            ]);

        $response->assertSessionHasNoErrors();
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
    public function testPostEditProject($hasData, $hasDescription, $isVisible)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/edit/'.($hasData ? $this->dataProject->id : $this->project->id), [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
            ]);

        $response->assertSessionHasNoErrors();
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
    public function testGetDeleteProject()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/projects/delete/'.$this->project->id)
            ->assertStatus(200);
    }

    /**
     * Test project deletion.
     *
     * @dataProvider projectDeleteProvider
     *
     * @param bool $withPiece
     * @param bool $expected
     */
    public function testPostDeleteProject($withPiece, $expected)
    {
        if ($withPiece) {
            $piece = Piece::factory()->project($this->project->id)->create();
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/projects/delete/'.$this->project->id);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertModelMissing($this->project);
        } else {
            $response->assertSessionHasErrors();
            $this->assertModelExists($this->project);
        }
    }

    public function projectDeleteProvider()
    {
        return [
            'basic'      => [0, 1],
            'with piece' => [1, 0],
        ];
    }
}
