<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AddTextPages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-text-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds text page skeletons based on the associated config file.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        $pages = config('aldebaran.text_pages');

        $this->info('******************');
        $this->info('* ADD TEXT PAGES *');
        $this->info('******************'."\n");

        $this->line("Adding text pages...existing entries will be skipped.\n");

        // Add text pages from config
        foreach ($pages as $key => $page) {
            $this->addTextPage($key, $page);
        }
    }

    /**
     * Adds a text page.
     *
     * @param string $key
     * @param array  $page
     */
    private function addTextPage($key, $page) {
        if (!DB::table('text_pages')->where('key', $key)->exists()) {
            DB::table('text_pages')->insert([
                [
                    'key'        => $key,
                    'name'       => $page['name'],
                    'text'       => $page['text'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],

            ]);
            $this->info('Added:   '.$page['name']);
        } else {
            $this->line('Skipped: '.$page['name']);
        }
    }
}
