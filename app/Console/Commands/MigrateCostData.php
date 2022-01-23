<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Commission\Commission;

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
     *
     * @return void
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
        $commissions = $this->withProgressBar(Commission::all(), function ($commission) {
            $cost[$commission->id][0] = [
                'cost' => $commission->getRawOriginal('cost_data'),
                'tip' => isset($commission->data['tip']) ? $commission->data['tip'] : null,
                'paid' => $commission->getRawOriginal('paid_status'),
                'intl' => 0
            ];

            // Update the commission with the new data
            $commission->update([
                'cost_data' => json_encode($cost[$commission->id])
            ]);
        });
    }
}
