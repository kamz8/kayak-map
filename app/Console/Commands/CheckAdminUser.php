<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check admin users in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminUsers = User::where('is_admin', true)->get();
        
        $this->info('Admin users in system:');
        
        if ($adminUsers->count() > 0) {
            foreach ($adminUsers as $user) {
                $this->line($user->email . ' - ' . $user->first_name . ' ' . $user->last_name);
            }
        } else {
            $this->error('No admin users found!');
        }
        
        $this->info("\nTotal users: " . User::count());
        $this->info("Admin users: " . $adminUsers->count());
    }
}
