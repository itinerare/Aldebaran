<?php

namespace App\Console\Commands;

use App\Models\Commission\Commission;
use App\Models\Commission\CommissionPayment;
use Illuminate\Console\Command;

class MigrateCostData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate-cost-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates commission cost data to the new format.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->withProgressBar(Commission::all(), function ($commission) {
            // Fetch existing data and create payment object(s)
            if (!$commission->payments->count()) {
                foreach ($commission->costData as $data) {
                    $payment = CommissionPayment::create([
                    'commission_id' => $commission->id,
                    'cost'          => $data['cost'],
                    'tip'           => $data['tip'] ? $data['tip'] : 0.00,
                    'is_paid'       => $data['paid'],
                    'is_intl'       => $data['intl'],
                    'paid_at'       => $data['paid'] ? $commission->updated_at : null,
                ]);

                    if (!$payment) {
                        $this->error('Failed to create payment record.');
                    }
                }
            }
        });
    }
}
