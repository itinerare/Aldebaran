<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Config;
use DB;
use Carbon\Carbon;

class AddTextPages extends Command
{
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
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a text page.
     *
     * @param  string    $key
     * @param  array     $page
     */
    private function addTextPage($key, $page) {
        if(!DB::table('text_pages')->where('key', $key)->exists()) {
            DB::table('text_pages')->insert([
                [
                    'key' => $key,
                    'name' => $page['name'],
                    'text' => $page['text'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]

            ]);
            $this->info("Added:   ".$page['name']);
        }
        else $this->line("Skipped: ".$page['name']);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $pages = Config::get('itinerare.text_pages');


        $this->info('******************');
        $this->info('* ADD TEXT PAGES *');
        $this->info('******************'."\n");

        $this->line("Adding text pages...existing entries will be skipped.\n");

        // Add text pages from config
        foreach($pages as $key => $page)
            $this->addTextPage($key, $page);

        $this->line("Adding commission text pages...existing entries will be skipped.\n");

        // Add text pages for each commission type
        foreach(Config::get('itinerare.comm_types') as $type=>$values) {
            // Add ToS and info pages
            $this->addTextPage($type.'tos', [
                'name' => ucfirst($type).' Commission Terms of Service',
                'text' => '<p>'.ucfirst($type).' commssion terms of service go here.</p>',
            ]);
            $this->addTextPage($type.'info', [
                'name' => ucfirst($type).' Commission Info',
                'text' => '<p>'.ucfirst($type).' commssion info goes here.</p>',
            ]);

            // Add any custom pages for the type
            if(isset($values['pages']))
                foreach($values['pages'] as $key=>$page)
                    $this->addTextPage($key, $page);
        }
    }
}
