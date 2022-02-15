<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountSettingsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        SETTINGS
    *******************************************************************************/

    /**
     * Test email editing.
     */
    public function testCanPostEditEmail()
    {
        // Make a persistent user
        $user = User::factory()->create();

        // Generate an email address
        $email = $this->faker->unique()->safeEmail();

        // Attempt to post data
        $response = $this->actingAs($user)
            ->post('admin/account-settings/email', [
                'email' => $email,
            ]);

        $this->assertDatabaseHas('users', [
            'name'  => $user->name,
            'email' => $email,
        ]);
    }

    /**
     * Test password editing with a valid password.
     * This should work.
     */
    public function testCanPostEditValidPassword()
    {
        // Make a persistent user
        $user = User::factory()->simplePass()->create();

        // Attempt to post data
        $response = $this->actingAs($user)
            ->post('admin/account-settings/password', [
                'old_password'              => 'simple_password',
                'new_password'              => 'password',
                'new_password_confirmation' => 'password',
            ]);

        $this->
            assertTrue(Hash::check('password', $user->fresh()->password));
    }

    /**
     * Test password editing with an invalid password.
     * This shouldn't work.
     */
    public function testCannotPostEditInvalidPassword()
    {
        // Make a persistent user
        $user = User::factory()->simplePass()->create();

        // Attempt to post data
        $response = $this->actingAs($user)
            ->post('admin/account-settings/password', [
                'old_password'              => 'simple_password',
                'new_password'              => 'password',
                'new_password_confirmation' => 'not_password',
            ]);

        $response->assertSessionHasErrors();
    }
}
