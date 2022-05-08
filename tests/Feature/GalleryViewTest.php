<?php

namespace Tests\Feature;

use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use App\Services\GalleryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GalleryViewTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: GALLERY & PROJECTS
    *******************************************************************************/

    protected function setUp(): void
    {
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
    public function testGetGallery($user, $enabled, $search, $pieceStatus, $status)
    {
        config(['aldebaran.settings.navigation.gallery' => $enabled]);

        if ($pieceStatus[0]) {
            // Create a piece to view
            $piece = Piece::factory()->create();

            if (!$pieceStatus[1]) {
                $piece->update(['is_visible' => 0]);
            }

            // Create images and test files
            $image = PieceImage::factory()->piece($piece->id)->create();
            $this->service->testImages($image);
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

            // Clean up test images
            $this->service->testImages($image, false);
        }
    }

    public function galleryAccessProvider()
    {
        return [
            'visitor, enabled'                     => [0, 1, null, [0, 0], 200],
            'visitor, disabled'                    => [0, 0, null, [0, 0], 404],
            'user, enabled'                        => [1, 1, null, [0, 0], 200],
            'user, disabled'                       => [1, 0, null, [0, 0], 404],
            'visitor, enabled with visible piece'  => [0, 1, null, [1, 1], 200],
            'visitor, disabled with visible piece' => [0, 0, null, [1, 1], 404],
            'user, enabled with visible piece'     => [1, 1, null, [1, 1], 200],
            'user, disabled with visible piece'    => [1, 0, null, [1, 1], 404],
            'visitor, enabled with hidden piece'   => [0, 1, null, [1, 0], 200],
            'visitor, disabled with hidden piece'  => [0, 0, null, [1, 0], 404],
            'user, enabled with hidden piece'      => [1, 1, null, [1, 0], 200],
            'user, disabled with hidden piece'     => [1, 0, null, [1, 0], 404],

            'search by title (successful)'     => [1, 1, ['name', 1], [1, 1], 200],
            'search by title (unsuccessful)'   => [1, 1, ['name', 0], [1, 1], 200],
            'search by project (successful)'   => [1, 1, ['project_id', 1], [1, 1], 200],
            'search by project (unsuccessful)' => [1, 1, ['project_id', 0], [1, 1], 200],
            'search by tag (successful)'       => [1, 1, ['tags%5B%5D', 1], [1, 1], 200],
            'search by tag (unsuccessful)'     => [1, 1, ['tags%5B%5D', 0], [1, 1], 200],
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
    public function testGetProject($user, $visible, $search, $pieceStatus, $status)
    {
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

            // Create images and test files
            $image = PieceImage::factory()->piece($piece->id)->create();
            $this->service->testImages($image);
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

            // Clean up test images
            $this->service->testImages($image, false);
        }
    }

    public function projectAccessProvider()
    {
        return [
            'visitor, visible'                    => [0, 1, null, [0, 0], 200],
            'visitor, hidden'                     => [0, 0, null, [0, 0], 404],
            'user, visible'                       => [1, 1, null, [0, 0], 200],
            'user, hidden'                        => [1, 0, null, [0, 0], 200],
            'visitor, visible with visible piece' => [0, 1, null, [1, 1], 200],
            'visitor, hidden with visible piece'  => [0, 0, null, [1, 1], 404],
            'user, visible with visible piece'    => [1, 1, null, [1, 1], 200],
            'user, hidden with visible piece'     => [1, 0, null, [1, 1], 200],
            'visitor, visible with hidden piece'  => [0, 1, null, [1, 0], 200],
            'visitor, hidden with hidden piece'   => [0, 0, null, [1, 0], 404],
            'user, visible with hidden piece'     => [1, 1, null, [1, 0], 200],
            'user, hidden with hidden piece'      => [1, 0, null, [1, 0], 200],

            'search by title (successful)'   => [1, 1, ['name', 1], [1, 1], 200],
            'search by title (unsuccessful)' => [1, 1, ['name', 0], [1, 1], 200],
            'search by tag (successful)'     => [1, 1, ['tags%5B%5D', 1], [1, 1], 200],
            'search by tag (unsuccessful)'   => [1, 1, ['tags%5B%5D', 0], [1, 1], 200],
        ];
    }

    /**
     * Test piece access.
     *
     * @dataProvider pieceAccessProvider
     *
     * @param bool $user
     * @param bool $description
     * @param bool $isVisible
     * @param bool $timestamp
     * @param bool $tag
     * @param bool $program
     * @param bool $goodExample
     * @param int  $status
     */
    public function testGetPiece($user, $isVisible, $description, $timestamp, $tag, $program, $goodExample, $status)
    {
        // Create objects and test images
        $piece = Piece::factory()->create();
        $image = PieceImage::factory()->piece($piece->id)->create();
        $this->service->testImages($image);

        // Adjust various attributes as appropriate
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

        // Clean up test images
        $this->service->testImages($image, false);
    }

    public function pieceAccessProvider()
    {
        // ($user, $isVisible, $description, $timestamp, $tag, $program, $goodExample, $status)

        return [
            'visitor, visible'      => [0, 1, 0, 0, 0, 0, 0, 200],
            'visitor, hidden'       => [0, 0, 0, 0, 0, 0, 0, 404],
            'user, visible'         => [1, 1, 0, 0, 0, 0, 0, 200],
            'user, hidden'          => [1, 0, 0, 0, 0, 0, 0, 200],
            'visitor, description'  => [0, 1, 1, 0, 0, 0, 0, 200],
            'user, description'     => [1, 1, 1, 0, 0, 0, 0, 200],
            'visitor, timestamp'    => [0, 1, 0, 1, 0, 0, 0, 200],
            'user, timestamp'       => [1, 1, 0, 1, 0, 0, 0, 200],
            'visitor, tag'          => [0, 1, 0, 0, 1, 0, 0, 200],
            'user, tag'             => [1, 1, 0, 0, 1, 0, 0, 200],
            'visitor, program'      => [0, 1, 0, 0, 0, 1, 0, 200],
            'user, program'         => [1, 1, 0, 0, 0, 1, 0, 200],
            'visitor, good example' => [0, 1, 0, 0, 0, 0, 1, 200],
            'user, good example'    => [1, 1, 0, 0, 0, 0, 1, 200],
        ];
    }
}
