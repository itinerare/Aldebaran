<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
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

    protected function setUp(): void
    {
        parent::setUp();

        // Create a piece for editing, etc. purposes
        $this->piece = Piece::factory()->create();

        // Create another piece with full data, to test removal
        // of different information
        $this->dataPiece = Piece::factory()
            ->description()->timestamp()->goodExample()
            ->create();
        $this->tag = PieceTag::factory()->piece($this->dataPiece->id)->create();
        $this->program = PieceProgram::factory()->piece($this->dataPiece->id)->create();

        // Generate some test information
        $this->name = $this->faker()->unique()->domainWord();
        $this->text = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test piece index access.
     */
    public function testGetPieceIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces')
            ->assertStatus(200);
    }

    /**
     * Test piece create access.
     */
    public function testGetCreatePiece()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/create')
            ->assertStatus(200);
    }

    /**
     * Test piece edit access.
     */
    public function testGetEditPiece()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/edit/'.$this->piece->id)
            ->assertStatus(200);
    }

    /**
     * Test piece creation.
     *
     * @dataProvider pieceProvider
     *
     * @param bool $hasData
     * @param bool $description
     * @param bool $isVisible
     * @param bool $timestamp
     * @param bool $tag
     * @param bool $program
     * @param bool $goodExample
     */
    public function testPostCreatePiece($hasData, $description, $isVisible, $timestamp, $tag, $program, $goodExample)
    {
        $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/pieces/create', [
                'name'         => $this->name,
                'project_id'   => $this->piece->project_id,
                'description'  => $description ? $this->text : null,
                'is_visible'   => $isVisible,
                'timestamp'    => $timestamp ? Carbon::now() : null,
                'tags'         => $tag ? [0 => $this->tag->tag_id] : null,
                'programs'     => $program ? [0 => $this->program->program_id] : null,
                'good_example' => $goodExample,
            ]);

        $this->assertDatabaseHas('pieces', [
            'name'         => $this->name,
            'description'  => $description ? $this->text : null,
            'is_visible'   => $isVisible,
            'timestamp'    => $timestamp ? Carbon::now() : null,
            'good_example' => $goodExample,
        ]);

        // Get the created piece for proper checking
        $this->piece = Piece::where('name', $this->name)->first();

        if ($tag) {
            $this->assertDatabaseHas('piece_tags', [
                'piece_id' => $this->piece->id,
                'tag_id'   => $this->tag->tag_id,
            ]);
        }

        if ($program) {
            $this->assertDatabaseHas('piece_programs', [
                'piece_id'   => $this->piece->id,
                'program_id' => $this->program->program_id,
            ]);
        }
    }

    /**
     * Test piece editing.
     *
     * @dataProvider pieceProvider
     *
     * @param bool $hasData
     * @param bool $description
     * @param bool $isVisible
     * @param bool $timestamp
     * @param bool $tag
     * @param bool $program
     * @param bool $goodExample
     */
    public function testPostEditPiece($hasData, $description, $isVisible, $timestamp, $tag, $program, $goodExample)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/edit/'.($hasData ? $this->dataPiece->id : $this->piece->id), [
                'name'         => $this->name,
                'project_id'   => $hasData ? $this->dataPiece->project_id : $this->piece->project_id,
                'description'  => $description ? $this->text : null,
                'is_visible'   => $isVisible,
                'timestamp'    => $timestamp ? Carbon::now() : null,
                'tags'         => $tag ? [0 => $this->tag->tag_id] : null,
                'programs'     => $program ? [0 => $this->program->program_id] : null,
                'good_example' => $goodExample,
            ]);

        $this->assertDatabaseHas('pieces', [
            'id'           => $hasData ? $this->dataPiece->id : $this->piece->id,
            'name'         => $this->name,
            'description'  => $description ? $this->text : null,
            'is_visible'   => $isVisible,
            'timestamp'    => $timestamp ? Carbon::now() : null,
            'good_example' => $goodExample,
        ]);

        if ($hasData) {
            if (!$tag) {
                $this->assertDeleted($this->tag);
            }
            if (!$program) {
                $this->assertDeleted($this->program);
            }
        } else {
            if ($tag) {
                $this->assertDatabaseHas('piece_tags', [
                    'piece_id' => $this->piece->id,
                    'tag_id'   => $this->tag->tag_id,
                ]);
            }

            if ($program) {
                $this->assertDatabaseHas('piece_programs', [
                    'piece_id'   => $this->piece->id,
                    'program_id' => $this->program->program_id,
                ]);
            }
        }
    }

    public function pieceProvider()
    {
        // ($hasData, $description, $isVisible, $timestamp, $tag, $program, $goodExample)

        return [
            'basic'              => [0, 0, 1, 0, 0, 0, 0],
            'description'        => [0, 1, 1, 0, 0, 0, 0],
            'remove description' => [1, 0, 1, 0, 0, 0, 0],
            'visible'            => [0, 0, 1, 0, 0, 0, 0],
            'hidden'             => [1, 0, 0, 0, 0, 0, 0],
            'timestamp'          => [0, 0, 1, 1, 0, 0, 0],
            'remove timestamp'   => [1, 0, 1, 0, 0, 0, 0],
            'tag'                => [0, 0, 1, 0, 1, 0, 0],
            'remove tag'         => [1, 0, 1, 0, 0, 0, 0],
            'program'            => [0, 0, 1, 0, 0, 1, 0],
            'remove program'     => [1, 0, 1, 0, 0, 0, 0],
            'good example'       => [0, 0, 1, 0, 0, 0, 1],
            'bad example'        => [1, 0, 1, 0, 0, 0, 0],
        ];
    }

    /**
     * Test piece delete access.
     */
    public function testGetDeletePiece()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/delete/'.$this->piece->id)
            ->assertStatus(200);
    }

    /**
     * Test piece deletion.
     */
    public function testPostDeletePiece()
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/delete/'.$this->piece->id);

        $this->assertSoftDeleted($this->piece);
    }
}
