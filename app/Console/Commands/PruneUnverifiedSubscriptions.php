<?php

namespace App\Console\Commands;

use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Console\Command;

class PruneUnverifiedSubscriptions extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune-unverified-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes unverified mailing list subscriptions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if (!MailingListSubscriber::where('is_verified', 0)->delete()) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
