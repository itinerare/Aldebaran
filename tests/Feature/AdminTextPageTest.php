<?php

namespace Tests\Feature;

use App\Models\TextPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTextPageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        TEXT PAGES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        $this->text = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test text page index access.
     */
    public function testGetTextPageIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/pages')
            ->assertStatus(200);
    }

    /**
     * Test text page editing.
     *
     * @dataProvider textPageProvider
     *
     * @param string $key
     */
    public function testPostEditSitePage($key)
    {
        // Ensure text pages are present to modify
        $this->artisan('add-text-pages');

        // Get the information for the page
        $page = TextPage::where('key', $key)->first();

        // Try to post data
        $this
            ->actingAs($this->user)
            ->post('/admin/pages/edit/'.$page->id, [
                'text' => $this->text,
            ]);

        $this->assertDatabaseHas('text_pages', [
            'key'  => $key,
            'text' => $this->text,
        ]);
    }

    public function textPageProvider()
    {
        return [
            'index'          => ['index'],
            'about'          => ['about'],
            'gallery'        => ['gallery'],
            'new commission' => ['new_commission'],
            'privacy policy' => ['privacy'],
        ];
    }
}
