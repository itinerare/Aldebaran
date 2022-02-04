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
    public function test_canGetProjectIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects')
            ->assertStatus(200);
    }

    /**
     * Test project create access.
     */
    public function test_canGetCreateProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/create')
            ->assertStatus(200);
    }

    /**
     * Test project edit access.
     */
    public function test_canGetEditProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/edit/'.Project::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test project creation.
     */
    public function test_canPostCreateProject()
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
    public function test_canPostEditProject()
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
    public function test_canPostCreateProjectWithDescription()
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
    public function test_canPostEditProjectWithDescription()
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
    public function test_canPostEditProjectWithoutDescription()
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
    public function test_canPostCreateProjectVisibility()
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
    public function test_canPostEditProjectVisibility()
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
    public function test_canGetDeleteProject()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/projects/delete/'.Project::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test project deletion.
     */
    public function test_canPostDeleteProject()
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
