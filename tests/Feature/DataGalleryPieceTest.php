<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Program;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
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
    public function testCanGetPieceIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces')
            ->assertStatus(200);
    }

    /**
     * Test piece create access.
     */
    public function testCanGetCreatePiece()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/create')
            ->assertStatus(200);
    }

    /**
     * Test piece edit access.
     */
    public function testCanGetEditPiece()
    {
        $piece = Piece::factory()->create();

        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/edit/'.$piece->id)
            ->assertStatus(200);
    }

    /**
     * Test piece creation.
     */
    public function testCanPostCreatePiece()
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
    public function testCanPostEditPiece()
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
    public function testCanPostCreatePieceWithDescription()
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
    public function testCanPostEditPieceWithDescription()
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
     * Test piece editing with a removed description.
     */
    public function testCanPostEditPieceWithoutDescription()
    {
        $piece = Piece::factory()->description()->create();

        // Define some basic data
        $data = [
            'name'        => $this->faker->unique()->domainWord(),
            'project_id'  => $piece->project_id,
            'description' => null,
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
    public function testCanPostCreatePieceVisibility()
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
    public function testCanPostEditPieceVisibility()
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
    public function testCanPostCreatePieceWithTimestamp()
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
    public function testCanPostEditPieceWithTimestamp()
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
     * Test piece editing with a removed timestamp.
     */
    public function testCanPostEditPieceWithoutTimestamp()
    {
        $piece = Piece::factory()->timestamp()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'timestamp'  => null,
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
     * Test piece creation with tag.
     */
    public function testCanPostCreatePieceWithTag()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => Project::factory()->create()->id,
            'tags'       => [
                0 => Tag::factory()->create()->id,
            ],
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        $piece = Piece::where('name', $data['name'])->first();

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('piece_tags', [
            'piece_id' => $piece->id,
            'tag_id'   => $data['tags'][0],
        ]);
    }

    /**
     * Test piece editing with tag.
     */
    public function testCanPostEditPieceWithTag()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'tags'       => [
                0 => Tag::factory()->create()->id,
            ],
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('piece_tags', [
            'piece_id' => $piece->id,
            'tag_id'   => $data['tags'][0],
        ]);
    }

    /**
     * Test piece editing with removed tag.
     */
    public function testCanPostEditPieceWithoutTag()
    {
        $piece = Piece::factory()->create();
        $tag = PieceTag::factory()->piece($piece->id)->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'tags'       => null,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDeleted($tag);
    }

    /**
     * Test piece creation with program.
     */
    public function testCanPostCreatePieceWithProgram()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => Project::factory()->create()->id,
            'programs'   => [
                0 => Program::factory()->create()->id,
            ],
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', $data);

        $piece = Piece::where('name', $data['name'])->first();

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('piece_programs', [
            'piece_id'   => $piece->id,
            'program_id' => $data['programs'][0],
        ]);
    }

    /**
     * Test piece editing with program.
     */
    public function testCanPostEditPieceWithProgram()
    {
        $piece = Piece::factory()->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'programs'   => [
                0 => Program::factory()->create()->id,
            ],
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('piece_programs', [
            'piece_id'   => $piece->id,
            'program_id' => $data['programs'][0],
        ]);
    }

    /**
     * Test piece editing with removed program.
     */
    public function testCanPostEditPieceWithoutProgram()
    {
        $piece = Piece::factory()->create();
        $program = PieceProgram::factory()->piece($piece->id)->create();

        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'project_id' => $piece->project_id,
            'programs'   => null,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/edit/'.$piece->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDeleted($program);
    }

    /**
     * Test piece creation with good example.
     */
    public function testCanPostCreatePieceWithGoodExample()
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
    public function testCanPostEditPieceWithGoodExample()
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
    public function testCanGetDeletePiece()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/pieces/delete/'.Piece::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test piece deletion.
     */
    public function testCanPostDeletePiece()
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
