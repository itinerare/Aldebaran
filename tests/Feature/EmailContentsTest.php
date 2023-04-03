<?php

namespace Tests\Feature;

use App\Mail\CommissionRequested;
use App\Mail\MailListEntry;
use App\Mail\QuoteRequested;
use App\Mail\VerifyMailingListSubscription;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionQuote;
use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListEntry;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmailContentsTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        EMAIL CONTENTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test new commission notification email contents.
     */
    public function testNewCommissionRequestNotification() {
        $commission = Commission::factory()->create();
        $mailable = new CommissionRequested($commission);

        $mailable->assertHasSubject('New Commission Request');
        $mailable->assertSeeInHtml(url('admin/commissions/edit/'.$commission->id));
    }

    /**
     * Test new quote notification email contents.
     */
    public function testNewQuoteRequestNotification() {
        $quote = CommissionQuote::factory()->create();
        $mailable = new QuoteRequested($quote);

        $mailable->assertHasSubject('New Quote Request');
        $mailable->assertSeeInHtml(url('admin/commissions/quotes/edit/'.$quote->id));
    }

    /**
     * Test mailing list verification email contents.
     */
    public function testMailingListVerification() {
        $mailingList = MailingList::factory()->create();
        $subscriber = MailingListSubscriber::factory()
            ->mailingList($mailingList->id)->create();

        $mailable = new VerifyMailingListSubscription($subscriber);

        $mailable->assertHasSubject('Verify Mailing List Subscription');
        $mailable->assertSeeInHtml($subscriber->verifyUrl);
    }

    /**
     * Test mailing list entry email contents.
     */
    public function testMailingListEntry() {
        $mailingList = MailingList::factory()->create();
        $entry = MailingListEntry::factory()
            ->mailingList($mailingList->id)->create();
        $subscriber = MailingListSubscriber::factory()
            ->mailingList($mailingList->id)->create();

        $mailable = new MailListEntry($entry, $subscriber);

        $mailable->assertHasSubject($entry->subject);
        $mailable->assertSeeInHtml($subscriber->unsubscribeUrl);
    }
}
