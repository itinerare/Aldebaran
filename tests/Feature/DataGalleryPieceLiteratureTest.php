<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceLiterature;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DataGalleryPieceLiteratureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PIECES/LITERATURES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Setup service for image handling purposes
        $this->service = new GalleryService;

        // Create a piece to add an image for
        $this->piece = Piece::factory()->create();

        // Create literatures
        $this->literature = PieceLiterature::factory()->piece($this->piece->id)->create();

        $this->dataLiterature = PieceLiterature::factory()
            ->piece($this->piece->id)->thumbnail()
            ->primary()->hidden()->create();
        $thumbnail = UploadedFile::fake()->image('test_thumbnail.png');
        $this->service->handleImage($thumbnail, $this->dataLiterature->imagePath, $this->dataLiterature->thumbnailFileName);

        // Set up a test file to test thumbnail handling
        $this->file = UploadedFile::fake()->image('test_image.png');

        // Generate some text
        $this->text = $this->faker->realText();
    }

    protected function tearDown(): void
    {
        if (File::exists($this->dataLiterature->imagePath.'/'.$this->dataLiterature->thumbnailFilename)) {
            // Remove test thumbnail file
            unlink($this->dataLiterature->imagePath.'/'.$this->dataLiterature->thumbnailFileName);
        }
    }

    /**
     * Test literature creation access.
     *
     * @dataProvider literatureCreateEditViewProvider
     *
     * @param bool $piece
     * @param int  $expected
     */
    public function testGetCreateLiterature($piece, $expected)
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/literatures/create/'.($piece ? $this->piece->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    /**
     * Test literature editing access.
     *
     * @dataProvider literatureCreateEditViewProvider
     *
     * @param bool $literature
     * @param int  $expected
     */
    public function testGetEditLiterature($literature, $expected)
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/literatures/edit/'.($literature ? $this->literature->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    public function literatureCreateEditViewProvider()
    {
        return [
            'valid'   => [1, 200],
            'invalid' => [0, 404],
        ];
    }

    /**
     * Test literature creation.
     *
     * @dataProvider literatureCreateProvider
     *
     * @param bool $withImage
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostCreateLiterature($withImage, $isVisible, $isPrimary)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/literatures/create', [
                'piece_id'    => $this->piece->id,
                'image'       => $withImage ? $this->file : null,
                'text'        => $this->text,
                'is_visible'  => $isVisible,
                'is_primary'  => $isPrimary,
            ]);

        $literature = PieceLiterature::where('piece_id', $this->piece->id)->whereNotIn('id', [$this->literature->id, $this->dataLiterature->id])->where('is_visible', $isVisible)->where('is_primary', $isPrimary)->first();

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('piece_literatures', [
            'text'       => $this->text,
            'is_visible' => $isVisible,
            'is_primary' => $isPrimary,
        ]);

        if ($withImage) {
            // Check that the hash and extension are set
            // This is a bit of a workaround since they're randomly generated
            $this->assertDatabaseMissing('piece_literatures', [
                'id'        => $literature->id,
                'hash'      => null,
                'extension' => null,
            ]);

            // Check that the associated image file is present
            $this->assertTrue(File::exists($literature->imagePath.'/'.$literature->thumbnailFilename));

            // Remove test thumbnail file
            unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
        }
    }

    public function literatureCreateProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(3);
    }

    /**
     * Test literature editing.
     *
     * @dataProvider literatureEditProvider
     *
     * @param bool $withData
     * @param bool $withImage
     * @param bool $removeImage
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostEditLiterature($withData, $withImage, $removeImage, $isVisible, $isPrimary)
    {
        // Specify which model will be used, for convenience
        $literature = $withData ? $this->dataLiterature : $this->literature;

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/literatures/edit/'.$literature->id, [
                'text'         => $this->text,
                'image'        => $withImage ? $this->file : null,
                'is_visible'   => $isVisible,
                'is_primary'   => $isPrimary,
                'remove_image' => $removeImage,
            ]);

        // Refresh the model, since specifying it above caches it
        $literature = $literature->refresh();

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('piece_literatures', [
            'id'         => $literature->id,
            'text'       => $this->text,
            'is_visible' => $isVisible,
            'is_primary' => $isPrimary,
        ]);

        if ($withImage || ($withData && !$removeImage)) {
            // Check for the image file
            $this->assertTrue(File::exists($literature->imagePath.'/'.$literature->thumbnailFilename));

            // and that the hash and extension are set
            $this->assertDatabaseMissing('piece_literatures', [
                'id'        => $literature->id,
                'hash'      => null,
                'extension' => null,
            ]);

            if (!$withData) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }
        } else {
            // Check that there is no hash or extension
            $this->assertDatabaseHas('piece_literatures', [
                'id'        => $literature->id,
                'hash'      => null,
                'extension' => null,
            ]);
        }
    }

    public function literatureEditProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(5);
    }

    /**
     * Test literature delete access.
     *
     * @dataProvider literatureDeleteProvider
     *
     * @param bool $literature
     * @param int  $expected
     */
    public function testGetDeleteLiterature($literature, $expected)
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/literatures/delete/'.($literature ? $this->literature->id : mt_rand(5, 50)))
            ->assertStatus($expected);
    }

    /**
     * Test literature deletion.
     *
     * @dataProvider literatureDeleteProvider
     *
     * @param bool $literature
     * @param bool $expected
     */
    public function testPostDeleteImage($literature, $expected)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/literatures/delete/'.($literature ? $this->literature->id : mt_rand(5, 50)));

        if ($expected == 200) {
            $response->assertSessionHasNoErrors();
            $this->assertModelMissing($this->literature);
        } else {
            $response->assertSessionHasErrors();
            $this->assertModelExists($this->literature);
        }
    }

    public function literatureDeleteProvider()
    {
        return [
            'valid'   => [1, 200],
            'invalid' => [0, 404],
        ];
    }
}
