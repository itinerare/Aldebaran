<?php

namespace Tests\Feature;

use App\Models\TextPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTextPageTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        TEXT PAGES
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test text page index access.
     */
    public function testGetTextPageIndex() {
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
    public function testPostEditSitePage($key) {
        // Ensure text pages are present to modify
        $this->artisan('add-text-pages');

        // Get the information for the page
        $page = TextPage::where('key', $key)->first();

        // Generate some test data
        $text = '<p>'.$this->faker->unique()->domainWord().'</p>';

        // Try to post data
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/pages/edit/'.$page->id, [
                'text' => $text,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('text_pages', [
            'key'  => $key,
            'text' => $text,
        ]);
    }

    public function textPageProvider() {
        return [
            'index'          => ['index'],
            'about'          => ['about'],
            'gallery'        => ['gallery'],
            'new commission' => ['new_commission'],
            'new quote'      => ['new_quote'],
            'privacy policy' => ['privacy'],
        ];
    }
}
