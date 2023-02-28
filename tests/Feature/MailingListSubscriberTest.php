<?php

namespace Tests\Feature;

use App\Mail\VerifyMailingListSubscription;
use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailingListSubscriberTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        MAILING LISTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        Mail::fake();

        // Create some mailing lists and subscribers
        $this->mailingList = MailingList::factory()->create();
        $this->closedList = MailingList::factory()->closed()->create();
        $this->subscriber = MailingListSubscriber::factory()
            ->mailingList($this->mailingList->id)->create();
        $this->verifiedSubscriber = MailingListSubscriber::factory()
            ->mailingList($this->mailingList->id)->verified()->create();

        // Generate a fake email address
        $this->email = $this->faker()->email();
    }

    /**
     * Test subscription page access.
     *
     * @dataProvider mailingListViewProvider
     *
     * @param bool  $mailEnabled
     * @param array $list
     * @param int   $expected
     */
    public function testGetMailingListPage($mailEnabled, $list, $expected) {
        if ($mailEnabled) {
            config(['aldebaran.settings.email_features' => 1]);
        }

        if ($list) {
            if ($list[1]) {
                $mailingList = $this->mailingList;
            } elseif (!$list[1]) {
                $mailingList = $this->closedList;
            }
        }

        $response = $this
            ->get('/mailing-lists/'.($list && isset($mailingList) ? $mailingList->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    public function mailingListViewProvider() {
        return [
            'mail enabled, valid, open'      => [1, [1, 1], 200],
            'mail enabled, valid, closed'    => [1, [1, 0], 404],
            'mail enabled, invalid'          => [1, null, 404],
            'mail disabled, valid, open'     => [0, [1, 1], 404],
            'mail disabled, valid, closed'   => [0, [1, 0], 404],
            'mail disabled, invalid'         => [0, null, 404],
        ];
    }

    /**
     * Test mailing list subscription.
     *
     * @dataProvider mailingListSubscribeProvider
     *
     * @param bool  $mailEnabled
     * @param array $list
     * @param bool  $expected
     */
    public function testPostMailingListSubscription($mailEnabled, $list, $expected) {
        if ($mailEnabled) {
            config(['aldebaran.settings.email_features' => 1]);
        }

        if ($list) {
            if ($list[1]) {
                $mailingList = $this->mailingList;
            } elseif (!$list[1]) {
                $mailingList = $this->closedList;
            }
        }

        $response = $this
            ->post('/mailing-lists/'.($list && isset($mailingList) ? $mailingList->id : mt_rand(5, 10)).'/subscribe', [
                'email' => $this->email,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('mailing_list_subscribers', [
                'email' => $this->email,
            ]);

            $subscriber = MailingListSubscriber::where('email', $this->email)->where('is_verified', 0)->first();

            Mail::assertSent(function (VerifyMailingListSubscription $mail) use ($subscriber) {
                return $mail->subscriber->id === $subscriber->id;
            });
        } else {
            $response->assertSessionHasErrors();
            Mail::assertNothingSent(VerifyMailingListSubscription::class);
        }
    }

    public function mailingListSubscribeProvider() {
        return [
            'mail enabled, valid, open'      => [1, [1, 1], 1],
            'mail enabled, valid, closed'    => [1, [1, 0], 0],
            'mail enabled, invalid'          => [1, null, 0],
            'mail disabled, valid, open'     => [0, [1, 1], 0],
            'mail disabled, valid, closed'   => [0, [1, 0], 0],
            'mail disabled, invalid'         => [0, null, 0],
        ];
    }

    /**
     * Test subscription verification.
     *
     * @dataProvider subscriptionVerificationProvider
     *
     * @param bool  $mailEnabled
     * @param array $subscription
     * @param bool  $expected
     * @param int   $status
     */
    public function testGetSubscriptionVerification($mailEnabled, $subscription, $expected, $status) {
        if ($mailEnabled) {
            config(['aldebaran.settings.email_features' => 1]);
        }

        if ($subscription) {
            if ($subscription[0]) {
                $subscriber = $this->verifiedSubscriber;
            } elseif (!$subscription[0]) {
                $subscriber = $this->subscriber;
            }
        }

        $url = '/mailing-lists/verify/';
        if ($subscription) {
            $url = $url.$subscriber->id.($subscription[1] ? '?token='.$subscriber->token : '');
        } else {
            $url = $url.mt_rand(5, 10);
        }

        $response = $this
            ->get($url)->assertStatus($status);

        if ($status == 302) {
            if ($expected) {
                $response->assertSessionHasNoErrors();
                $this->assertDatabaseHas('mailing_list_subscribers', [
                    'id'          => $subscriber->id,
                    'is_verified' => 1,
                ]);
            } else {
                $response->assertSessionHasErrors();
            }
        }
    }

    public function subscriptionVerificationProvider() {
        return [
            'mail enabled, unverified, with token'     => [1, [0, 1], 1, 302],
            'mail enabled, unverified, without token'  => [1, [0, 0], 0, 404],
            'mail enabled, verified, with token'       => [1, [1, 1], 0, 302],
            'mail enabled, verified, without token'    => [1, [1, 0], 0, 404],
            'mail enabled, invalid'                    => [1, null, 0, 404],
            'mail disabled, unverified, with token'    => [0, [0, 1], 1, 302],
            'mail disabled, unverified, without token' => [0, [0, 0], 0, 404],
            'mail disabled, verified, with token'      => [0, [1, 1], 0, 302],
            'mail disabled, verified, without token'   => [0, [1, 0], 0, 404],
            'mail disabled, invalid'                   => [0, null, 0, 404],
        ];
    }

    /**
     * Test unsubscription.
     *
     * @dataProvider unsubscriptionProvider
     *
     * @param bool  $mailEnabled
     * @param array $subscription
     * @param bool  $expected
     * @param int   $status
     */
    public function testGetUnsubscribe($mailEnabled, $subscription, $expected, $status) {
        if ($mailEnabled) {
            config(['aldebaran.settings.email_features' => 1]);
        }

        // Unsubscription is technically accessible at any time,
        // though the link is only provided when mailing list entries are sent out
        if ($subscription) {
            if ($subscription[0]) {
                $subscriber = $this->verifiedSubscriber;
            } elseif (!$subscription[0]) {
                $subscriber = $this->subscriber;
            }
        }

        $url = '/mailing-lists/unsubscribe/';
        if ($subscription) {
            $url = $url.$subscriber->id.($subscription[1] ? '?token='.$subscriber->token : '');
        } else {
            $url = $url.mt_rand(5, 10);
        }

        $response = $this
            ->get($url)->assertStatus($status);

        if ($status == 302) {
            if ($expected) {
                $response->assertSessionHasNoErrors();
                $this->assertModelMissing($subscriber);
            } else {
                $response->assertSessionHasErrors();
                $this->assertModelExists($subscriber);
            }
        }
    }

    public function unsubscriptionProvider() {
        return [
            'mail enabled, unverified, with token'     => [1, [0, 1], 1, 302],
            'mail enabled, unverified, without token'  => [1, [0, 0], 0, 404],
            'mail enabled, verified, with token'       => [1, [1, 1], 1, 302],
            'mail enabled, verified, without token'    => [1, [1, 0], 0, 404],
            'mail enabled, invalid'                    => [1, null, 0, 404],
            'mail disabled, unverified, with token'    => [0, [0, 1], 1, 302],
            'mail disabled, unverified, without token' => [0, [0, 0], 0, 404],
            'mail disabled, verified, with token'      => [0, [1, 1], 1, 302],
            'mail disabled, verified, without token'   => [0, [1, 0], 0, 404],
            'mail disabled, invalid'                   => [0, null, 0, 404],
        ];
    }
}
