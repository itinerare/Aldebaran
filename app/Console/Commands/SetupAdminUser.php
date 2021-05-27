<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\UserService;
use App\Models\User;

class SetupAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the admin user account if no users exist, or resets the password if it does.';

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
        $this->info('********************');
        $this->info('* ADMIN USER SETUP *');
        $this->info('********************'."\n");

        // Check if the admin user exists...
        $user = User::first();
        if(!$user) {
            $this->line('Setting up admin account. This account will have access to all site data.');
            $name = $this->anticipate('Username', ['Admin', 'System']);
            $email = $this->ask('Email Address');

            $this->line("\nUsername: ".$name);
            $this->line("Email: ".$email);
            $confirm = $this->confirm("Proceed to create account with this information?");

            if($confirm) {
                $password = str_random(20);

                $service = new UserService;
                $service->createUser([
                    'name' => $name,
                    'email' => $email,
                    'rank_id' => $adminRank->id,
                    'password' => $password
                ]);

                $this->line('Admin account created. You can now log in with the registered email and the following password:');
                $this->line($password);
                $this->line('If necessary, you can run this command again to change the email address and password of the admin account.');
                return;
            }
        }
        else {
            // Change the admin email/password.
            $this->line('Admin account [' . $user->name . '] already exists.');
            if($this->confirm("Reset email address and password for this account?")) {
                $email = $this->ask('Email Address');
                $password = str_random(20);

                $this->line("\nEmail: ".$email);
                $this->line("\nPassword: ".$password);

                if($this->confirm("Proceed to change email address and password?")) {
                    $service = new UserService;
                    $service->updateUser([
                        'id' => $user->id,
                        'email' => $email,
                        'password' => $password
                    ]);

                    $this->line('Admin account email and password changed.');
                    return;
                }
            }
        }
        $this->line('Action cancelled.');

    }
}
