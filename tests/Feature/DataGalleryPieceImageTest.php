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

class DataGalleryPieceImageTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PIECES/IMAGES
    *******************************************************************************/

    protected function setUp(): void {
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

    protected function tearDown(): void {
        $this->service->testImages($this->image, false);
        $this->service->testImages($this->dataImage, false);
    }

    /**
     * Test image creation access.
     *
     * @dataProvider imageCreateEditViewProvider
     *
     * @param bool $piece
     * @param int  $expected
     */
    public function testGetCreateImage($piece, $expected) {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/create/'.($piece ? $this->piece->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    /**
     * Test image editing access.
     *
     * @dataProvider imageCreateEditViewProvider
     *
     * @param bool $image
     * @param int  $expected
     */
    public function testGetEditImage($image, $expected) {
        // This sidesteps casts not working correctly in tests,
        // for some reason
        $this->image->data = json_decode($this->image->data, true);
        $this->image->save();

        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/edit/'.($image ? $this->image->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    public static function imageCreateEditViewProvider() {
        return [
            'valid'   => [1, 200],
            'invalid' => [0, 404],
        ];
    }

    /**
     * Test image view access.
     *
     * @dataProvider imageViewProvider
     *
     * @param bool   $image
     * @param string $type
     * @param int    $expected
     */
    public function testGetViewImage($image, $type, $expected) {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/view/'.($image ? $this->image->id : mt_rand(5, 10)).'/'.$type)
            ->assertStatus($expected);
    }

    public static function imageViewProvider() {
        return [
            'valid full'      => [1, 'full', 302],
            'valid display'   => [1, 'display', 200],
            'valid thumb'     => [1, 'thumb', 200],
            'invalid full'    => [0, 'full', 404],
            'invalid display' => [0, 'display', 404],
            'invalid thumb'   => [0, 'thumb', 404],
        ];
    }

    /**
     * Test image creation.
     *
     * @dataProvider imageCreateProvider
     *
     * @param bool $withDescription
     * @param bool $withAltText
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostCreateImage($withDescription, $withAltText, $isVisible, $isPrimary) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/create', [
                'piece_id'           => $this->piece->id,
                'image'              => $this->file,
                'description'        => $withDescription ? $this->caption : null,
                'alt_text'           => $withAltText ? $this->caption : null,
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

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('piece_images', [
            'description'       => $withDescription ? $this->caption : null,
            'is_visible'        => $isVisible,
            'is_primary_image'  => $isPrimary,
            'extension'         => 'png',
            'display_extension' => config('aldebaran.settings.image_formats.display', 'png'),
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

    public static function imageCreateProvider() {
        return [
            'hidden'                          => [0, 0, 0, 0],
            'hidden, primary'                 => [0, 0, 0, 1],
            'visible'                         => [0, 0, 1, 0],
            'visible, primary'                => [0, 0, 1, 1],
            'alt text, hidden'                => [0, 1, 0, 0],
            'alt text, primary, hidden'       => [0, 1, 0, 1],
            'alt text, visible'               => [0, 1, 1, 0],
            'alt text, primary, visible'      => [0, 1, 1, 1],
            'desc, hidden'                    => [1, 0, 0, 0],
            'desc, primary, hidden'           => [1, 0, 0, 1],
            'desc, visible'                   => [1, 0, 1, 0],
            'desc, primary, visible'          => [1, 0, 1, 1],
            'desc, alt text, hidden'          => [1, 1, 0, 0],
            'desc, alt text, primary, hidden' => [1, 1, 0, 1],
            'desc, alt text, visible'         => [1, 1, 1, 0],
            'everything'                      => [1, 1, 1, 1],
        ];
    }

    /**
     * Test image editing.
     * Largely checks associated info due to quirks of the test environment.
     *
     * @dataProvider imageEditProvider
     *
     * @param bool $withData
     * @param bool $withDescription
     * @param bool $withAltText
     * @param bool $isVisible
     * @param bool $isPrimary
     */
    public function testPostEditImage($withData, $withDescription, $withAltText, $isVisible, $isPrimary) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/edit/'.($withData ? $this->dataImage->id : $this->image->id), [
                'description'      => $withDescription ? $this->caption : null,
                'alt_text'         => $withAltText ? $this->caption : null,
                'is_visible'       => $isVisible,
                'is_primary_image' => $isPrimary,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('piece_images', [
            'id'               => $withData ? $this->dataImage->id : $this->image->id,
            'description'      => $withDescription ? $this->caption : null,
            'is_visible'       => $isVisible,
            'is_primary_image' => $isPrimary,
        ]);
    }

    public static function imageEditProvider() {
        return [
            'hidden'                                => [0, 0, 0, 0, 0],
            'hidden, primary'                       => [0, 0, 0, 0, 1],
            'visible'                               => [0, 0, 0, 1, 0],
            'visible, primary'                      => [0, 0, 0, 1, 1],
            'alt text, hidden'                      => [0, 0, 1, 0, 0],
            'alt text, primary'                     => [0, 0, 1, 0, 1],
            'alt text, visible'                     => [0, 0, 1, 1, 0],
            'alt text, primary, visible'            => [0, 0, 1, 1, 1],
            'desc, hidden'                          => [0, 1, 0, 0, 0],
            'desc, primary, hidden'                 => [0, 1, 0, 0, 1],
            'desc, visible'                         => [0, 1, 0, 1, 0],
            'desc, primary, visible'                => [0, 1, 0, 1, 1],
            'desc, alt text, hidden'                => [0, 1, 1, 0, 0],
            'desc, alt text, hidden, primary'       => [0, 1, 1, 0, 1],
            'desc, alt text, visible'               => [0, 1, 1, 1, 0],
            'desc, alt text, visible, primary'      => [0, 1, 1, 1, 1],
            'data, hidden'                          => [1, 0, 0, 0, 0],
            'data, primary, hidden'                 => [1, 0, 0, 0, 1],
            'data, visible'                         => [1, 0, 0, 1, 0],
            'data, visible, primary'                => [1, 0, 0, 1, 1],
            'data, alt text, hidden'                => [1, 0, 1, 0, 0],
            'data, alt text, primary, hidden'       => [1, 0, 1, 0, 1],
            'data, alt text, visible'               => [1, 0, 1, 1, 0],
            'data, alt text, primary, visible'      => [1, 0, 1, 1, 1],
            'data, desc, hidden'                    => [1, 1, 0, 0, 0],
            'data, desc, primary, hidden'           => [1, 1, 0, 0, 1],
            'data, desc, visible'                   => [1, 1, 0, 1, 0],
            'data, desc, primary, visible'          => [1, 1, 0, 1, 1],
            'data, desc, alt text, hidden'          => [1, 1, 1, 0, 0],
            'data, desc, alt text, primary, hidden' => [1, 1, 1, 0, 1],
            'data, desc, alt text, visible'         => [1, 1, 1, 1, 0],
            'everything'                            => [1, 1, 1, 1, 1],
        ];
    }

    /**
     * Test image delete access.
     *
     * @dataProvider imageDeleteProvider
     *
     * @param bool $image
     * @param int  $expected
     */
    public function testGetDeleteImage($image, $expected) {
        $this->actingAs($this->user)
            ->get('/admin/data/pieces/images/delete/'.($image ? $this->image->id : mt_rand(5, 50)))
            ->assertStatus($expected);
    }

    /**
     * Test image deletion.
     *
     * @dataProvider imageDeleteProvider
     *
     * @param bool $image
     * @param bool $expected
     */
    public function testPostDeleteImage($image, $expected) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/pieces/images/delete/'.($image ? $this->image->id : mt_rand(5, 50)));

        if ($expected == 200) {
            $response->assertSessionHasNoErrors();
            $this->assertModelMissing($this->image);
        } else {
            $response->assertSessionHasErrors();
            $this->assertModelExists($this->image);
        }
    }

    public static function imageDeleteProvider() {
        return [
            'valid'   => [1, 200],
            'invalid' => [0, 404],
        ];
    }
}
