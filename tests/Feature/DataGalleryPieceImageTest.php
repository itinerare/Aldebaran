<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image;
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
        $this->service->testImages($this->dataImage, false);
    }

    /**
     * Test image creation access.
     */
    public function testGetCreateImage()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/create/'.$this->piece->id)
            ->assertStatus(200);
    }

    /**
     * Test image editing access.
     */
    public function testGetEditImage()
    {
        // This sidesteps casts not working correctly in tests,
        // for some reason
        $this->image->data = json_decode($this->image->data, true);
        $this->image->save();

        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/edit/'.$this->image->id)
            ->assertStatus(200);
    }

    /**
     * Test image creation.
     *
     * @dataProvider imageCreateProvider
     *
     * @param bool $withDescription
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostCreateImage($withDescription, $isVisible, $isPrimary)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/create', [
                'piece_id'           => $this->piece->id,
                'image'              => $this->file,
                'description'        => $withDescription ? $this->caption : null,
                'is_visible'         => $isVisible,
                'is_primary_image'   => $isPrimary,
                'watermark_scale'    => '.'.mt_rand(2, 7).'0',
                'watermark_opacity'  => mt_rand(0, 10).'0',
                'watermark_position' => 'bottom-right',
                'watermark_color'    => null,
                'text_watermark'     => null,
                'text_opacity'       => '.'.mt_rand(1, 9).'0',
            ]);

        $image = PieceImage::where('piece_id', $this->piece->id)->whereNotIn('id', [$this->image->id, $this->dataImage->id])->where('is_visible', $isVisible)->where('is_primary_image', $isPrimary)->first();

        $this->assertDatabaseHas('piece_images', [
            'description'      => $withDescription ? $this->caption : null,
            'is_visible'       => $isVisible,
            'is_primary_image' => $isPrimary,
        ]);

        // Check that the associated image files are present
        $this->
            assertTrue(File::exists($image->imagePath.'/'.$image->fullsizeFilename));
        $this->
            assertTrue(File::exists($image->imagePath.'/'.$image->imageFilename));
        $this->
            assertTrue(File::exists($image->imagePath.'/'.$image->thumbnailFilename));

        // Clean up test files
        $this->service->testImages($image, false);
    }

    public function imageCreateProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(3);
    }

    /**
     * Test image editing.
     * Largely checks associated info due to quirks of the test environment.
     *
     * @dataProvider imageEditProvider
     *
     * @param bool $withData
     * @param bool $withDescription
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostEditImage($withData, $withDescription, $isVisible, $isPrimary)
    {
        $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/edit/'.($withData ? $this->dataImage->id : $this->image->id), [
                'description'      => $withDescription ? $this->caption : null,
                'is_visible'       => $isVisible,
                'is_primary_image' => $isPrimary,
            ]);

        $this->assertDatabaseHas('piece_images', [
            'id'               => $withData ? $this->dataImage->id : $this->image->id,
            'description'      => $withDescription ? $this->caption : null,
            'is_visible'       => $isVisible,
            'is_primary_image' => $isPrimary,
        ]);
    }

    public function imageEditProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(4);
    }
}
