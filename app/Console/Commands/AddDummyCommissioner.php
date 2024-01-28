<?php

namespace App\Console\Commands;

use App\Models\Commission\Commissioner;
use Illuminate\Console\Command;

class AddDummyCommissioner extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-dummy-commissioner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a dummy commissioner.';

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
        $url = parse_url(env('APP_URL', 'https://itinerare.net'));

        if (!Commissioner::where('email', 'client@'.$url['host'])->first()) {
            Commissioner::create([
                'name'          => 'A Client',
                'email'         => 'client@'.$url['host'],
                'contact'       => 'Varies',
                'payment_email' => 'client@'.$url['host'],
            ]);
            $this->line('Dummy commissioner data created!');
        } else {
            $this->line('Dummy commissioner data already exists!');
        }
    }
}
