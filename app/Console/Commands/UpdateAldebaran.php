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

            // Update images
            $this->line("\n".'Updating images...');
            $this->call('update-images');
        } else {
            $this->line('Aborting! Please run composer install and then run this command again.');
        }
    }
}
