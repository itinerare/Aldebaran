<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataGalleryTagTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: TAGS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Create a couple tags for editing, etc. purposes
        $this->tag = Tag::factory()->create();
        $this->dataTag = Tag::factory()
            ->description()->hidden()->inactive()->create();

        // Generate some test data
        $this->name = $this->faker->unique()->domainWord();
        $this->text = $this->faker->unique()->domainWord();
    }

    /**
     * Test tag index access.
     */
    public function testGetTagIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags')
            ->assertStatus(200);
    }

    /**
     * Test tag create access.
     */
    public function testGetCreateTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/create')
            ->assertStatus(200);
    }

    /**
     * Test tag edit access.
     */
    public function testGetEditTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/edit/'.$this->tag->id)
            ->assertStatus(200);
    }

    /**
     * Test tag creation.
     *
     * @dataProvider tagProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     * @param bool $isActive
     */
    public function testPostCreateTag($hasData, $hasDescription, $isVisible, $isActive)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/create', [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
                'is_active'   => $isActive,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tags', [
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
            'is_active'   => $isActive,
        ]);
    }

    /**
     * Test tag editing.
     *
     * @dataProvider tagProvider
     *
     * @param bool $hasData
     * @param bool $hasDescription
     * @param bool $isVisible
     * @param bool $isActive
     */
    public function testPostEditTag($hasData, $hasDescription, $isVisible, $isActive)
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/edit/'.($hasData ? $this->dataTag->id : $this->tag->id), [
                'name'        => $this->name,
                'description' => $hasDescription ? $this->text : null,
                'is_visible'  => $isVisible,
                'is_active'   => $isActive,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tags', [
            'id'          => $hasData ? $this->dataTag->id : $this->tag->id,
            'name'        => $this->name,
            'description' => $hasDescription ? $this->text : null,
            'is_visible'  => $isVisible,
            'is_active'   => $isActive,
        ]);
    }

    public function tagProvider()
    {
        // Get all possible sequences
        return $this->booleanSequences(4);
    }

    /**
     * Test tag delete access.
     */
    public function testGetDeleteTag()
    {
        $this->actingAs($this->user)
            ->get('/admin/data/tags/delete/'.$this->tag->id)
            ->assertStatus(200);
    }

    /**
     * Test tag deletion.
     *
     * @dataProvider tagDeleteProvider
     *
     * @param array $with
     * @param bool  $expected
     */
    public function testPostDeleteTag($with, $expected)
    {
        if ($with[0] ?? false) {
            $piece = Piece::factory()->create();
            PieceTag::factory()
                ->piece($piece->id)->tag($this->tag->id)
                ->create();
        }
        if ($with[1] ?? false) {
            CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10], true, $this->tag->id, true, true, $this->faker->unique()->domainWord())->create();
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/data/tags/delete/'.$this->tag->id);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertDeleted($this->tag);
        } else {
            $response->assertSessionHasErrors();
            $this->assertModelExists($this->tag);
        }
    }

    public function tagDeleteProvider()
    {
        return [
            'basic'      => [[0, 0], 1],
            'with piece' => [[1, 0], 0],

            // JSON searching may not work well in the test environment
            //'with type'           => [[0, 1], 0],
            //'with piece and type' => [[1, 1], 0],
        ];
    }
}
