<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:remove-admin {email : The email of the user}';

    /**
     * The console command description.
     */
    protected $description = 'Remove administrator privileges from a user';

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
        
        if (!$user->is_admin) {
            $this->info("User '{$email}' is not an administrator.");
            return 0;
        }
        
        // Check if this is the last admin
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount <= 1) {
            $this->error("Cannot remove the last administrator. Create another admin first.");
            return 1;
        }
        
        $user->update(['is_admin' => false]);
        
        $this->info("Administrator privileges removed from '{$email}'.");
        
        return 0;
    }
} 