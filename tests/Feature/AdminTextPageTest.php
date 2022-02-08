<?php

namespace Tests\Feature;

use App\Models\TextPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTextPageTest extends TestCase
{
    use RefreshDatabase;

    /******************************************************************************
        TEXT PAGES
    *******************************************************************************/

    /**
     * Test text page index access.
     */
    public function testCanGetTextPageIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/pages')
            ->assertStatus(200);
    }

    /**
     * Test text page editing.
     */
    public function testCanPostEditSitePage()
    {
        // Ensure text pages are present to modify
        $this->artisan('add-text-pages');

        // Get the information for the 'about' page
        $page = TextPage::where('key', 'about')->first();

        // Make sure the setting is default so as to consistently test
        $page->update(['text' => '<p>The text here will be displayed on the about page.</p>']);

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/pages/edit/'.$page->id, [
                'text' => 'TEST SUCCESS',
            ]);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('text_pages', [
            'key'  => 'about',
            'text' => 'TEST SUCCESS',
        ]);
    }
}
