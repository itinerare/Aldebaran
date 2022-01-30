<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting the main page.
     */
    public function test_CanGetIndex()
    {
        // The main page's contents are dependent on text pages existing,
        // so make sure they're present.
        $this->artisan('add-text-pages');

        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
