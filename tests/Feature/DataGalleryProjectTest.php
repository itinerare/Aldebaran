<?php

namespace Tests\Feature;

use App\Models\Gallery\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PROJECTS
    *******************************************************************************/

    /**
     * Test project index access.
     */
    public function testCanGetProjectIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects')
            ->assertStatus(200);
    }

    /**
     * Test project create access.
     */
    public function testCanGetCreateProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/create')
            ->assertStatus(200);
    }

    /**
     * Test project edit access.
     */
    public function testCanGetEditProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/edit/'.Project::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test project creation.
     */
    public function testCanPostCreateProject()
    {
        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'name' => $data['name'],
        ]);
    }

    /**
     * Test project editing.
     */
    public function testCanPostEditProject()
    {
        $project = Project::factory()->create();

        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/edit/'.$project->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'id'   => $project->id,
            'name' => $data['name'],
        ]);
    }

    /**
     * Test project creation with a description.
     */
    public function testCanPostCreateProjectWithDescription()
    {
        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test project editing with a description.
     */
    public function testCanPostEditProjectWithDescription()
    {
        $project = Project::factory()->create();

        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/edit/'.$project->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'id'          => $project->id,
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test project editing with a removed description.
     */
    public function testCanPostEditProjectWithoutDescription()
    {
        $project = Project::factory()->description()->create();

        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'description' => null,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/edit/'.$project->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'id'          => $project->id,
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test project creation with visibility.
     */
    public function testCanPostCreateProjectVisibility()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test project editing with visibility.
     */
    public function testCanPostEditProjectVisibility()
    {
        $project = Project::factory()->hidden()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/edit/'.$project->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('projects', [
            'id'         => $project->id,
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test project delete access.
     */
    public function testCanGetDeleteProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/delete/'.Project::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test project deletion.
     */
    public function testCanPostDeleteProject()
    {
        // Create a category to delete
        $project = Project::factory()->create();

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/projects/delete/'.$project->id);

        // Check that there are fewer categories than before
        $this->assertDeleted($project);
    }
}
