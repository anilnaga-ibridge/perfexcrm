<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public ?User $creator;

    /**
     * Create a new event instance.
     *
     * @param User $user The newly created staff member
     * @param User|null $creator The admin user who created the staff member
     */
    public function __construct(User $user, ?User $creator = null)
    {
        $this->user = $user;
        $this->creator = $creator;
    }
}
