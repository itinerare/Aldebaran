<?php

namespace Tests\Feature;

use App\Mail\QuoteRequestAccepted;
use App\Mail\QuoteRequestDeclined;
use App\Mail\QuoteRequestUpdate;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionQuote;
use App\Models\Commission\CommissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminQuoteTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        Mail::fake();

        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();
    }

    /**
     * Test commission viewing.
     *
     * @dataProvider commissionViewProvider
     *
     * @param string $status
     * @param int    $expected
     */
    public function testGetViewQuote($status, $expected) {
        // Create a quote to view, alternately with or without payment information
        $quote = CommissionQuote::factory()
            ->type($this->type->id)->status($status)
            ->create();

        $response = $this->actingAs($this->user)
            ->get('admin/commissions/quotes/edit/'.($expected == 200 ? $quote->id : mt_rand(5, 10)))
            ->assertStatus($expected);
    }

    public function commissionViewProvider() {
        return [
            'pending'  => ['Pending', 200],
            'accepted' => ['Accepted', 200],
            'declined' => ['Declined', 200],
            'complete' => ['Complete', 200],
            'invalid'  => ['Pending', 404],
        ];
    }

    /**
     * Test quote state editing, or operations other than updating.
     * Includes banning commissioner for tidiness.
     *
     * @dataProvider quoteStateProvider
     *
     * @param string $status
     * @param string $operation
     * @param bool   $withComments
     * @param bool   $withCommission
     * @param bool   $sendMail
     * @param bool   $expected
     */
    public function testPostEditQuoteState($status, $operation, $withComments, $withCommission, $sendMail, $expected) {
        if ($withCommission) {
            $commission = Commission::factory()->status($expected ? 'Declined' : 'Accepted')->create();
        }

        $quote = CommissionQuote::factory()->status($status)->create([
            'commission_id' => $withCommission ? $commission->id : null,
        ]);
        $comments = $withComments ? $this->faker->domainWord() : null;

        if ($sendMail) {
            // Enable email notifications
            config(['aldebaran.settings.email_features' => 1]);
            $quote->commissioner->update([
                'receive_notifications' => 1,
            ]);
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/commissions/quotes/edit/'.$quote->id.'/'.$operation, [
                'comments' => $comments,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();

            // Check that the commission has been updated appropriately
            switch ($operation) {
                case 'accept':
                    $this->assertDatabaseHas('commission_quotes', [
                        'id'       => $quote->id,
                        'status'   => 'Accepted',
                        'comments' => $comments ?? null,
                    ]);

                    if ($sendMail) {
                        Mail::assertSent(QuoteRequestAccepted::class);
                    } else {
                        Mail::assertNotSent(QuoteRequestAccepted::class);
                    }
                    break;
                case 'complete':
                    $this->assertDatabaseHas('commission_quotes', [
                        'id'       => $quote->id,
                        'status'   => 'Complete',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'decline':
                    $this->assertDatabaseHas('commission_quotes', [
                        'id'       => $quote->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? null,
                    ]);

                    if ($sendMail) {
                        Mail::assertSent(QuoteRequestDeclined::class);
                    } else {
                        Mail::assertNotSent(QuoteRequestDeclined::class);
                    }
                    break;
                case 'ban':
                    // Check both that the commission and the commissioner have been
                    // updated appropriately
                    $this->assertDatabaseHas('commissioners', [
                        'id'        => $quote->commissioner->id,
                        'is_banned' => 1,
                    ]);

                    $this->assertDatabaseHas('commission_quotes', [
                        'id'       => $quote->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? '<p>Automatically declined due to ban.</p>',
                    ]);
                    break;
            }
        } else {
            $response->assertSessionHasErrors();
        }
    }

    public function quoteStateProvider() {
        return [
            'accept pending'                  => ['Pending', 'accept', 0, 0, 0, 1],
            'accept pending with comments'    => ['Pending', 'accept', 1, 0, 0, 1],
            'accept pending with mail'        => ['Pending', 'accept', 0, 0, 1, 1],
            'decline pending'                 => ['Pending', 'decline', 0, 0, 0, 1],
            'decline pending with comments'   => ['Pending', 'decline', 1, 0, 0, 1],
            'decline pending with mail'       => ['Pending', 'decline', 0, 0, 1, 1],
            'complete pending'                => ['Pending', 'complete', 0, 0, 0, 0],

            'accept accepted'                 => ['Accepted', 'accept', 0, 0, 0, 0],
            'decline accepted'                => ['Accepted', 'decline', 0, 0, 0, 1],
            'decline accepted with comments'  => ['Accepted', 'decline', 1, 0, 0, 1],
            'decline accepted, accepted comm' => ['Accepted', 'decline', 0, 1, 0, 0],
            'decline accepted, declined comm' => ['Accepted', 'decline', 0, 1, 0, 1],
            'complete accepted'               => ['Accepted', 'complete', 0, 0, 0, 1],
            'complete accepted with comments' => ['Accepted', 'complete', 1, 0, 0, 1],

            'ban, pending'                    => ['Pending', 'ban', 0, 0, 0, 1],
            'ban, pending with comments'      => ['Pending', 'ban', 1, 0, 0, 1],
            'ban, accepted'                   => ['Accepted', 'ban', 0, 0, 0, 1],
            'ban, accepted with comments'     => ['Accepted', 'ban', 1, 0, 0, 1],
            'ban, complete'                   => ['Complete', 'ban', 0, 0, 0, 0],
            'ban, declined'                   => ['Declined', 'ban', 0, 0, 0, 0],
        ];
    }

    /**
     * Test quote updating.
     *
     * @dataProvider quoteUpdateProvider
     *
     * @param string $status
     * @param bool   $withAmount
     * @param bool   $withComments
     * @param bool   $sendMail
     * @param bool   $expected
     */
    public function testPostUpdateQuote($status, $withAmount, $withComments, $sendMail, $expected) {
        $quote = CommissionQuote::factory()->status($status)->create();
        $comments = $withComments ? $this->faker->domainWord() : null;
        $amount = (float) mt_rand(1, 50);

        if ($sendMail) {
            // Enable email notifications
            config(['aldebaran.settings.email_features' => 1]);
            $quote->commissioner->update([
                'receive_notifications' => 1,
            ]);
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/commissions/quotes/edit/'.$quote->id.'/update', [
                'comments'          => $comments,
                'amount'            => $withAmount ? $amount : 0.00,
                'send_notification' => $sendMail,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('commission_quotes', [
                'id'       => $quote->id,
                'comments' => $comments,
                'amount'   => $withAmount ? $amount : 0.00,
            ]);

            if ($sendMail) {
                Mail::assertSent(QuoteRequestUpdate::class);
            } else {
                Mail::assertNotSent(QuoteRequestUpdate::class);
            }
        } else {
            $response->assertSessionHasErrors();

            if ($sendMail) {
                Mail::assertNotSent(QuoteRequestUpdate::class);
            }
        }
    }

    public function quoteUpdateProvider() {
        return [
            'basic'           => ['Accepted', 0, 0, 0, 1],
            'with comments'   => ['Accepted', 0, 1, 0, 1],
            'with amount'     => ['Accepted', 1, 1, 0, 1],
            'with mail'       => ['Accepted', 0, 0, 1, 1],

            'update pending'  => ['Pending', 0, 0, 0, 0],
            'update declined' => ['Declined', 0, 0, 0, 0],
            'update complete' => ['Complete', 0, 0, 0, 0],
        ];
    }
}
