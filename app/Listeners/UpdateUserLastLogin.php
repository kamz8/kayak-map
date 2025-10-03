<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\DB;

class UpdateUserLastLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Updates user's last login timestamp and IP address.
     * Uses DB::table to avoid triggering model events and prevent recursion.
     *
     * @param UserLoggedIn $event
     * @return void
     */
    public function handle(UserLoggedIn $event): void
    {
        // Use DB::table to avoid model events and prevent recursion
        DB::table('users')
            ->where('id', $event->user->id)
            ->update([
                'last_login_at' => now(),
                'last_login_ip' => $event->ip,
            ]);
    }
}
