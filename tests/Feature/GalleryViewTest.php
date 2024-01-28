<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceLiterature;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use App\Services\GalleryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GalleryViewTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: GALLERY & PROJECTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Set up gallery service for image processing
        $this->service = new GalleryService;
    }

    /**
     * Test gallery access.
     *
     * @dataProvider galleryAccessProvider
     *
     * @param bool       $user
     * @param array|null $search
     * @param bool       $enabled
     * @param array      $pieceStatus
     * @param int        $status
     */
    public function testGetGallery($user, $enabled, $search, $pieceStatus, $status) {
        config(['aldebaran.settings.navigation.gallery' => $enabled]);

        $this->artisan('app:add-text-pages');

        if ($pieceStatus[0]) {
            // Create a piece to view
            $piece = Piece::factory()->create();

            if (!$pieceStatus[1]) {
                $piece->update(['is_visible' => 0]);
            }

            if ($pieceStatus[2]) {
                // Create images and test files
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($image);
            }

            if ($pieceStatus[3] && $pieceStatus[3][0]) {
                if ($pieceStatus[3][1]) {
                    // Create a literature with thumbnail data
                    $literature = PieceLiterature::factory()
                        ->piece($piece->id)->thumbnail()->create();

                    // And generate a fake thumbnail image and handle it
                    $thumbnail = UploadedFile::fake()->image('test_thumbnail.png');
                    $this->service->handleImage($thumbnail, $literature->imagePath, $literature->thumbnailFileName);
                } else {
                    // Otherwise just generate a literature
                    $literature = PieceLiterature::factory()
                        ->piece($piece->id)->create();
                }
            }
        }

        $url = 'gallery';

        // Set up urls for different search criteria / intended success
        if ($search) {
            $url = $url.'?'.$search[0].'=';
            switch ($search[0]) {
                case 'name':
                    $url = $url.($search[1] ? $piece->name : $this->faker->unique()->domainWord());
                    break;
                case 'project_id':
                    $url = $url.($search[1] ? $piece->project_id : Project::factory()->create()->id);
                    break;
                case 'tags%5B%5D':
                    $url = $url.($search[1] ? PieceTag::factory()->piece($piece)->create()->tag_id : Tag::factory()->create()->id);
            }
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);

        if ($pieceStatus[0]) {
            // If the gallery should be visible, test that the piece is
            // displayed or not depending on auth status and piece visibility
            // as well as search criteria
            if ($status == 200) {
                $response->assertViewHas('pieces', function ($pieces) use ($user, $search, $pieceStatus, $piece) {
                    if (($user || $pieceStatus[1]) && (!$search || $search[1])) {
                        return $pieces->contains($piece);
                    } else {
                        return !$pieces->contains($piece);
                    }
                });
            }

            if ($pieceStatus[2]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }

            if ($pieceStatus[3] && $pieceStatus[3][1]) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }
        }
    }

    public static function galleryAccessProvider() {
        return [
            'visitor, enabled'  => [0, 1, null, [0, 0, 0, 0], 200],
            'visitor, disabled' => [0, 0, null, [0, 0, 0, 0], 404],
            'user, enabled'     => [1, 1, null, [0, 0, 0, 0], 200],
            'user, disabled'    => [1, 0, null, [0, 0, 0, 0], 404],

            'visitor, enabled with visible piece with image'  => [0, 1, null, [1, 1, 1, 0], 200],
            'visitor, disabled with visible piece with image' => [0, 0, null, [1, 1, 1, 0], 404],
            'user, enabled with visible piece with image'     => [1, 1, null, [1, 1, 1, 0], 200],
            'user, disabled with visible piece with image'    => [1, 0, null, [1, 1, 1, 0], 404],
            'visitor, enabled with hidden piece with image'   => [0, 1, null, [1, 0, 1, 0], 200],
            'visitor, disabled with hidden piece with image'  => [0, 0, null, [1, 0, 1, 0], 404],
            'user, enabled with hidden piece with image'      => [1, 1, null, [1, 0, 1, 0], 200],
            'user, disabled with hidden piece with image'     => [1, 0, null, [1, 0, 1, 0], 404],

            'visitor, enabled with visible piece with literature'  => [0, 1, null, [1, 1, 0, [1, 0]], 200],
            'visitor, disabled with visible piece with literature' => [0, 0, null, [1, 1, 0, [1, 0]], 404],
            'user, enabled with visible piece with literature'     => [1, 1, null, [1, 1, 0, [1, 0]], 200],
            'user, disabled with visible piece with literature'    => [1, 0, null, [1, 1, 0, [1, 0]], 404],
            'visitor, enabled with hidden piece with literature'   => [0, 1, null, [1, 0, 0, [1, 0]], 200],
            'visitor, disabled with hidden piece with literature'  => [0, 0, null, [1, 0, 0, [1, 0]], 404],
            'user, enabled with hidden piece with literature'      => [1, 1, null, [1, 0, 0, [1, 0]], 200],
            'user, disabled with hidden piece with literature'     => [1, 0, null, [1, 0, 0, [1, 0]], 404],

            'visitor, enabled with visible piece with literature with thumbnail'  => [0, 1, null, [1, 1, 0, [1, 1]], 200],
            'visitor, disabled with visible piece with literature with thumbnail' => [0, 0, null, [1, 1, 0, [1, 1]], 404],
            'user, enabled with visible piece with literature with thumbnail'     => [1, 1, null, [1, 1, 0, [1, 1]], 200],
            'user, disabled with visible piece with literature with thumbnail'    => [1, 0, null, [1, 1, 0, [1, 1]], 404],
            'visitor, enabled with hidden piece with literature with thumbnail'   => [0, 1, null, [1, 0, 0, [1, 1]], 200],
            'visitor, disabled with hidden piece with literature with thumbnail'  => [0, 0, null, [1, 0, 0, [1, 1]], 404],
            'user, enabled with hidden piece with literature with thumbnail'      => [1, 1, null, [1, 0, 0, [1, 1]], 200],
            'user, disabled with hidden piece with literature with thumbnail'     => [1, 0, null, [1, 0, 0, [1, 1]], 404],

            'search by title (successful)'     => [1, 1, ['name', 1], [1, 1, 1, 0], 200],
            'search by title (unsuccessful)'   => [1, 1, ['name', 0], [1, 1, 1, 0], 200],
            'search by project (successful)'   => [1, 1, ['project_id', 1], [1, 1, 1, 0], 200],
            'search by project (unsuccessful)' => [1, 1, ['project_id', 0], [1, 1, 1, 0], 200],
            'search by tag (successful)'       => [1, 1, ['tags%5B%5D', 1], [1, 1, 1, 0], 200],
            'search by tag (unsuccessful)'     => [1, 1, ['tags%5B%5D', 0], [1, 1, 1, 0], 200],
        ];
    }

    /**
     * Test project access.
     *
     * @dataProvider projectAccessProvider
     *
     * @param bool       $user
     * @param bool       $visible
     * @param array|null $search
     * @param array      $pieceStatus
     * @param int        $status
     */
    public function testGetProject($user, $visible, $search, $pieceStatus, $status) {
        $project = Project::factory()->create();
        if (!$visible) {
            $project->update(['is_visible' => 0]);
        }

        if ($pieceStatus[0]) {
            // Create a piece to view
            $piece = Piece::factory()->project($project->id)->create();

            if (!$pieceStatus[1]) {
                $piece->update(['is_visible' => 0]);
            }

            if ($pieceStatus[2]) {
                // Create images and test files
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($image);
            }

            if ($pieceStatus[3] && $pieceStatus[3][0]) {
                if ($pieceStatus[3][1]) {
                    // Create a literature with thumbnail data
                    $literature = PieceLiterature::factory()
                        ->piece($piece->id)->thumbnail()->create();

                    // And generate a fake thumbnail image and handle it
                    $thumbnail = UploadedFile::fake()->image('test_thumbnail.png');
                    $this->service->handleImage($thumbnail, $literature->imagePath, $literature->thumbnailFileName);
                } else {
                    // Otherwise just generate a literature
                    $literature = PieceLiterature::factory()
                        ->piece($piece->id)->create();
                }
            }
        }

        $url = $project->url;

        // Set up urls for different search criteria / intended success
        if ($search) {
            $url = $url.'?'.$search[0].'=';
            switch ($search[0]) {
                case 'name':
                    $url = $url.($search[1] ? $piece->name : $this->faker->unique()->domainWord());
                    break;
                case 'tags%5B%5D':
                    $url = $url.($search[1] ? PieceTag::factory()->piece($piece)->create()->tag_id : Tag::factory()->create()->id);
            }
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);

        if ($pieceStatus[0]) {
            // If the gallery should be visible, test that the piece is
            // displayed or not depending on auth status and piece visibility
            // as well as search criteria
            if ($status == 200) {
                $response->assertViewHas('pieces', function ($pieces) use ($user, $search, $pieceStatus, $piece) {
                    if (($user || $pieceStatus[1]) && (!$search || $search[1])) {
                        return $pieces->contains($piece);
                    } else {
                        return !$pieces->contains($piece);
                    }
                });
            }

            if ($pieceStatus[2]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }

            if ($pieceStatus[3] && $pieceStatus[3][1]) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }
        }
    }

    public static function projectAccessProvider() {
        return [
            'visitor, visible' => [0, 1, null, [0, 0, 0, 0], 200],
            'visitor, hidden'  => [0, 0, null, [0, 0, 0, 0], 404],
            'user, visible'    => [1, 1, null, [0, 0, 0, 0], 200],
            'user, hidden'     => [1, 0, null, [0, 0, 0, 0], 200],

            'visitor, visible with visible piece with image' => [0, 1, null, [1, 1, 1, 0], 200],
            'visitor, hidden with visible piece with image'  => [0, 0, null, [1, 1, 1, 0], 404],
            'user, visible with visible piece with image'    => [1, 1, null, [1, 1, 1, 0], 200],
            'user, hidden with visible piece with image'     => [1, 0, null, [1, 1, 1, 0], 200],
            'visitor, visible with hidden piece with image'  => [0, 1, null, [1, 0, 1, 0], 200],
            'visitor, hidden with hidden piece with image'   => [0, 0, null, [1, 0, 1, 0], 404],
            'user, visible with hidden piece with image'     => [1, 1, null, [1, 0, 1, 0], 200],
            'user, hidden with hidden piece with image'      => [1, 0, null, [1, 0, 1, 0], 200],

            'visitor, visible with visible piece with literature' => [0, 1, null, [1, 1, 0, [1, 0]], 200],
            'visitor, hidden with visible piece with literature'  => [0, 0, null, [1, 1, 0, [1, 0]], 404],
            'user, visible with visible piece with literature'    => [1, 1, null, [1, 1, 0, [1, 0]], 200],
            'user, hidden with visible piece with literature'     => [1, 0, null, [1, 1, 0, [1, 0]], 200],
            'visitor, visible with hidden piece with literature'  => [0, 1, null, [1, 0, 0, [1, 0]], 200],
            'visitor, hidden with hidden piece with literature'   => [0, 0, null, [1, 0, 0, [1, 0]], 404],
            'user, visible with hidden piece with literature'     => [1, 1, null, [1, 0, 0, [1, 0]], 200],
            'user, hidden with hidden piece with literature'      => [1, 0, null, [1, 0, 0, [1, 0]], 200],

            'visitor, visible with visible piece with literature with thumbnail' => [0, 1, null, [1, 1, 0, [1, 1]], 200],
            'visitor, hidden with visible piece with literature with thumbnail'  => [0, 0, null, [1, 1, 0, [1, 1]], 404],
            'user, visible with visible piece with literature with thumbnail'    => [1, 1, null, [1, 1, 0, [1, 1]], 200],
            'user, hidden with visible piece with literature with thumbnail'     => [1, 0, null, [1, 1, 0, [1, 1]], 200],
            'visitor, visible with hidden piece with literature with thumbnail'  => [0, 1, null, [1, 0, 0, [1, 1]], 200],
            'visitor, hidden with hidden piece with literature with thumbnail'   => [0, 0, null, [1, 0, 0, [1, 1]], 404],
            'user, visible with hidden piece with literature with thumbnail'     => [1, 1, null, [1, 0, 0, [1, 1]], 200],
            'user, hidden with hidden piece with literature with thumbnail'      => [1, 0, null, [1, 0, 0, [1, 1]], 200],

            'search by title (successful)'   => [1, 1, ['name', 1], [1, 1, 1, 0], 200],
            'search by title (unsuccessful)' => [1, 1, ['name', 0], [1, 1, 1, 0], 200],
            'search by tag (successful)'     => [1, 1, ['tags%5B%5D', 1], [1, 1, 1, 0], 200],
            'search by tag (unsuccessful)'   => [1, 1, ['tags%5B%5D', 0], [1, 1, 1, 0], 200],
        ];
    }

    /**
     * Test piece access.
     *
     * @dataProvider pieceAccessProvider
     *
     * @param bool $user
     * @param bool $image
     * @param bool $literature
     * @param bool $description
     * @param bool $altText
     * @param bool $isVisible
     * @param bool $timestamp
     * @param bool $tag
     * @param bool $program
     * @param bool $goodExample
     * @param int  $status
     */
    public function testGetPiece($user, $image, $literature, $isVisible, $description, $altText, $timestamp, $tag, $program, $goodExample, $status) {
        // Create objects and test images
        $piece = Piece::factory()->create();

        // Adjust various attributes as appropriate
        if ($image) {
            $image = PieceImage::factory()->piece($piece->id)->create();
            $this->service->testImages($image);

            if ($altText) {
                $image->update([
                    'alt_text' => $this->faker->realText(),
                ]);
            }
        }
        if ($literature) {
            $literature = PieceLiterature::factory()->piece($piece->id)->create();
        }
        if ($description) {
            $piece->update(['description' => $this->faker->unique()->domainWord()]);
        }
        if (!$isVisible) {
            $piece->update(['is_visible' => 0]);
        }
        if ($timestamp) {
            $piece->update(['timestamp' => Carbon::now()]);
        }
        if ($tag) {
            PieceTag::factory()->piece($piece->id)->create();
        }
        if ($program) {
            PieceProgram::factory()->piece($piece->id)->create();
        }
        if ($goodExample) {
            $piece->update(['good_example' => 1]);
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($piece->url);
        } else {
            $response = $this->get($piece->url);
        }

        $response->assertStatus($status);

        if ($image) {
            // Clean up test images
            $this->service->testImages($image, false);
        }
    }

    public static function pieceAccessProvider() {
        // ($user, $image, $literature, $isVisible, $description, $altText, $timestamp, $tag, $program, $goodExample, $status)

        return [
            'visitor, visible with image'      => [0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 200],
            'visitor, hidden with image'       => [0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 404],
            'user, visible with image'         => [1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 200],
            'user, hidden with image'          => [1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 200],
            'visitor, description with image'  => [0, 1, 0, 1, 1, 0, 0, 0, 0, 0, 200],
            'user, description with image'     => [1, 1, 0, 1, 1, 0, 0, 0, 0, 0, 200],
            'visitor, alt text with image'     => [0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 200],
            'user, alt text with image'        => [1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 200],
            'visitor, timestamp with image'    => [0, 1, 0, 1, 0, 0, 1, 0, 0, 0, 200],
            'user, timestamp with image'       => [1, 1, 0, 1, 0, 0, 1, 0, 0, 0, 200],
            'visitor, tag with image'          => [0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 200],
            'user, tag with image'             => [1, 1, 0, 1, 0, 0, 0, 1, 0, 0, 200],
            'visitor, program with image'      => [0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 200],
            'user, program with image'         => [1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 200],
            'visitor, good example with image' => [0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 200],
            'user, good example with image'    => [1, 1, 0, 1, 0, 0, 0, 0, 0, 1, 200],

            'visitor, visible with literature'      => [0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 200],
            'visitor, hidden with literature'       => [0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 404],
            'user, visible with literature'         => [1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 200],
            'user, hidden with literature'          => [1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 200],
            'visitor, description with literature'  => [0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'user, description with literature'     => [1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'visitor, timestamp with literature'    => [0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 200],
            'user, timestamp with literature'       => [1, 0, 1, 1, 0, 0, 1, 0, 0, 0, 200],
            'visitor, tag with literature'          => [0, 0, 1, 1, 0, 0, 0, 1, 0, 0, 200],
            'user, tag with literature'             => [1, 0, 1, 1, 0, 0, 0, 1, 0, 0, 200],
            'visitor, program with literature'      => [0, 0, 1, 1, 0, 0, 0, 0, 1, 0, 200],
            'user, program with literature'         => [1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 200],
            'visitor, good example with literature' => [0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 200],
            'user, good example with literature'    => [1, 0, 1, 1, 0, 0, 0, 0, 0, 1, 200],

            'visitor, visible with both'      => [0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 200],
            'visitor, hidden with both'       => [0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 404],
            'user, visible with both'         => [1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 200],
            'user, hidden with both'          => [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 200],
            'visitor, description with both'  => [0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'user, description with both'     => [1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'visitor, alt text with both'     => [0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'user, alt text with both'        => [1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 200],
            'visitor, timestamp with both'    => [0, 1, 1, 1, 0, 0, 1, 0, 0, 0, 200],
            'user, timestamp with both'       => [1, 1, 1, 1, 0, 0, 1, 0, 0, 0, 200],
            'visitor, tag with both'          => [0, 1, 1, 1, 0, 0, 0, 1, 0, 0, 200],
            'user, tag with both'             => [1, 1, 1, 1, 0, 0, 0, 1, 0, 0, 200],
            'visitor, program with both'      => [0, 1, 1, 1, 0, 0, 0, 0, 1, 0, 200],
            'user, program with both'         => [1, 1, 1, 1, 0, 0, 0, 0, 1, 0, 200],
            'visitor, good example with both' => [0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 200],
            'user, good example with both'    => [1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 200],
        ];
    }
}
