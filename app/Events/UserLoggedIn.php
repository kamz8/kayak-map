<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The authenticated user
     *
     * @var User
     */
    public User $user;

    /**
     * The IP address of the user
     *
     * @var string
     */
    public string $ip;

    /**
     * The authentication guard
     *
     * @var string
     */
    public string $guard;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $ip
     * @param string $guard
     */
    public function __construct(User $user, string $ip, string $guard = 'web')
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->guard = $guard;
    }
}
