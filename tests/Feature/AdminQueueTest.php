<?php

namespace Tests\Feature;

use App\Models\Commission\Commission;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminQueueTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSION QUEUES
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test admin index access with commission queues.
     *
     * @dataProvider adminIndexProvider
     *
     * @param bool $withCommission
     */
    public function testGetIndexWithQueues($withCommission) {
        config(['aldebaran.commissions.enabled' => 1]);

        if ($withCommission) {
            // Create a commission
            // This will automatically create the underlying objects
            $commission = Commission::factory()->create();
        } else {
            // Create a commission class without additional objects
            $commission = CommissionClass::factory()->create();
        }

        $response = $this->actingAs($this->user)
            ->get('admin')
            ->assertStatus(200);

        $response->assertViewHas('commissionClasses', function ($commissionClasses) use ($withCommission, $commission) {
            return $commissionClasses->contains($withCommission ? $commission->type->category->class : $commission);
        });

        if ($withCommission) {
            $response->assertViewHas('pendingCount', function ($pendingCount) use ($commission) {
                return $pendingCount[$commission->type->category->class->id] == 1;
            });
        }
    }

    public function adminIndexProvider() {
        return [
            'basic'           => [0],
            'with commission' => [1],
        ];
    }

    /**
     * Test commission queue access.
     *
     * @dataProvider queueProvider
     *
     * @param bool        $commsEnabled
     * @param array       $commData
     * @param array|null  $search
     * @param int         $status
     * @param string|null $queue
     */
    public function testGetQueue($commsEnabled, $commData, $search, $status, $queue = 'Pending') {
        config(['aldebaran.commissions.enabled' => $commsEnabled]);

        if ($commData[0]) {
            // Create a commission
            // This will automatically create the underlying objects
            $commission = Commission::factory()->status($commData[1] ? $commData[1] : 'Pending')->create();
        } else {
            // Create a commission class without additional objects
            $commission = CommissionClass::factory()->create();
        }

        if ($search && $search[0] && $commData[0]) {
            if ($search[1]) {
                $type = $commission->type;
            } else {
                $type = CommissionType::factory()->create();
            }
        }

        $url = 'admin/commissions/'.($commData[0] ? $commission->type->category->class->slug : $commission->slug).($queue != 'Pending' ? '/'.Str::lower($queue) : '').(isset($type) ? '?commission_type='.$type->id : '');

        $response = $this->actingAs($this->user)
            ->get($url)
            ->assertStatus($status);

        if ($commData[0] && $status == 200) {
            // If there is a commission and the queue should be visible,
            // test that the commission is/isn't present dependent on queue
            // being viewed and commission status
            $response->assertViewHas('commissions', function ($commissions) use ($commData, $search, $queue, $commission) {
                if (((!$commData[1] && $queue == 'Pending') || $commData[1] == $queue) && (!$search || ($search[1]))) {
                    return $commissions->contains($commission);
                } else {
                    return !$commissions->contains($commission);
                }
            });
        }
    }

    public function queueProvider() {
        return [
            'basic'                       => [1, [0, null], null, 200],
            'search (successful)'         => [1, [0, null], [1, 1], 200],
            'search (unsuccessful)'       => [1, [0, null], [1, 0], 200],
            'commissions disabled'        => [0, [0, null], null, 404],
            'pending with pending comm'   => [1, [1, null], null, 200],
            'pending with accepted comm'  => [1, [1, 'Accepted'], null, 200],
            'pending with complete comm'  => [1, [1, 'Complete'], null, 200],
            'pending with declined comm'  => [1, [1, 'Declined'], null, 200],
            'accepted with accepted comm' => [1, [1, 'Accepted'], null, 200, 'Accepted'],
            'accepted with pending comm'  => [1, [1, 'Pending'], null, 200, 'Accepted'],
            'accepted with complete comm' => [1, [1, 'Complete'], null, 200, 'Accepted'],
            'accepted with declined comm' => [1, [1, 'Declined'], null, 200, 'Accepted'],
            'complete with complete comm' => [1, [1, 'Complete'], null, 200, 'Complete'],
            'complete with pending comm'  => [1, [1, 'Pending'], null, 200, 'Complete'],
            'complete with accepted comm' => [1, [1, 'Accepted'], null, 200, 'Complete'],
            'complete with declined comm' => [1, [1, 'Declined'], null, 200, 'Complete'],
            'declined with declined comm' => [1, [1, 'Declined'], null, 200, 'Declined'],
            'declined with pending comm'  => [1, [1, 'Pending'], null, 200, 'Declined'],
            'declined with accepted comm' => [1, [1, 'Accepted'], null, 200, 'Declined'],
            'declined with complete comm' => [1, [1, 'Complete'], null, 200, 'Declined'],
        ];
    }

    /**
     * Test ledger access.
     *
     * @dataProvider ledgerProvider
     *
     * @param bool $commsEnabled
     * @param bool $withCommission
     * @param bool $pendingCommission
     * @param bool $cancelledCommission
     * @param int  $status
     */
    public function testGetLedger($commsEnabled, $withCommission, $pendingCommission, $cancelledCommission, $status) {
        config(['aldebaran.commissions.enabled' => $commsEnabled]);

        if ($withCommission) {
            // Create a commission with some payments
            $commission = Commission::factory()
                ->has(CommissionPayment::factory()->count(2), 'payments')
                ->status('Accepted')->create();
        }

        if ($pendingCommission) {
            // Create a commission, with payments, that will not appear in the ledger
            $pendingCommission = Commission::factory()->has(CommissionPayment::factory()->count(2), 'payments')->create();
        }

        if ($cancelledCommission) {
            // Create a cancelled commission for which a payment will appear in the ledger due to being paid
            $cancelledCommission = Commission::factory()->has(CommissionPayment::factory()->paid()->count(1), 'payments')->status('Cancelled')->create();
        }

        $response = $this->actingAs($this->user)
            ->get('admin/ledger')
            ->assertStatus($status);

        if ($status == 200) {
            if ($withCommission) {
                // Test that the commission is present
                $response->assertViewHas('yearCommissions', function ($yearCommissions) use ($commission) {
                    return $yearCommissions[Carbon::today()->format('Y')]->contains($commission);
                });

                // Test that the payments are present
                foreach ($commission->payments as $payment) {
                    $response->assertViewHas('yearPayments', function ($yearPayments) use ($payment) {
                        return $yearPayments[Carbon::today()->format('Y')]->contains($payment);
                    });
                }
            }

            if ($pendingCommission) {
                // Test that the pending commission is not present
                $response->assertViewHas('yearCommissions', function ($yearCommissions) use ($pendingCommission) {
                    return !$yearCommissions->count() || !$yearCommissions[Carbon::today()->format('Y')]->contains($pendingCommission);
                });

                // Test that the payments are not present
                foreach ($pendingCommission->payments as $payment) {
                    $response->assertViewHas('yearPayments', function ($yearPayments) use ($payment) {
                        return !$yearPayments->count() || !$yearPayments[Carbon::today()->format('Y')]->contains($payment);
                    });
                }
            }

            if ($cancelledCommission) {
                // Test that the hidden commission is absent
                $response->assertViewHas('yearCommissions', function ($yearCommissions) use ($cancelledCommission) {
                    return !$yearCommissions->count() || !$yearCommissions[Carbon::today()->format('Y')]->contains($cancelledCommission);
                });

                // Test that the payment is present
                foreach ($cancelledCommission->payments as $payment) {
                    $response->assertViewHas('yearPayments', function ($yearPayments) use ($payment) {
                        return $yearPayments[Carbon::today()->format('Y')]->contains($payment);
                    });
                }
            }
        }
    }

    public function ledgerProvider() {
        return [
            'basic'                              => [1, 0, 0, 0, 200],
            'with commission'                    => [1, 1, 0, 0, 200],
            'with pending commission'            => [1, 0, 1, 0, 200],
            'with cancelled but paid commission' => [1, 0, 0, 1, 200],
            'all commissions'                    => [1, 1, 1, 1, 200],
            'commissions disabled'               => [0, 0, 0, 0, 404],
        ];
    }

    /**
     * Test PayPal fee calculation.
     *
     * @dataProvider feeCalcProvider
     *
     * @param bool  $isIntl
     * @param mixed $expected
     */
    public function testFeeCalculation($isIntl, $expected) {
        // Create a commission with payment to calculate for, with a known cost
        // and tip
        $commission = Commission::factory()->has(CommissionPayment::factory()->count(1)->state(function (array $attributes) use ($isIntl) {
            return [
                'cost'    => 100.00,
                'tip'     => 5.00,
                'is_intl' => $isIntl,
            ];
        }), 'payments')->create();
        $payment = $commission->payments->first();

        $this->assertTrue($expected == $commission->paymentWithFees($payment));
    }

    public function feeCalcProvider() {
        return [
            'non intl' => [0, 100.85],
            'intl'     => [1, 99.27],
        ];
    }
}
