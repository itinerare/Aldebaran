<?php

namespace App\Console\Commands;

use App\Models\Commission\CommissionPayment;
use Illuminate\Console\Command;

class StorePaymentFeeTotals extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store-payment-fee-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates total with fees for each extant commission payment and stores it.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if ($this->confirm('Do you want to calculate and store total with fees for commission payments now? This will only impact payments which are already paid and for which this information is not already stored.')) {
            $payments = CommissionPayment::where('is_paid', 1)->whereNull('total_with_fees');
            if ($payments->count()) {
                $this->line('Updating commission payments...');
                $bar = $this->output->createProgressBar($payments->count());
                $bar->start();

                $payments = $payments->get();
                foreach ($payments as $payment) {
                    $payment->update([
                        'total_with_fees' => $payment->totalWithFees,
                    ]);
                    $bar->advance();
                }
                $bar->finish();
                $this->line("\n".'All payments updated!');
            } else {
                $this->line('No payments to update!');
            }
        } else {
            $this->line('Skipped updating commission payments.');
        }

        return Command::SUCCESS;
    }
}
