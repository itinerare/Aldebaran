<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageViewTest extends TestCase
{
    use RefreshDatabase;

    /******************************************************************************
        PUBLIC: PAGES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test page access.
     *
     * @dataProvider pageProvider
     *
     * @param string $page
     * @param bool   $setup
     * @param bool   $user
     * @param int    $status
     */
    public function testGetPage($page, $setup, $user, $status)
    {
        if ($setup) {
            $this->artisan('add-text-pages');
        }

        if ($user) {
            $response = $this->actingAs($this->user)->get($page);
        } else {
            $response = $this->get($page);
        }

        $response->assertStatus($status);
    }

    public function pageProvider()
    {
        return [
            'about, not set up, visitor'          => ['about', 0, 0, 404],
            'about, not set up, user'             => ['about', 0, 1, 404],
            'about, set up, visitor'              => ['about', 1, 0, 200],
            'about, set up, user'                 => ['about', 1, 1, 200],
            'privacy policy, not set up, visitor' => ['privacy', 0, 0, 404],
            'privacy policy, not set up, user'    => ['privacy', 0, 1, 404],
            'privacy policy, set up, visitor'     => ['privacy', 1, 0, 200],
            'privacy policy, set up, user'        => ['privacy', 1, 1, 200],
            'non-page blurb, visitor'             => ['new_commission', 1, 0, 404],
            'non-page blurb, user'                => ['new_commission', 1, 1, 404],
            'invalid page, visitor'               => ['invalid', 1, 0, 404],
            'invalid page, user'                  => ['invalid', 1, 1, 404],
        ];
    }
}
