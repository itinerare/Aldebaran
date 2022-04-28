<?php

namespace Tests\Feature;

use App\Models\Commission\Commission;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionPayment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminQueueTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSION QUEUES
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test admin index access with commission queues.
     *
     * @dataProvider adminIndexProvider
     *
     * @param bool $withCommission
     */
    public function testGetIndexWithQueues($withCommission)
    {
        config(['aldebaran.settings.commissions.enabled' => 1]);

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

    public function adminIndexProvider()
    {
        return [
            'basic'           => [0],
            'with commission' => [1],
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
    public function testGetLedger($commsEnabled, $withCommission, $pendingCommission, $cancelledCommission, $status)
    {
        config(['aldebaran.settings.commissions.enabled' => $commsEnabled]);

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

    public function ledgerProvider()
    {
        return [
            'basic'                              => [1, 0, 0, 0, 200],
            'with commission'                    => [1, 1, 0, 0, 200],
            'commissions disabled'               => [0, 0, 0, 0, 404],
            'with pending commission'            => [1, 0, 1, 0, 200],
            'with cancelled but paid commission' => [1, 0, 0, 1, 200],
        ];
    }
}
