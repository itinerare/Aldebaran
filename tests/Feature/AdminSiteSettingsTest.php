<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionClass;
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

        // Generate some test data
        $this->value = $this->faker->unique()->domainWord();

        // Ensure site settings are present
        $this->artisan('add-site-settings');
    }

    /**
     * Test site settings access.
     *
     * @dataProvider settingsViewProvider
     *
     * @param bool $commsEnabled
     * @param int  $expected
     */
    public function testGetSiteSettingsIndex($commsEnabled, $expected)
    {
        // Adjust commission enable/disable as appropriate
        config(['aldebaran.settings.commissions.enabled' => $commsEnabled]);

        if ($commsEnabled) {
            // Create testing class
            $this->class = CommissionClass::factory()->create();
        }

        $this->actingAs($this->user)
            ->get('/admin/site-settings')
            ->assertStatus($expected);
    }

    public function settingsViewProvider()
    {
        return [
            'basic'               => [0, 200],
            'commissions enabled' => [1, 200],
        ];
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
        // Try to post data
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/site-settings/'.$key, [$key.'_value' => $value ?? $this->value]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_settings', [
            'key'   => $key,
            'value' => $value ?? $this->value,
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
