<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSiteSettingsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        SITE SETTINGS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        $this->value = $this->faker->unique()->domainWord();
    }

    /**
     * Test site settings access.
     */
    public function testGetSiteSettingsIndex()
    {
        $this->actingAs($this->user)
            ->get('/admin/site-settings')
            ->assertStatus(200);
    }

    /**
     * Test site setting editing.
     *
     * @dataProvider settingsProvider
     *
     * @param string     $key
     * @param mixed|null $value
     */
    public function testPostEditSiteSetting($key, $value = null)
    {
        // Ensure site settings are present to modify
        $this->artisan('add-site-settings');

        // Try to post data
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/site-settings/'.$key, ['value' => isset($value) ? $value : $this->value]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_settings', [
            'key'   => $key,
            'value' => isset($value) ? $value : $this->value,
        ]);
    }

    public function settingsProvider()
    {
        // Values here should *not* be the defaults for the setting
        // For settings which ordinarily use a string, provide null;
        // The test will substitute in a generated string

        return [
            'site name'           => ['site_name', null],
            'site description'    => ['site_desc', null],
            'notification emails' => ['notif_emails', 1],
        ];
    }
}
