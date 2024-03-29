<?php

namespace Tests\Feature;

use App\Mail\CommissionInvoicePaid;
use App\Mail\CommissionRequestAccepted;
use App\Mail\CommissionRequestConfirmation;
use App\Mail\CommissionRequestDeclined;
use App\Mail\CommissionRequested;
use App\Mail\CommissionRequestUpdate;
use App\Mail\MailListEntry;
use App\Mail\QuoteRequestAccepted;
use App\Mail\QuoteRequestConfirmation;
use App\Mail\QuoteRequestDeclined;
use App\Mail\QuoteRequested;
use App\Mail\QuoteRequestUpdate;
use App\Mail\VerifyMailingListSubscription;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionPiece;
use App\Models\Commission\CommissionQuote;
use App\Models\Gallery\Piece;
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
     * Test commission notification email contents.
     *
     * @dataProvider commissionNotificationProvider
     *
     * @param string     $mailType
     * @param string     $status
     * @param array|null $payment
     * @param bool       $withPiece
     */
    public function testCommissionNotification($mailType, $status, $payment, $withPiece) {
        $commission = Commission::factory()->status($status)->create();

        if ($payment) {
            $payment = CommissionPayment::factory()->create([
                'commission_id' => $commission->id,
                'is_paid'       => $payment[0],
            ]);
        }

        if ($withPiece) {
            // Create a piece and link to the commission
            $piece = Piece::factory()->create();
            CommissionPiece::factory()->piece($piece->id)->commission($commission->id)->create();
        }

        switch ($mailType) {
            case 'CommissionRequested':
                $mailable = new CommissionRequested($commission);
                $mailable->assertHasSubject('New Commission Request');
                break;
            case 'CommissionRequestConfirmation':
                $mailable = new CommissionRequestConfirmation($commission);
                $mailable->assertHasSubject('Commission Request Confirmation (#'.$commission->id.')');
                break;
            case 'CommissionRequestDeclined':
                $mailable = new CommissionRequestDeclined($commission);
                $mailable->assertHasSubject('Commission Request Declined (#'.$commission->id.')');
                break;
            case 'CommissionRequestAccepted':
                $mailable = new CommissionRequestAccepted($commission);
                $mailable->assertHasSubject('Commission Request Accepted (#'.$commission->id.')');
                break;
            case 'CommissionRequestUpdate':
                $mailable = new CommissionRequestUpdate($commission);
                $mailable->assertHasSubject('Commission Updated (#'.$commission->id.')');

                if ($withPiece) {
                    $mailable->assertSeeInText('has 1 piece');
                }
                if ($payment) {
                    if ($payment->is_paid) {
                        $mailable->assertDontSeeInText('your commission is marked unpaid');
                    } else {
                        $mailable->assertSeeInText('your commission is marked unpaid');
                    }
                }
                break;
            case 'CommissionInvoicePaid':
                $mailable = new CommissionInvoicePaid($commission);
                $mailable->assertHasSubject('Commission (#'.$commission->id.') Invoice Paid');
        }

        if ($mailType == 'CommissionRequested' || $mailType == 'CommissionInvoicePaid') {
            $mailable->assertSeeInHtml($commission->adminUrl);
        } else {
            $mailable->assertSeeInHtml($commission->url);
        }
    }

    public static function commissionNotificationProvider() {
        return [
            'new request'                            => ['CommissionRequested', 'Pending', null, 0],
            'new request confirmation'               => ['CommissionRequestConfirmation', 'Pending', null, 0],
            'declined request'                       => ['CommissionRequestDeclined', 'Declined', null, 0],
            'accepted request'                       => ['CommissionRequestAccepted', 'Accepted', null, 0],
            'updated commission'                     => ['CommissionRequestUpdate', 'Accepted', null, 0],
            'updated commission with piece'          => ['CommissionRequestUpdate', 'Accepted', null, 1],
            'updated commission with unpaid payment' => ['CommissionRequestUpdate', 'Accepted', [0], 0],
            'updated commission with paid payment'   => ['CommissionRequestUpdate', 'Accepted', [1], 0],
            'invoice paid'                           => ['CommissionInvoicePaid', 'Accepted', [1], 0],
        ];
    }

    /**
     * Test quote notification email contents.
     *
     * @dataProvider quoteNotificationProvider
     *
     * @param string $mailType
     * @param string $status
     */
    public function testQuoteNotification($mailType, $status) {
        $quote = CommissionQuote::factory()->status($status)->create();

        switch ($mailType) {
            case 'QuoteRequested':
                $mailable = new QuoteRequested($quote);
                $mailable->assertHasSubject('New Quote Request');
                break;
            case 'QuoteRequestConfirmation':
                $mailable = new QuoteRequestConfirmation($quote);
                $mailable->assertHasSubject('Quote Request Confirmation (#'.$quote->id.')');
                break;
            case 'QuoteRequestDeclined':
                $mailable = new QuoteRequestDeclined($quote);
                $mailable->assertHasSubject('Quote Request Declined (#'.$quote->id.')');
                break;
            case 'QuoteRequestAccepted':
                $mailable = new QuoteRequestAccepted($quote);
                $mailable->assertHasSubject('Quote Request Accepted (#'.$quote->id.')');
                break;
            case 'QuoteRequestUpdate':
                $mailable = new QuoteRequestUpdate($quote);
                $mailable->assertHasSubject('Quote Updated (#'.$quote->id.')');
        }

        if ($mailType == 'QuoteRequested') {
            $mailable->assertSeeInHtml($quote->adminUrl);
        } else {
            $mailable->assertSeeInHtml($quote->url);
        }
    }

    public static function quoteNotificationProvider() {
        return [
            'new request'              => ['QuoteRequested', 'Pending'],
            'new request confirmation' => ['QuoteRequestConfirmation', 'Pending'],
            'declined request'         => ['QuoteRequestDeclined', 'Declined'],
            'accepted request'         => ['QuoteRequestAccepted', 'Accepted'],
            'updated quote'            => ['QuoteRequestUpdate', 'Accepted'],
        ];
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
