<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user login with username';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = '1000000000001';
        $password = 'password';

        $this->info("Testing login for user: $username");

        $user = \App\Models\User::where('username', $username)->first();
        if (!$user) {
            $this->error("User not found!");
            return;
        }

        $user->password_hash = \Illuminate\Support\Facades\Hash::make($password);
        $user->save();
        $this->info("Password updated to '$password'");

        if (\Illuminate\Support\Facades\Auth::attempt(['username' => $username, 'password' => $password])) {
            $this->info("Login Success!");
        } else {
            $this->error("Login Failed!");
        }
    }
}
