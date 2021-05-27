<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Commission\Commissioner;

use DB;

class AddDummyCommissioner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-dummy-commissioner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a dummy commissioner.';

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
     * @return mixed
     */
    public function handle()
    {
        if(!Commissioner::where('email', 'client@itinerare.net')->first()) {
            Commissioner::create([
                'name' => 'A Client',
                'email' => 'client@itinerare.net',
                'contact' => 'Varies',
                'paypal' => 'client@itinerare.net'
            ]);
            $this->line('Dummy commissioner data created!');
        }
        else $this->line('Dummy commissioner data already exists!');

    }
}
