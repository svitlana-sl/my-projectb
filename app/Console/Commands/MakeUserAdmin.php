<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:make-admin {email : The email of the user}';

    /**
     * The console command description.
     */
    protected $description = 'Make a user an administrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        if ($user->is_admin) {
            $this->info("User '{$email}' is already an administrator.");
            return 0;
        }
        
        $user->update(['is_admin' => true]);
        
        $this->info("User '{$email}' has been made an administrator.");
        
        return 0;
    }
} 