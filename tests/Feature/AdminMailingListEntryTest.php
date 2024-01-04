<?php

namespace Tests\Feature;

use App\Mail\MailListEntry;
use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListEntry;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminMailingListEntryTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        MAILING LISTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        config(['aldebaran.settings.email_features' => 1]);

        Mail::fake();

        // Create a mailing list and subscriber to "send" entries to
        $this->mailingList = MailingList::factory()->create();
        $this->subscriber = MailingListSubscriber::factory()
            ->mailingList($this->mailingList->id)->create();

        // As well as some entries to use for testing
        $this->entry = MailingListEntry::factory()
            ->mailingList($this->mailingList->id)->create();
        $this->sentEntry = MailingListEntry::factory()
            ->mailingList($this->mailingList->id)->sent()->create();

        // Generate subject and text values
        $this->subject = $this->faker()->unique()->domainWord();
        $this->text = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test entry create access.
     */
    public function testGetCreateMailingListEntry() {
        $response = $this->actingAs($this->user)
            ->get('/admin/mailing-lists/entries/create/'.$this->mailingList->id)
            ->assertStatus(200);
    }

    /**
     * Test entry edit access.
     */
    public function testGetEditMailingListEntry() {
        $response = $this->actingAs($this->user)
            ->get('/admin/mailing-lists/entries/edit/'.$this->entry->id)
            ->assertStatus(200);
    }

    /**
     * Test entry creation.
     *
     * @dataProvider mailingListEntryCreateProvider
     *
     * @param bool $isDraft
     */
    public function testPostCreateMailingListEntry($isDraft) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/entries/create', [
                'mailing_list_id' => $this->mailingList->id,
                'subject'         => $this->subject,
                'text'            => $this->text,
                'is_draft'        => $isDraft,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mailing_list_entries', [
            'subject'    => $this->subject,
            'text'       => $this->text,
            'is_draft'   => $isDraft,
        ]);

        if (!$isDraft) {
            $entry = MailingListEntry::where('subject', $this->subject)->where('text', $this->text)->where('is_draft', $isDraft)->first();

            Mail::assertQueued(function (MailListEntry $mail) use ($entry) {
                return $mail->entry->id === $entry->id;
            });
        } else {
            Mail::assertNotQueued(MailListEntry::class);
        }
    }

    public static function mailingListEntryCreateProvider() {
        return [
            'draft'   => [1],
            'sending' => [0],
        ];
    }

    /**
     * Test entry editing.
     *
     * @dataProvider mailingListEntryEditProvider
     *
     * @param bool $isSent
     * @param bool $isDraft
     * @param bool $expected
     */
    public function testPostEditMailingListEntry($isSent, $isDraft, $expected) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/entries/edit/'.($isSent ? $this->sentEntry->id : $this->entry->id), [
                'subject'    => $this->subject,
                'text'       => $this->text,
                'is_draft'   => $isDraft,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('mailing_list_entries', [
                'id'         => $isSent ? $this->sentEntry->id : $this->entry->id,
                'subject'    => $this->subject,
                'text'       => $this->text,
                'is_draft'   => $isDraft,
            ]);

            if (!$isDraft && !$isSent) {
                $entry = $this->entry;
                Mail::assertQueued(function (MailListEntry $mail) use ($entry) {
                    return $mail->entry->id === $entry->id;
                });
            } else {
                Mail::assertNotQueued(MailListEntry::class);
            }
        } else {
            $response->assertSessionHasErrors();
            Mail::assertNotQueued(MailListEntry::class);
        }
    }

    public static function mailingListEntryEditProvider() {
        return [
            'draft'   => [0, 1, 1],
            'sending' => [0, 0, 1],
            'sent'    => [1, 0, 0],
        ];
    }

    /**
     * Test entry delete access.
     */
    public function testGetDeleteMailingListEntry() {
        $this->actingAs($this->user)
            ->get('/admin/mailing-lists/entries/delete/'.$this->entry->id)
            ->assertStatus(200);
    }

    /**
     * Test entry deletion.
     */
    public function testPostDeleteMailingListEntry() {
        $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/entries/delete/'.$this->entry->id);

        $this->assertModelMissing($this->entry);
    }
}
