<?php

namespace Tests\Feature;

use App\Models\Changelog;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Tag;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeedViewTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: RSS FEEDS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Set up gallery service for image processing
        $this->service = new GalleryService;
        $this->artisan('add-site-settings');
    }

    /**
     * Test feed index access.
     */
    public function testGetFeedIndex() {
        $this->get('feeds')
            ->assertStatus(200);
    }

    /**
     * Test feed access.
     *
     * @dataProvider feedProvider
     *
     * @param string $feed
     * @param array  $entry
     * @param int    $status
     */
    public function testGetFeed($feed, $entry, $status) {
        if ($entry[0]) {
            if ($feed == 'gallery' || $feed == 'all') {
                // Create a couple of pieces, one which should appear in the gallery
                // and one which should not

                $galleryPiece = Piece::factory()->create();
                $galleryPiece->update(['is_visible' => $entry[1]]);
                $piece = Piece::factory()->create();
                $piece->update(['is_visible' => $entry[1]]);

                // Create a tag hiding one of the pieces in the gallery
                $tag = Tag::factory()->inactive()->create();
                PieceTag::factory()->piece($piece->id)->tag($tag->id)->create();

                // Create images and test files
                $galleryImage = PieceImage::factory()->piece($galleryPiece->id)->create();
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($galleryImage);
                $this->service->testImages($image);
            }

            if ($feed == 'changelog') {
                $changelog = Changelog::factory()->create();
                $changelog->update(['is_visible' => $entry[1]]);
            }
        }

        $response = $this->get('feeds/'.$feed)
            ->assertStatus($status);

        if ($entry[0] && $status == 200) {
            switch ($feed) {
                case 'gallery':
                    if ($entry[1]) {
                        $response->assertSee(env('APP_URL').'/gallery/pieces/'.$galleryPiece->id);
                    } else {
                        $response->assertDontSee(env('APP_URL').'/gallery/pieces/'.$galleryImage->id);
                    }
                    $response->assertDontSee(env('APP_URL').'/gallery/pieces/'.$piece->id);
                    break;
                case 'all':
                    if ($entry[1]) {
                        $response->assertSee(env('APP_URL').'/gallery/pieces/'.$galleryPiece->id);
                        $response->assertSee(env('APP_URL').'/gallery/pieces/'.$piece->id);
                    } else {
                        $response->assertDontSee(env('APP_URL').'/gallery/pieces/'.$galleryImage->id);
                        $response->assertDontSee(env('APP_URL').'/gallery/pieces/'.$piece->id);
                    }
                    break;
                case 'changelog':
                    if ($entry[1]) {
                        $response->assertSee(env('APP_URL').'/changelog/'.$changelog->id);
                    } else {
                        $response->assertDontSee(env('APP_URL').'/changelog/'.$changelog->id);
                    }
                    break;
            }
        }

        if ($entry[0] && ($feed == 'gallery' || $feed == 'all')) {
            // Clean up test images
            $this->service->testImages($galleryImage, false);
            $this->service->testImages($image, false);
        }
    }

    public function feedProvider() {
        return [
            'gallery'                     => ['gallery', [0, 0], 200],
            'gallery with piece'          => ['gallery', [1, 1], 200],
            'gallery with hidden piece'   => ['gallery', [1, 0], 200],
            'all'                         => ['all', [0, 0], 200],
            'all with piece'              => ['all', [1, 1], 200],
            'all with hidden piece'       => ['all', [1, 0], 200],
            'changelog'                   => ['changelog', [0, 0], 200],
            'changelog with entry'        => ['changelog', [1, 1], 200],
            'changelog with hidden entry' => ['changelog', [1, 0], 200],
            'invalid'                     => ['invalid', [0, 0], 404],
        ];
    }
}
