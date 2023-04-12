<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAldebaran extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-aldebaran';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs general update commands.';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        $this->info('********************');
        $this->info('* UPDATE ALDEBARAN *');
        $this->info('********************'."\n");

        // Check if the user has run composer and run migrations
        $this->info('This command should be run after installing packages using composer.');

        if ($this->confirm('Have you run the composer install command or equivalent?')) {
            // Run migrations
            $this->line("\n".'Clearing caches...');
            $this->call('config:cache');

            // Run migrations
            $this->line("\n".'Running migrations...');
            $this->call('migrate');

            // Run setup commands
            $this->line("\n".'Updating site pages and settings...');
            $this->call('add-site-settings');
            $this->call('add-text-pages');

            $oldVersion = $this->ask('What version are you updating from? Please enter only the major and minor version number, e.g. 3.9 for v3.9.0. If you do not know, or wish to run all updates, please enter 0.');
            if ($oldVersion < 3.7) {
                $this->line('Updating from version '.$oldVersion.'...');
            } elseif ($oldVersion == 0) {
                $this->line('Running all updates...');
            } else {
                $this->line('No further updates to run!');

                return Command::SUCCESS;
            }

            if ($oldVersion < 3.4) {
                // Update images
                $this->line("\n".'Updating images...');
                $this->call('update-images');
            }

            if ($oldVersion < 3.7) {
                // Store commission payment fees
                $this->line("\n".'Updating commission payments...');
                $this->call('store-payment-fee-totals');
            }

            $this->line('Updates complete!');

            return Command::SUCCESS;
        } else {
            $this->line('Aborting! Please run composer install and then run this command again.');

            return Command::FAILURE;
        }
    }
}
