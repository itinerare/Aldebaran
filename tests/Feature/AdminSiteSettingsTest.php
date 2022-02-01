<?php

namespace Tests\Feature;

use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    /******************************************************************************
        SITE SETTINGS
    *******************************************************************************/

    /**
     * Test site settings access.
     */
    public function test_canGetSiteSettingsIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/site-settings')
            ->assertStatus(200);
    }

    /**
     * Test site setting editing.
     */
    public function test_canPostEditSiteSetting()
    {
        // Ensure site settings are present to modify
        $this->artisan('add-site-settings');

        // Make sure the setting is true so as to consistently test
        DB::table('site_settings')->where('key', 'commissions_on')->update(['value' => 1]);

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/site-settings/commissions_on', ['value' => 0]);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('site_settings', [
            'key'   => 'commissions_on',
            'value' => 0,
        ]);
    }
}
