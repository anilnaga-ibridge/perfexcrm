<?php

namespace App\Listeners;

use App\Events\StaffCreated;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Notifications\StaffWelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleStaffCreated
{

    /**
     * Handle the event.
     *
     * @param StaffCreated $event
     * @return void
     */
    public function handle(StaffCreated $event): void
    {
        $user = $event->user;
        $creator = $event->creator;

        if (!$user) {
            return;
        }

        // 1. Send Queued Welcome Email Notification
        try {
            $user->notify(new StaffWelcomeNotification());
        } catch (Throwable $e) {
            Log::error("Failed to send welcome email notification to staff #{$user->id} ({$user->email}): " . $e->getMessage());
        }

        // 2. Create In-App Bell Notification for the Staff Member
        try {
            Notification::create([
                'touserid'    => $user->id,
                'description' => 'Welcome to the organization! Please set up your password and complete your profile.',
                'link'        => '/setup/staff',
                'isread'      => false,
                'date'        => now(),
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to create bell notification for staff #{$user->id}: " . $e->getMessage());
        }

        // 3. Write Structured Audit Activity Log
        try {
            $creatorName = $creator ? $creator->name : 'System Administrator';
            $creatorId   = $creator ? $creator->id : null;
            
            $roleName = 'Employee';
            if ($user->relationLoaded('role') && $user->role) {
                $roleName = $user->role->name ?? ucfirst($user->role->slug ?? 'employee');
            } elseif ($user->role_id) {
                $role = \App\Models\Role::find($user->role_id);
                if ($role) {
                    $roleName = $role->name ?? ucfirst($role->slug);
                }
            }

            ActivityLog::log(
                "Created new staff member: {$user->name} ({$user->email}) assigned role '{$roleName}' by {$creatorName}",
                $creatorId
            );
        } catch (Throwable $e) {
            Log::error("Failed to log activity for staff creation #{$user->id}: " . $e->getMessage());
        }
    }
}
