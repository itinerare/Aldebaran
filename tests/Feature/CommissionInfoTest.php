<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceLiterature;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use App\Models\TextPage;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommissionInfoTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: COMMISSION INFO
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Create testing class
        $this->class = CommissionClass::factory()->create();

        // Set up gallery service for image processing
        $this->service = new GalleryService;
    }

    /**
     * Test commission info access.
     *
     * @dataProvider commissionAccessProvider
     * @dataProvider commissionInfoProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetCommissionInfo($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        if (!$visibility[1]) {
            $this->class->update(['is_active' => 0]);
        }

        // Perform additional setup
        if ($data && $data[0]) {
            $category = CommissionCategory::factory()->class($this->class->id)->create();

            if ($data[1]) {
                $tag = Tag::factory()->create();
                $type = CommissionType::factory()->category($category->id)->testData(['type' => 'flat', 'cost' => 10], $tag->id, true, true, $this->faker->unique()->domainWord())->create();

                $this->class->update(['show_examples' => $data[1]]);

                if ($data[3][0]) {
                    $piece = Piece::factory()->create();
                    PieceTag::factory()->piece($piece)->tag($tag->id)->create();

                    $piece->update([
                        'is_visible'   => $data[3][1],
                        'good_example' => $data[3][2],
                    ]);

                    if ($data[3][5]) {
                        // Create images and test files
                        $image = PieceImage::factory()->piece($piece->id)->create();
                        $this->service->testImages($image);
                    }

                    if ($data[3][6] && $data[3][6][0]) {
                        if ($data[3][6][1]) {
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

                    // Create examples if relevant
                    foreach ([$data[3][3], $data[3][4]] as $key => $quantity) {
                        if ($quantity > 0) {
                            $examples = [];
                            for ($i = 0; $i <= $quantity; $i++) {
                                $examples[$i] = Piece::factory()->create();
                                PieceTag::factory()->piece($examples[$i])->tag($tag->id)->create();
                                $examples[$i]->update(['good_example' => ($key == 3 ? 1 : 0)]);

                                $exampleImages[$key][$i] = PieceImage::factory()->piece($examples[$i]->id)->create();
                                $this->service->testImages($exampleImages[$key][$i]);
                            }
                            unset($examples);
                        }
                    }
                }
            }
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get('commissions/'.$this->class->slug);
        } else {
            $response = $this->get('commissions/'.$this->class->slug);
        }

        $response->assertStatus($status);

        if ($data && $data[3][0]) {
            if ($status == 200) {
                // Fetch examples and check that there are the intended number of them
                $examples = $type->getExamples($user ? $this->user : null);
                $this->assertTrue($examples->count() == (($data[3][1] || $user ? $data[3][0] : 0) + $data[3][3] + $data[3][4]));

                // For this particular case, it's difficult to test against the view,
                // so instead check that the view loads as expected and examples are
                // returned or not based on visibility here
                if ($data[2] && ($user || $data[3][1]) && ($data[3][2] || $data[3][3] < 4)) {
                    $this->assertTrue($examples->contains($piece));
                } else {
                    $this->assertFalse($examples->contains($piece));
                }
            }

            if ($data[3][5]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }

            if ($data[3][6] && $data[3][6][1]) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }

            if (isset($exampleImages)) {
                foreach ($exampleImages as $pools) {
                    foreach ($pools as $image) {
                        $this->service->testImages($image, false);
                    }
                }
            }
        }
    }

    public function commissionInfoProvider() {
        // $data = [hasCategory, hasType, withExamples, [withPiece, isVisible, isGoodExample, goodExamples, okExamples, hasImage, hasLiterature/[hasLiterature, hasThumb]]]

        return [
            'with category'                 => [[1, 1], 0, [1, 0, 0, [0, 0, 0, 0, 0, 0, 0]], 200],
            'with type'                     => [[1, 1], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 200],
            'with type with empty examples' => [[1, 1], 0, [1, 1, 1, [0, 0, 0, 0, 0, 0, 0]], 200],

            'visitor with type with visible example with image' => [[1, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 1, 0]], 200],
            'visitor with type with hidden example with image'  => [[1, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 1, 0]], 200],
            'user with type with visible example with image'    => [[1, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 1, 0]], 200],
            'user with type with hidden example with image'     => [[1, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 1, 0]], 200],

            'visitor with type with visible example with literature' => [[1, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 0]]], 200],
            'visitor with type with hidden example with literature'  => [[1, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 0]]], 200],
            'user with type with visible example with literature'    => [[1, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 0]]], 200],
            'user with type with hidden example with literature'     => [[1, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 0]]], 200],

            'visitor with type with visible example with literature with thumbnail' => [[1, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 1]]], 200],
            'visitor with type with hidden example with literature with thumbnail'  => [[1, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 1]]], 200],
            'user with type with visible example with literature with thumbnail'    => [[1, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 1]]], 200],
            'user with type with hidden example with literature with thumbnail'     => [[1, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 1]]], 200],

            // Disabled in favor of manual testing for the moment
            //'good example with 4 ok examples' => [[1, 1], 1, [1, 1, 1, [1, 1, 0, 0, 4]], 200],
            //'ok example with 3 good examples' => [[1, 1], 1, [1, 1, 1, [1, 1, 0, 3, 0]], 200],
            //'ok example with 4 good examples' => [[1, 1], 1, [1, 1, 1, [1, 1, 0, 4, 0]], 200],
        ];
    }

    /**
     * Test commission type info access.
     *
     * @dataProvider commissionTypeProvider
     *
     * @param array $visibility
     * @param bool  $user
     * @param array $data
     * @param int   $status
     */
    public function testGetCommissionTypeInfo($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        // Update various settings
        $this->class->update(['is_active' => $visibility[1]]);
        if ($visibility[2]) {
            DB::table('site_settings')->where('key', $this->class->slug.'_comms_open')->update([
                'value' => 1,
            ]);
        }

        // Perform additional setup
        if ($data && $data[0]) {
            $category = CommissionCategory::factory()->class($this->class->id)->create();

            if ($data[1]) {
                $tag = Tag::factory()->create();
                $type = CommissionType::factory()->category($category->id)->testData(['type' => 'flat', 'cost' => 10], $tag->id, true, true, $this->faker->unique()->domainWord())->create();

                $type->update([
                    'is_active'  => $visibility[3],
                    'is_visible' => $visibility[4],
                ]);

                $this->class->update(['show_examples' => $data[1]]);

                if ($data[3][0]) {
                    $piece = Piece::factory()->create();
                    PieceTag::factory()->piece($piece)->tag($tag->id)->create();

                    $piece->update([
                        'is_visible'   => $data[3][1],
                        'good_example' => $data[3][2],
                    ]);

                    if ($data[3][5]) {
                        // Create images and test files
                        $image = PieceImage::factory()->piece($piece->id)->create();
                        $this->service->testImages($image);
                    }

                    if ($data[3][6] && $data[3][6][0]) {
                        if ($data[3][6][1]) {
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

                    // Create examples if relevant
                    foreach ([$data[3][3], $data[3][4]] as $key => $quantity) {
                        if ($quantity > 0) {
                            $examples = [];
                            for ($i = 0; $i <= $quantity; $i++) {
                                $examples[$i] = Piece::factory()->create();
                                PieceTag::factory()->piece($examples[$i])->tag($tag->id)->create();
                                $examples[$i]->update(['good_example' => ($key == 3 ? 1 : 0)]);

                                $exampleImages[$key][$i] = PieceImage::factory()->piece($examples[$i]->id)->create();
                                $this->service->testImages($exampleImages[$key][$i]);
                            }
                            unset($examples);
                        }
                    }
                }
            }
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get('commissions/types/'.$type->key);
        } else {
            $response = $this->get('commissions/types/'.$type->key);
        }

        $response->assertStatus($status);

        if ($data && $data[3][0]) {
            if ($status == 200) {
                // Fetch examples
                $examples = $type->getExamples($user ? $this->user : null);
                $this->assertTrue($examples->count() == (($data[3][1] || $user ? $data[3][0] : 0) + $data[3][3] + $data[3][4]));

                // For this particular case, it's difficult to test against the view,
                // so instead check that the view loads as expected and examples are
                // returned or not based on visibility here
                if ($data[2] && ($user || $data[3][1]) && ($data[3][2] || $data[3][3] < 4)) {
                    $this->assertTrue($examples->contains($piece));
                } else {
                    $this->assertFalse($examples->contains($piece));
                }
            }

            if ($data[3][5]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }

            if ($data[3][6] && $data[3][6][1]) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }
        }
    }

    public function commissionTypeProvider() {
        // $visibility = [commsEnabled, classActive, commsOpen, typeActive, typeVisible, withKey]
        // $data = [hasCategory, hasType, withExamples, [withPiece, isVisible, isGoodExample, goodExamples, okExamples, hasImage, hasLiterature/[hasLiterature, hasThumb]]]

        return [
            'visitor, type active, visible'           => [[1, 1, 1, 1, 1, 0], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'visitor, type inactive, visible'         => [[1, 1, 1, 0, 1, 0], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'visitor, type active, hidden with key'   => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 200],
            'visitor, type inactive, hidden with key' => [[1, 1, 1, 0, 0, 1], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'visitor, class inactive'                 => [[1, 0, 1, 1, 1, 0], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'visitor, comms disabled'                 => [[0, 1, 1, 1, 1, 0], 0, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'user, type active, visible'              => [[1, 1, 1, 1, 1, 0], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'user, type inactive, visible'            => [[1, 1, 1, 0, 1, 0], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'user, type active, hidden with key'      => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 200],
            'user, type inactive, hidden with key'    => [[1, 1, 1, 0, 0, 1], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'user, class inactive'                    => [[1, 0, 1, 1, 1, 0], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],
            'user, comms disabled'                    => [[0, 1, 1, 1, 1, 0], 1, [1, 1, 0, [0, 0, 0, 0, 0, 0, 0]], 404],

            'with type with empty examples' => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [0, 0, 0, 0, 0, 0, 0]], 200],

            'visitor with type with visible example with image' => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 1, 0]], 200],
            'visitor with type with hidden example with image'  => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 1, 0]], 200],
            'user with type with visible example with image'    => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 1, 0]], 200],
            'user with type with hidden example with image'     => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 1, 0]], 200],

            'visitor with type with visible example with literature' => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 0]]], 200],
            'visitor with type with hidden example with literature'  => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 0]]], 200],
            'user with type with visible example with literature'    => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 0]]], 200],
            'user with type with hidden example with literature'     => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 0]]], 200],

            'visitor with type with visible example with literature with thumbnail' => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 1]]], 200],
            'visitor with type with hidden example with literature with thumbnail'  => [[1, 1, 1, 1, 0, 1], 0, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 1]]], 200],
            'user with type with visible example with literature with thumbnail'    => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 1, 1, 0, 0, 0, [1, 1]]], 200],
            'user with type with hidden example with literature with thumbnail'     => [[1, 1, 1, 1, 0, 1], 1, [1, 1, 1, [1, 0, 1, 0, 0, 0, [1, 1]]], 200],
        ];
    }

    /**
     * Test commission type gallery access.
     *
     * @dataProvider commissionAccessProvider
     * @dataProvider commissionTypeGalleryProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetCommissionTypeGallery($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        if (!$visibility[1]) {
            $this->class->update(['is_active' => 0]);
        }

        $tag = Tag::factory()->create();

        // Create a category in the class
        $category = CommissionCategory::factory()->class($this->class->id)->create();

        // Create type in the category
        $type = CommissionType::factory()->category($category->id)->testData(['type' => 'flat', 'cost' => 10], $tag->id, true, true, $this->faker->unique()->domainWord())->create();

        // If called for, create a piece and attending objects
        if ($data && $data[0]) {
            $piece = Piece::factory()->create();
            PieceTag::factory()->piece($piece)->tag($tag->id)->create();

            if (!$data[0][1]) {
                $piece->update(['is_visible' => 0]);
            }

            if ($data[0][2]) {
                // Create images and test files
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($image);
            }

            if ($data[0][3] && $data[0][3][0]) {
                if ($data[0][3][1]) {
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

        $url = 'commissions/types/'.$type->id.'/gallery';
        // Set up urls for different search criteria / intended success
        if ($data && $data[1]) {
            $url = $url.'?'.$data[1][0].'=';
            switch ($data[1][0]) {
                case 'name':
                    $url = $url.($data[1][1] ? $piece->name : $this->faker->unique()->domainWord());
                    break;
                case 'project_id':
                    $url = $url.($data[1][1] ? $piece->project_id : Project::factory()->create()->id);
                    break;
            }
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);

        if ($data && $data[0]) {
            // If the gallery should be visible, test that the piece is
            // displayed or not depending on auth status and piece visibility
            // as well as search criteria
            if ($status == 200) {
                $response->assertViewHas('pieces', function ($pieces) use ($user, $data, $piece) {
                    if (($user || $data[0][1]) && (!$data[1] || $data[1][1])) {
                        return $pieces->contains($piece);
                    } else {
                        return !$pieces->contains($piece);
                    }
                });
            }

            if ($data[0][2]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }

            if ($data[0][3] && $data[0][3][1]) {
                // Remove test thumbnail file
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }
        }
    }

    public function commissionTypeGalleryProvider() {
        // $data = [[hasPiece, isVisible, hasImage, hasLiterature/[hasLiterature, hasThumb]], [searchType, expectedResult]]
        // Search is dependent on presence of at least one piece

        return [
            'visitor, with visible piece with image' => [[1, 1], 0, [[1, 1, 1, 0], null], 200],
            'visitor, with hidden piece with image'  => [[1, 1], 0, [[1, 0, 1, 0], null], 200],
            'user, with visible piece with image'    => [[1, 1], 1, [[1, 1, 1, 0], null], 200],
            'user, with hidden piece with image'     => [[1, 1], 1, [[1, 0, 1, 0], null], 200],

            'visitor, with visible piece with literature' => [[1, 1], 0, [[1, 1, 0, [1, 0]], null], 200],
            'visitor, with hidden piece with literature'  => [[1, 1], 0, [[1, 0, 0, [1, 0]], null], 200],
            'user, with visible piece with literature'    => [[1, 1], 1, [[1, 1, 0, [1, 0]], null], 200],
            'user, with hidden piece with literature'     => [[1, 1], 1, [[1, 0, 0, [1, 0]], null], 200],

            'visitor, with visible piece with literature with thumbnail' => [[1, 1], 0, [[1, 1, 0, [1, 1]], null], 200],
            'visitor, with hidden piece with literature with thumbnail'  => [[1, 1], 0, [[1, 0, 0, [1, 1]], null], 200],
            'user, with visible piece with literature with thumbnail'    => [[1, 1], 1, [[1, 1, 0, [1, 1]], null], 200],
            'user, with hidden piece with literature with thumbnail'     => [[1, 1], 1, [[1, 0, 0, [1, 1]], null], 200],

            'search by title (successful)'     => [[1, 1], 1, [[1, 1, 1, 0], ['name', 1]], 200],
            'search by title (unsuccessful)'   => [[1, 1], 1, [[1, 1, 1, 0], ['name', 0]], 200],
            'search by project (successful)'   => [[1, 1], 1, [[1, 1, 1, 0], ['project_id', 1]], 200],
            'search by project (unsuccessful)' => [[1, 1], 1, [[1, 1, 1, 0], ['project_id', 0]], 200],
        ];
    }

    /**
     * Test commission ToS access.
     *
     * @dataProvider commissionAccessProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetCommissionTerms($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        if (!$visibility[1]) {
            $this->class->update(['is_active' => 0]);
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get('commissions/'.$this->class->slug.'/tos');
        } else {
            $response = $this->get('commissions/'.$this->class->slug.'/tos');
        }

        $response->assertStatus($status);
    }

    /**
     * Test commission queue access.
     *
     * @dataProvider commissionAccessProvider
     * @dataProvider commissionQueueProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetCommissionQueue($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        if (!$visibility[1]) {
            $this->class->update(['is_active' => 0]);
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get('commissions/'.$this->class->slug.'/queue');
        } else {
            $response = $this->get('commissions/'.$this->class->slug.'/queue');
        }

        $response->assertStatus($status);
    }

    public function commissionQueueProvider() {
        return [
            //'with commission' => [[1, 1], 0, null, 200],
        ];
    }

    /**
     * Test commission page access.
     *
     * @dataProvider commissionAccessProvider
     * @dataProvider commissionPageProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetCommissionPage($visibility, $user, $data, $status) {
        // Enable/disable commission components
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);

        // Create a page to view, and update the class with it
        $page = TextPage::factory()->create();
        $this->class->update([
            'is_active' => $visibility[1],
            'data'      => '{"pages":{"'.$page->id.'":{"key":"'.$page->key.'","title":"'.$page->name.'"}}}',
        ]);

        $url = 'commissions/'.$this->class->slug.'/'.(!$data || ($data && $data[0]) ? $page->key : 'about');

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);
    }

    public function commissionPageProvider() {
        return [
            'with valid page'   => [[1, 1], 0, [1], 200],
            'with invalid page' => [[1, 1], 0, [0], 404],
        ];
    }

    public function commissionAccessProvider() {
        return [
            'visitor, comms enabled, active'    => [[1, 1], 0, null, 200],
            'visitor, comms enabled, inactive'  => [[1, 0], 0, null, 404],
            'visitor, comms disabled, active'   => [[0, 1], 0, null, 404],
            'visitor, comms disabled, inactive' => [[0, 0], 0, null, 404],
            'user, comms enabled, active'       => [[1, 1], 1, null, 200],
            'user, comms enabled, inactive'     => [[1, 0], 1, null, 200],
            'user, comms disabled, active'      => [[0, 1], 1, null, 404],
            'user, comms disabled, inactive'    => [[0, 0], 1, null, 404],
        ];
    }
}
