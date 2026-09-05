<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Permissions\PermissionRegistry;

class TaskPolicy
{
    public function view(User $user, ?Task $task = null): bool
    {
        if ($user->hasPermission(PermissionRegistry::TASKS_VIEW)) {
            return true;
        }

        if ($task && $user->hasPermission('Tasks.view_own')) {
            return $task->addedfrom == $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionRegistry::TASKS_CREATE);
    }

    public function update(User $user, ?Task $task = null): bool
    {
        if ($user->hasPermission(PermissionRegistry::TASKS_EDIT)) {
            return true;
        }

        if ($task && $user->hasPermission('Tasks.edit_assigned')) {
            return $task->addedfrom == $user->id;
        }

        return false;
    }

    public function delete(User $user, ?Task $task = null): bool
    {
        return $user->hasPermission(PermissionRegistry::TASKS_DELETE);
    }
}
