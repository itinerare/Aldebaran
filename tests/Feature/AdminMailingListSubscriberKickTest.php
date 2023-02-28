<?php

namespace Tests\Feature;

use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminMailingListSubscriberKickTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        MAILING LISTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        config(['aldebaran.settings.email_features' => 1]);

        // Create a mailing list and subscriber to "send" entries to
        $this->mailingList = MailingList::factory()->create();
        $this->subscriber = MailingListSubscriber::factory()
            ->mailingList($this->mailingList->id)->create();
    }

    /**
     * Test subscriber kick access.
     */
    public function testGetKickSubscriber() {
        $this->actingAs($this->user)
            ->get('/admin/mailing-lists/subscriber/'.$this->subscriber->id.'/kick')
            ->assertStatus(200);
    }

    /**
     * Test subscriber kicking.
     */
    public function testPostKickSubscriber() {
        $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/subscriber/'.$this->subscriber->id.'/kick');

        $this->assertModelMissing($this->subscriber);
    }

    /**
     * Test subscriber ban access.
     */
    public function testGetBanSubscriber() {
        $this->actingAs($this->user)
            ->get('/admin/mailing-lists/subscriber/'.$this->subscriber->id.'/ban')
            ->assertStatus(200);
    }

    /**
     * Test subscriber banning.
     */
    public function testPostBanSubscriber() {
        $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/subscriber/'.$this->subscriber->id.'/ban');

        $this->assertModelMissing($this->subscriber);

        $this->assertDatabaseHas('commissioners', [
            'email'     => $this->subscriber->email,
            'is_banned' => 1,
        ]);
    }
}
