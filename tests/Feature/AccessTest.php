<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase {
    use RefreshDatabase;

    /******************************************************************************
        ACCESS/MIDDLEWARE
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test getting the main page.
     * This should be representative of all visitor-accessible routes.
     *
     * @dataProvider accessProvider
     *
     * @param bool $user
     * @param int  $status
     */
    public function testGetIndex($user, $status) {
        if ($user) {
            $response = $this->actingAs($this->user)->get('/');
        } else {
            $response = $this->get('/');
        }

        $response->assertStatus($status);
    }

    public function accessProvider() {
        return [
            'visitor' => [0, 200],
            'user'    => [1, 200],
        ];
    }

    /**
     * Test access to the admin index.
     * This should be representative of all user-only routes.
     *
     * @dataProvider adminAccessProvider
     *
     * @param bool $user
     * @param int  $status
     */
    public function testAdminIndexAccess($user, $status) {
        if ($user) {
            $response = $this->actingAs($this->user)->get('/admin');
        } else {
            $response = $this->get('/admin');
        }

        $response->assertStatus($status);
    }

    public function adminAccessProvider() {
        return [
            'visitor' => [0, 302],
            'user'    => [1, 200],
        ];
    }
}
