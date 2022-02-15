<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DataGalleryPieceImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PIECES/IMAGES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a piece to add an image for
        $this->piece = Piece::factory()->create();

        // Create images and test files
        $this->image = PieceImage::factory()->piece($this->piece->id)->create();
        $this->service = new GalleryService;
        $this->service->testImages($this->image);

        $this->dataImage = PieceImage::factory()
            ->piece($this->piece->id)->caption()
            ->primary()->hidden()->create();
        $this->service->testImages($this->dataImage);

        $this->file = UploadedFile::fake()->image('test_image.png');

        // Generate some test data
        $this->caption = $this->faker->unique()->domainWord();
    }

    protected function tearDown(): void
    {
        $this->service->testImages($this->image, false);
    }

    /**
     * Test image creation access.
     */
    public function testCanGetCreateImage()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/create/'.$this->piece->id)
            ->assertStatus(200);
    }

    /**
     * Test image editing access.
     */
    public function testCanGetEditImage()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/edit/'.$this->image->id)
            ->assertStatus(200);
    }

    /**
     * Test image editing.
     *
     * @dataProvider imageProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testCanPostEditImageInfo($hasData, $hasDescription, $isVisible, $isPrimary)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/edit/'.($hasData ? $this->dataImage->id : $this->image->id), [
                'description'      => $hasDescription ? $this->caption : null,
                'is_visible'       => $isVisible,
                'is_primary_image' => $isPrimary,
            ]);

        $this->assertDatabaseHas('piece_images', [
            'id'               => $hasData ? $this->dataImage->id : $this->image->id,
            'description'      => $hasDescription ? $this->caption : null,
            'is_visible'       => $isVisible,
            'is_primary_image' => $isPrimary,
        ]);
    }

    public function imageProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(4);
    }
}
