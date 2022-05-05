<?php

namespace Tests\Feature;

use App\Facades\Settings;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionPiece;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCommissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();

        // Set up gallery service for image processing
        $this->service = new GalleryService;
    }

    /**
     * Test commission viewing.
     *
     * @dataProvider commissionViewProvider
     *
     * @param array|null $pieceData
     * @param bool       $withPayment
     * @param string     $status
     * @param array|null $fieldData
     * @param int        $expected
     */
    public function testGetViewCommission($pieceData, $withPayment, $status, $fieldData, $expected)
    {
        if ($fieldData) {
            // Generate some keys so they can be referred back to later
            $fieldKeys = [
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
            ];

            $this->type->update([
                'data' => '{"fields":{"'.Str::lower($fieldKeys[0]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"'.$fieldData[0].'","rules":'.($fieldData[1] ? '"required"' : 'null').',"choices":'.($fieldData[2] ? '["option 1","option 2"]' : 'null').',"value":'.($fieldData[3] ? '"'.$fieldData[3].'"' : 'null').',"help":'.($fieldData[4] ? '"'.$fieldData[4].'"' : 'null').'}},"include":{"class":'.$fieldData[6].',"category":'.$fieldData[5].'},"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
            ]);

            if ($fieldData[5]) {
                $this->type->category->update([
                    'data' => '{"fields":{"'.Str::lower($fieldKeys[1]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":0}}',
                ]);
            }

            if ($fieldData[6]) {
                $this->type->category->class->update([
                    'data' => '{"fields":{"'.Str::lower($fieldKeys[2]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}}}',
                ]);
            }

            if ($fieldData[7]) {
                switch ($fieldData[0]) {
                    case 'text':
                        $answer = $this->faker->domainWord();
                        break;
                    case 'textarea':
                        $answer = $this->faker->domainWord();
                        break;
                    case 'number':
                        $answer = (string) mt_rand(1, 10);
                        break;
                    case 'checkbox':
                        $answer = (string) 1;
                        break;
                    case 'choice':
                        $answer = (string) 0;
                        break;
                    case 'multiple':
                        $answer = [0 => (string) 0];
                        break;
                }
            } else {
                $answer = null;
            }
        }

        // Create a commission to view, alternately with or without payment information
        if ($withPayment) {
            $commission = Commission::factory()
                ->type($this->type->id)->has(CommissionPayment::factory()->count(2), 'payments')->status($status)
                ->create();
        } else {
            $commission = Commission::factory()
                ->type($this->type->id)->status($status)
                ->create();
        }

        if ($pieceData) {
            // Create a piece and link to the commission
            $piece = Piece::factory()->create([
                'is_visible' => $pieceData[1],
            ]);
            CommissionPiece::factory()->piece($piece->id)->commission($commission->id)->create();

            if ($pieceData[0]) {
                // Create images and test files
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($image);
            }
        }

        // Adjust commission data as necessary
        $commission->update([
            'data' => $fieldData && (isset($answer) || $fieldData[5] || $fieldData[6]) ? '{'.($fieldData[6] ? '"'.$fieldKeys[2].'":"test",' : '').($fieldData[5] ? '"'.$fieldKeys[1].'":"test",' : '').'"'.$fieldKeys[0].'":'.(isset($answer) ? ($fieldData[0] != 'multiple' ? '"'.$answer.'"' : '["'.$answer[0].'"]') : 'null').'}' : null,
        ]);

        $response = $this->actingAs($this->user)
            ->get('admin/commissions/edit/'.$commission->id)
            ->assertStatus($expected);

        if ($pieceData) {
            if ($expected) {
                // Check that the piece is displayed in some fashion
                $response->assertSee($piece->name);

                if ($pieceData[0]) {
                    // Check that the image's thumbnail is present/displayed
                    $response->assertSee($image->thumbnailUrl);
                }
            }

            if ($pieceData[0]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }
        }
    }

    public function commissionViewProvider()
    {
        return [
            'basic'        => [null, 0, 'Pending', null, 200],
            'with payment' => [null, 1, 'Pending', null, 200],
            'accepted'     => [null, 0, 'Accepted', null, 200],
            'declined'     => [null, 0, 'Declined', null, 200],
            'complete'     => [null, 0, 'Complete', null, 200],

            // $pieceData = [(bool) withImage, (bool) isVisible]
            'with piece'            => [[0, 1], 0, 'Accepted', null, 200],
            'with hidden piece'     => [[0, 0], 0, 'Accepted', null, 200],
            'with piece with image' => [[1, 1], 0, 'Accepted', null, 200],

            // Field testing
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) is empty
            'text field'                   => [null, 0, 'Pending', ['text', 0, 0, null, null, 0, 0, 1], 200],
            'text field, empty'            => [null, 0, 'Pending', ['text', 0, 0, null, null, 0, 0, 0], 200],
            'text field with rule'         => [null, 0, 'Pending', ['text', 1, 0, null, null, 0, 0, 1], 200],
            'text field with value'        => [null, 0, 'Pending', ['text', 0, 0, 'test', null, 0, 0, 1], 200],
            'text field with help'         => [null, 0, 'Pending', ['text', 0, 0, null, 'test', 0, 0, 1], 200],
            'textbox field'                => [null, 0, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 1], 200],
            'textbox field, empty'         => [null, 0, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 0], 200],
            'number field'                 => [null, 0, 'Pending', ['number', 0, 0, null, null, 0, 0, 1], 200],
            'number field, empty'          => [null, 0, 'Pending', ['number', 0, 0, null, null, 0, 0, 0], 200],
            'checkbox field'               => [null, 0, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 1], 200],
            'checkbox field, empty'        => [null, 0, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 0], 200],
            'choose one field'             => [null, 0, 'Pending', ['choice', 0, 0, null, null, 0, 0, 1], 200],
            'choose one field, empty'      => [null, 0, 'Pending', ['choice', 0, 0, null, null, 0, 0, 0], 200],
            'choose multiple field'        => [null, 0, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 1], 200],
            'choose multiple field, empty' => [null, 0, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 0], 200],

            'include from category'           => [null, 0, 'Pending', ['text', 0, 0, null, null, 1, 0, 1], 200],
            'include from class'              => [null, 0, 'Pending', ['text', 0, 0, null, null, 0, 1, 1], 200],
            'include from category and class' => [null, 0, 'Pending', ['text', 0, 0, null, null, 1, 1, 1], 200],
        ];
    }

    /**
     * Test commission state editing, or operations other than updating.
     * Includes banning commissioner for tidiness.
     *
     * @dataProvider commissionStateProvider
     *
     * @param string     $status
     * @param string     $operation
     * @param bool       $withComments
     * @param array|null $slotData
     * @param bool       $expected
     */
    public function testPostEditCommissionState($status, $operation, $withComments, $slotData, $expected)
    {
        $commission = Commission::factory()->status($status)->create();
        $comments = $withComments ? $this->faker->domainWord() : null;

        if ($slotData) {
            // Handle filler commission info to test slot-related operations
            $slotCommission = Commission::factory()->status($slotData[0])->create();

            if ($slotData[1]) {
                // Adjust settings for same-type tests
                $slotCommission->update([
                    'commission_type' => $commission->type->id,
                ]);

                $commission->type->update([
                    'availability' => 1,
                ]);
            } else {
                // Adjust settings for same-class tests
                // as this is the only other relevant state

                // To be safe, assign the commission to a different type
                // in the same category
                $type = CommissionType::factory()->category($commission->type->category->id)->create();

                $slotCommission->update([
                    'commission_type' => $type->id,
                ]);

                DB::table('site_settings')->where('key', $commission->type->category->class->slug.'_overall_slots')->update([
                    'value' => 1,
                ]);
            }
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/commissions/edit/'.$commission->id.'/'.$operation, [
                'comments' => $comments,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();

            // Check that the commission has been updated appropriately
            switch ($operation) {
                case 'accept':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Accepted',
                        'comments' => $comments ?? null,
                    ]);

                    if ($slotData) {
                        $this->assertDatabaseHas('commissions', [
                            'id'       => $slotCommission->id,
                            'status'   => $slotData[2] ? 'Accepted' : 'Declined',
                            'comments' => $slotData[2] ? null : ($slotData[1] ? '<p>Sorry, all slots for this commission type have been filled! '.Settings::get($commission->type->category->class->slug.'_full').'</p>' : '<p>Sorry, all slots have been filled! '.Settings::get($commission->type->category->class->slug.'_full').'</p>'),
                        ]);
                    }
                    break;
                case 'complete':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Complete',
                        'progress' => 'Finished',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'decline':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'ban':
                    // Check both that the commission and the commissioner have been
                    // updated appropriately
                    $this->assertDatabaseHas('commissioners', [
                        'id'        => $commission->commissioner->id,
                        'is_banned' => 1,
                    ]);

                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? '<p>Automatically declined due to ban.</p>',
                    ]);
                    break;
            }
        } else {
            $response->assertSessionHasErrors();
        }
    }

    public function commissionStateProvider()
    {
        // $slotData = (string) status, (bool) sameType, (bool) expected

        return [
            'accept pending'                 => ['Pending', 'accept', 0, null, 1],
            'accept pending with comments'   => ['Pending', 'accept', 1, null, 1],
            'decline pending'                => ['Pending', 'decline', 0, null, 1],
            'decline pending with comments'  => ['Pending', 'decline', 1, null, 1],
            'complete pending'               => ['Pending', 'complete', 0, null, 0],
            'accept pending with full type'  => ['Pending', 'accept', 0, ['Accepted', 1, 1], 0],
            'accept pending with full class' => ['Pending', 'accept', 0, ['Accepted', 0, 1], 0],
            'accept pending, filling type'   => ['Pending', 'accept', 0, ['Pending', 1, 0], 1],
            'accept pending, filling class'  => ['Pending', 'accept', 0, ['Pending', 0, 0], 1],

            'accept accepted'                 => ['Accepted', 'accept', 0, null, 0],
            'decline accepted'                => ['Accepted', 'decline', 0, null, 1],
            'decline accepted with comments'  => ['Accepted', 'decline', 1, null, 1],
            'complete accepted'               => ['Accepted', 'complete', 0, null, 1],
            'complete accepted with comments' => ['Accepted', 'complete', 1, null, 1],

            'ban, pending'                => ['Pending', 'ban', 0, null, 1],
            'ban, pending with comments'  => ['Pending', 'ban', 1, null, 1],
            'ban, accepted'               => ['Accepted', 'ban', 0, null, 1],
            'ban, accepted with comments' => ['Accepted', 'ban', 1, null, 1],
            'ban, complete'               => ['Complete', 'ban', 0, null, 0],
            'ban, declined'               => ['Declined', 'ban', 0, null, 0],
        ];
    }

    /**
     * Test commission updating.
     *
     * @dataProvider commissionUpdateProvider
     *
     * @param string     $status
     * @param bool       $withPiece
     * @param array|null $paymentData
     * @param string     $progress
     * @param bool       $withComments
     * @param bool       $expected
     */
    public function testPostUpdateCommission($status, $withPiece, $paymentData, $progress, $withComments, $expected)
    {
        $commission = Commission::factory()->status($status)->create();
        $comments = $withComments ? $this->faker->domainWord() : null;

        if ($withPiece) {
            // Create a piece to attach
            $piece = Piece::factory()->create();
        }

        if ($paymentData) {
            // Set up some payment info such that it can be retrieved later
            $costData = [
                'cost' => mt_rand(1.00, 50.00),
                'tip'  => mt_rand(1.00, 50.00),
            ];

            if ($paymentData[0]) {
                // If needed, generate an existing payment entry to modify
                $payment = CommissionPayment::factory()->create([
                    'commission_id' => $commission->id,
                ]);
            }
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/commissions/edit/'.$commission->id.'/update', [
                'pieces'   => $withPiece ? [0 => $piece->id] : null,
                'progress' => $progress,
                'comments' => $comments,
                'cost'     => $paymentData ? [$paymentData[0] ? $payment->id : 0 => $costData['cost']] : null,
                'tip'      => $paymentData ? [$paymentData[0] ? $payment->id : 0 => $costData['tip']] : null,
                'is_intl'  => isset($payment) ? [$payment->id => $paymentData[2]] : null,
                'is_paid'  => isset($payment) ? [$payment->id => $paymentData[1]] : null,
                'paid_at'  => isset($payment) ? [$payment->id => $paymentData[1] ? $payment->paid_at : null] : null,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('commissions', [
                'id'       => $commission->id,
                'comments' => $comments,
                'progress' => $progress,
            ]);

            if ($withPiece) {
                // Check that the link to the piece has been created
                $this->assertDatabaseHas('commission_pieces', [
                    'commission_id' => $commission->id,
                    'piece_id'      => $piece->id,
                ]);
            }

            if ($paymentData) {
                // Check that the payment has been added or updated appropriately
                $this->assertDatabaseHas('commission_payments', [
                    'commission_id' => $commission->id,
                    'cost'          => $costData['cost'],
                    'tip'           => $costData['tip'],
                    'is_paid'       => isset($payment) && $paymentData[1] ? $paymentData[1] : 0,
                    'is_intl'       => isset($payment) && $paymentData[2] ? $paymentData[2] : 0,
                ]);

                if ($paymentData[1]) {
                    // If relevant, check that paid_at is now set for the payment
                    // This is a bit of a hackneyed workaround for retrieving
                    // casted info being janky in a test environment at present
                    $this->assertDatabaseMissing('commission_payments', [
                        'commission_id' => $commission->id,
                        'paid_at'       => null,
                    ]);
                }
            }
        } else {
            $response->assertSessionHasErrors();
        }
    }

    public function commissionUpdateProvider()
    {
        return [
            'basic'         => ['Accepted', 0, null, 'Not Started', 0, 1],
            'with piece'    => ['Accepted', 1, null, 'Not Started', 0, 1],
            'with progress' => ['Accepted', 0, null, 'Working On', 0, 1],
            'with comments' => ['Accepted', 0, null, 'Not Started', 1, 1],

            // $paymentData = [(bool) existingPayment, (bool) isPaid, (bool) isIntl]
            'mark payment intl'      => ['Accepted', 0, [1, 0, 1], 'Not Started', 0, 1],
            'mark payment paid'      => ['Accepted', 0, [1, 1, 0], 'Not Started', 0, 1],
            'mark intl payment paid' => ['Accepted', 0, [1, 1, 1], 'Not Started', 0, 1],

            'update pending'  => ['Pending', 0, null, 'Not Started', 0, 0],
            'update declined' => ['Declined', 0, null, 'Not Started', 0, 0],
            'update complete' => ['Complete', 0, null, 'Not Started', 0, 0],
        ];
    }
}
