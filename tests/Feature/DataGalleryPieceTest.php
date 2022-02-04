<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryPieceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PIECES
    *******************************************************************************/

    /**
     * Test piece index access.
     */
    public function test_canGetPieceIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces')
            ->assertStatus(200);
    }

    /**
     * Test piece create access.
     */
    public function test_canGetCreatePiece()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/create')
            ->assertStatus(200);
    }

    /**
     * Test piece edit access.
     */
    public function test_canGetEditPiece()
    {
        $piece = Piece::factory()->create();

        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/edit/'.$piece->id)
            ->assertStatus(200);
    }

    /**
     * Test piece creation.
     */
    public function test_canPostCreatePiece()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => Project::factory()->create()->id,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'name' => $data['name'],
        ]);
    }

    /**
     * Test piece editing.
     */
    public function test_canPostEditPiece()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'id'   => $piece->id,
            'name' => $data['name'],
        ]);
    }

    /**
     * Test piece creation with description.
     */
    public function test_canPostCreatePieceWithDescription()
    {
        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'project_id'  => Project::factory()->create()->id,
            'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test piece editing with description.
     */
    public function test_canPostEditPieceWithDescription()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'project_id'  => $piece->project_id,
            'description' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'id'          => $piece->id,
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Test piece creation with visibility.
     */
    public function test_canPostCreatePieceVisibility()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => Project::factory()->create()->id,
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test piece editing with visibility.
     */
    public function test_canPostEditPieceVisibility()
    {
        $piece = Piece::factory()->hidden()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'id'         => $piece->id,
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test piece creation with timestamp.
     */
    public function test_canPostCreatePieceWithTimestamp()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => Project::factory()->create()->id,
            'timestamp'  => Carbon::now(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'name'      => $data['name'],
            'timestamp' => $data['timestamp'],
        ]);
    }

    /**
     * Test piece editing with timestamp.
     */
    public function test_canPostEditPieceWithTimestamp()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'timestamp'  => Carbon::now(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'id'        => $piece->id,
            'name'      => $data['name'],
            'timestamp' => $data['timestamp'],
        ]);
    }

    /**
     * Test piece creation with good example.
     */
    public function test_canPostCreatePieceWithGoodExample()
    {
        // Define some basic data
        $data = [
            'name'         => $this->faker->unique()->domainWord(),
            'project_id'   => Project::factory()->create()->id,
            'good_example' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'name'         => $data['name'],
            'good_example' => 1,
        ]);
    }

    /**
     * Test piece editing with good example.
     */
    public function test_canPostEditPieceWithGoodExample()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'         => $this->faker->unique()->domainWord(),
            'project_id'   => $piece->project_id,
            'good_example' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('pieces', [
            'id'           => $piece->id,
            'name'         => $data['name'],
            'good_example' => 1,
        ]);
    }

    /**
     * Test piece delete access.
     */
    public function test_canGetDeletePiece()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/delete/'.Piece::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test piece deletion.
     */
    public function test_canPostDeletePiece()
    {
        // Create a category to delete
        $piece = Piece::factory()->create();

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/delete/'.$piece->id);

        // Check that there are fewer categories than before
        $this->assertSoftDeleted($piece);
    }
}
