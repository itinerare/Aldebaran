<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    // These tests check that visitor/user access to different routes is as expected

    /**
     * Test getting the main page.
     */
    public function test_CanGetIndex()
    {
        // Attempt to access the site on the most basic level
        $response = $this
            ->get('/')
            ->assertStatus(200);
    }

    /**
     * Ensure visitor cannot access admin routes.
     */
    public function test_visitorCannotGetAdminIndex()
    {
        $response = $this
            ->get('/admin')
            ->assertStatus(302);
    }

    /**
     * Ensure user can access admin routes.
     */
    public function test_userCanGetAdminIndex()
    {
        // Try to access admin dashboard
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin')
            ->assertStatus(200);
    }
}
