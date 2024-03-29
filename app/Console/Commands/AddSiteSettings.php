<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddSiteSettings extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-site-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds site settings.';

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
        $this->info('*********************');
        $this->info('* ADD SITE SETTINGS *');
        $this->info('*********************'."\n");

        $this->line("Adding site settings...existing entries will be skipped.\n");

        $this->addSiteSetting('site_name', 'aldebaran', 'Display name used around the site.');
        $this->addSiteSetting('site_desc', 'Personal gallery site.', 'Description used for meta tag/link previews.');

        $this->addSiteSetting('notif_emails', 0, 'Whether or not you wish to receive a notification email when a commission/quote request is submitted or (if payment processor integrations are enabled) when an invoice is paid.');

        $this->addSiteSetting('display_mailing_lists', 0, 'Whether or not a list of open mailing lists should be displayed on the site\'s index page.');

        $this->addSiteSetting('comm_contact_info', 0, 'What methods you accept for contact information, e.g. email. Displayed as part of the new commission request form. If cleared/set to 0, nothing will be displayed.');

        $this->line("\nSite settings up to date!");
    }

    /**
     * Add a site setting.
     *
     * @param string $key
     * @param int    $value
     * @param string $description
     */
    private function addSiteSetting($key, $value, $description) {
        if (!DB::table('site_settings')->where('key', $key)->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $key,
                    'value'       => $value,
                    'description' => $description,
                ],
            ]);
            $this->info('Added:   '.$key.' / Default: '.$value);
        } else {
            $this->line('Skipped: '.$key);
        }
    }
}
