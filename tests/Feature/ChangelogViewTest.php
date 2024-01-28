<?php

namespace Tests\Feature;

use App\Models\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangelogViewTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: CHANGELOG
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test changelog access.
     *
     * @dataProvider changelogProvider
     *
     * @param bool  $user
     * @param array $changelogStatus
     * @param int   $status
     */
    public function testGetChangelog($user, $changelogStatus, $status) {
        if ($changelogStatus[0]) {
            // Create a changelog to view
            $changelog = Changelog::factory()->create();

            $changelog->update([
                'is_visible' => $changelogStatus[1],
                'name'       => $changelogStatus[2] ? $this->faker->unique()->domainWord() : null,
            ]);
        }

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get('changelog');
        } else {
            $response = $this->get('changelog');
        }

        $response->assertStatus($status);

        if ($changelogStatus[0] && $status == 200) {
            // Test that the changelog is displayed or not depending on
            // auth status and piece visibility
            $response->assertViewHas('changelogs', function ($changelogs) use ($user, $changelogStatus, $changelog) {
                if ($user || $changelogStatus[1]) {
                    return $changelogs->contains($changelog);
                } else {
                    return !$changelogs->contains($changelog);
                }
            });
        }
    }

    public static function changelogProvider() {
        return [
            'visitor'                               => [0, [0, 0, 0], 200],
            'user'                                  => [1, [0, 0, 0], 200],
            'visitor with visible changelog'        => [0, [1, 1, 0], 200],
            'visitor, visible changelog with title' => [0, [1, 1, 1], 200],
            'visitor with hidden changelog'         => [0, [1, 0, 0], 200],
            'visitor, hidden changelog with title'  => [0, [1, 0, 1], 200],
            'user with visible changelog'           => [0, [1, 1, 0], 200],
            'user, visible changelog with title'    => [0, [1, 1, 1], 200],
            'user with hidden changelog'            => [0, [1, 0, 0], 200],
            'user, hidden changelog with title'     => [0, [1, 0, 1], 200],
        ];
    }
}
