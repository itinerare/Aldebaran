<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\Program;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DataGalleryProgramTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PROGRAMS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Create a program for editing, etc. purposes
        $this->program = Program::factory()->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->file = UploadedFile::fake()->image('test_image.png');
    }

    /**
     * Test program index access.
     */
    public function testGetProgramIndex() {
        $this->actingAs($this->user)
            ->get('/admin/data/programs')
            ->assertStatus(200);
    }

    /**
     * Test program create access.
     */
    public function testGetCreateProgram() {
        $this->actingAs($this->user)
            ->get('/admin/data/programs/create')
            ->assertStatus(200);
    }

    /**
     * Test program edit access.
     */
    public function testGetEditProgram() {
        $this->actingAs($this->user)
            ->get('/admin/data/programs/edit/'.$this->program->id)
            ->assertStatus(200);
    }

    /**
     * Test program creation.
     *
     * @dataProvider programCreateProvider
     *
     * @param bool $image
     * @param bool $isVisible
     */
    public function testPostCreateProgram($image, $isVisible) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/programs/create', [
                'name'       => $this->name,
                'image'      => $image ? $this->file : null,
                'is_visible' => $isVisible,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('programs', [
            'name'       => $this->name,
            'has_image'  => $image,
            'is_visible' => $isVisible,
        ]);

        if ($image) {
            // Locate the created program to properly check for its image
            // and perform cleanup after the fact
            $this->program = Program::where('name', $this->name)->first();

            $this->assertTrue(File::exists($this->program->imagePath.'/'.$this->program->imageFileName));
            unlink($this->program->imagePath.'/'.$this->program->imageFileName);
        }
    }

    public static function programCreateProvider() {
        return [
            'hidden'              => [0, 0],
            'visible'             => [0, 1],
            'with image, hidden'  => [1, 0],
            'with image, visible' => [1, 1],
        ];
    }

    /**
     * Test program editing.
     *
     * @dataProvider programEditProvider
     *
     * @param bool $hasImage
     * @param bool $image
     * @param bool $removeImage
     * @param bool $isVisible
     */
    public function testPostEditProgram($hasImage, $image, $removeImage, $isVisible) {
        if ($hasImage) {
            (new GalleryService)->handleImage(UploadedFile::fake()->image('alt_test_image.png'), $this->program->imagePath, $this->program->imageFileName);
            $this->program->update(['has_image' => 1]);
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/programs/edit/'.$this->program->id, [
                'name'         => $this->name,
                'image'        => $image ? $this->file : null,
                'is_visible'   => $isVisible,
                'remove_image' => $removeImage,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('programs', [
            'id'         => $this->program->id,
            'name'       => $this->name,
            'has_image'  => ($hasImage && !$removeImage) || $image ? 1 : 0,
            'is_visible' => $isVisible,
        ]);

        if ($image) {
            $this->assertTrue(File::exists($this->program->imagePath.'/'.$this->program->imageFileName));
            unlink($this->program->imagePath.'/'.$this->program->imageFileName);
        } elseif ($removeImage) {
            // Check that the file is not present
            $this->assertFalse(File::exists($this->program->imagePath.'/'.$this->program->imageFileName));
        }
    }

    public static function programEditProvider() {
        return [
            'hidden'                         => [0, 0, 0, 0],
            'visible'                        => [0, 0, 0, 1],
            'remove image, hidden'           => [0, 0, 1, 0],
            'remove image, visible'          => [0, 0, 1, 1],
            'with image, hidden'             => [0, 1, 0, 0],
            'with image, visible'            => [0, 1, 0, 1],
            'with image+remove, hidden'      => [0, 1, 1, 0],
            'with image+remove, visible'     => [0, 1, 1, 1],
            'has image, hidden'              => [1, 0, 0, 0],
            'has image, visible'             => [1, 0, 0, 1],
            'has image+remove, hidden'       => [1, 0, 1, 0],
            'has image+remove, visible'      => [1, 0, 1, 1],
            'with+has image, hidden'         => [1, 1, 0, 0],
            'with+has image, visible'        => [1, 1, 0, 1],
            'with+has image+remove, hidden'  => [1, 1, 1, 0],
            'with+has image+remove, visible' => [1, 1, 1, 1],
        ];
    }

    /**
     * Test program delete access.
     */
    public function testGetDeleteProgram() {
        $this->actingAs($this->user)
            ->get('/admin/data/programs/delete/'.$this->program->id)
            ->assertStatus(200);
    }

    /**
     * Test program deletion.
     *
     * @dataProvider programDeleteProvider
     *
     * @param bool $withPiece
     * @param bool $expected
     */
    public function testPostDeleteProgram($withPiece, $expected) {
        if ($withPiece) {
            $piece = Piece::factory()->create();
            PieceProgram::factory()
                ->piece($piece->id)->program($this->program->id)
                ->create();
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/programs/delete/'.$this->program->id);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertModelMissing($this->program);
        } else {
            $response->assertSessionHasErrors();
            $this->assertModelExists($this->program);
        }
    }

    public static function programDeleteProvider() {
        return [
            'basic'      => [0, 1],
            'with piece' => [1, 0],
        ];
    }
}
