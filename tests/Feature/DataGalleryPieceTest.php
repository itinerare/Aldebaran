<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryPieceTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PIECES
    *******************************************************************************/

    protected function setUp(): void {
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
     *
     * @dataProvider pieceIndexProvider
     *
     * @param bool       $withPiece
     * @param array|null $search
     */
    public function testGetPieceIndex($withPiece, $search) {
        // Remove testing pieces if not in use
        if (!$withPiece) {
            Piece::query()->delete();
        }

        $url = '/admin/data/pieces';
        // Set up urls for different search criteria / intended success
        if ($search) {
            $url = $url.'?'.$search[0].'=';
            switch ($search[0]) {
                case 'name':
                    $url = $url.($search[1] ? $this->piece->name : $this->faker->unique()->domainWord());
                    break;
                case 'project_id':
                    $url = $url.($search[1] ? $this->piece->project_id : Project::factory()->create()->id);
                    break;
                case 'tags%5B%5D':
                    $url = $url.($search[1] ? PieceTag::factory()->piece($this->piece)->create()->tag_id : Tag::factory()->create()->id);
            }
        }

        $response = $this->actingAs($this->user)
            ->get($url)
            ->assertStatus(200);

        $response->assertViewHas('pieces', function ($pieces) use ($search, $withPiece) {
            if ($withPiece && (!$search || $search[1])) {
                return $pieces->contains($this->piece);
            } else {
                return !$pieces->contains($this->piece);
            }
        });
    }

    public static function pieceIndexProvider() {
        return [
            'basic'                            => [0, null],
            'with piece'                       => [1, null],
            'search by name (successful)'      => [1, ['name', 1]],
            'search by name (unsuccessful)'    => [1, ['name', 0]],
            'search by project (successful)'   => [1, ['project_id', 1]],
            'search by project (unsuccessful)' => [1, ['project_id', 0]],
            'search by tag (successful)'       => [1, ['tags%5B%5D', 1]],
            'search by tag (unsuccessful)'     => [1, ['tags%5B%5D', 0]],
        ];
    }

    /**
     * Test piece create access.
     */
    public function testGetCreatePiece() {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/create')
            ->assertStatus(200);
    }

    /**
     * Test piece edit access.
     */
    public function testGetEditPiece() {
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
    public function testPostCreatePiece($hasData, $description, $isVisible, $timestamp, $tag, $program, $goodExample) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/create', [
                'name'         => $this->name,
                'project_id'   => $this->piece->project_id,
                'description'  => $description ? $this->text : null,
                'is_visible'   => $isVisible,
                'timestamp'    => $timestamp ? $this->piece->created_at : null,
                'tags'         => $tag ? [0 => $this->tag->tag_id] : null,
                'programs'     => $program ? [0 => $this->program->program_id] : null,
                'good_example' => $goodExample,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pieces', [
            'name'         => $this->name,
            'description'  => $description ? $this->text : null,
            'is_visible'   => $isVisible,
            'timestamp'    => $timestamp ? $this->piece->created_at : null,
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
    public function testPostEditPiece($hasData, $description, $isVisible, $timestamp, $tag, $program, $goodExample) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/edit/'.($hasData ? $this->dataPiece->id : $this->piece->id), [
                'name'         => $this->name,
                'project_id'   => $hasData ? $this->dataPiece->project_id : $this->piece->project_id,
                'description'  => $description ? $this->text : null,
                'is_visible'   => $isVisible,
                'timestamp'    => $timestamp ? $this->piece->created_at : null,
                'tags'         => $tag ? [0 => $this->tag->tag_id] : null,
                'programs'     => $program ? [0 => $this->program->program_id] : null,
                'good_example' => $goodExample,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pieces', [
            'id'           => $hasData ? $this->dataPiece->id : $this->piece->id,
            'name'         => $this->name,
            'description'  => $description ? $this->text : null,
            'is_visible'   => $isVisible,
            'timestamp'    => $timestamp ? $this->piece->created_at : null,
            'good_example' => $goodExample,
        ]);

        if ($hasData) {
            if (!$tag) {
                $this->assertModelMissing($this->tag);
            }
            if (!$program) {
                $this->assertModelMissing($this->program);
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

    public static function pieceProvider() {
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
    public function testGetDeletePiece() {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/delete/'.$this->piece->id)
            ->assertStatus(200);
    }

    /**
     * Test piece deletion.
     */
    public function testPostDeletePiece() {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/delete/'.$this->piece->id);

        $response->assertSessionHasNoErrors();
        $this->assertSoftDeleted($this->piece);
    }
}
