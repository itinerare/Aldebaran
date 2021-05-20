<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Config;

class AddSiteSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-site-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds site settings.';

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
     * Add a site setting.
     *
     * Example usage:
     * $this->addSiteSetting("site_setting_key", 1, "0: does nothing. 1: does something.");
     *
     * @param  string  $key
     * @param  int     $value
     * @param  string  $description
     */
    private function addSiteSetting($key, $value, $description) {
        if(!DB::table('site_settings')->where('key', $key)->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $key,
                    'value'       => $value,
                    'description' => $description,
                ],
            ]);
            $this->info( "Added:   ".$key." / Default: ".$value);
        }
        else $this->line("Skipped: ".$key);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*********************');
        $this->info('* ADD SITE SETTINGS *');
        $this->info('*********************'."\n");

        $this->line("Adding site settings...existing entries will be skipped.\n");

        $this->addSiteSetting('site_name', 'itinerare', 'Display name used around the site.');

        foreach(Config::get('itinerare.comm_types') as $type=>$values) {
            $this->addSiteSetting($type.'_comms_open', 0, '0: '.ucfirst($type).' commissions closed, 1: '.ucfirst($type).' commissions open.');
            $this->addSiteSetting('overall_'.$type.'_slots', 0, 'Overall number of availabile commission slots. Set to 0 to disable limits.');
        }

        $this->line("\nSite settings up to date!");

    }
}
