<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use App\Permissions\PermissionRegistry;

class CustomerPolicy
{
    public function view(User $user, ?Client $client = null): bool
    {
        if ($user->hasPermission(PermissionRegistry::CUSTOMERS_VIEW)) {
            return true;
        }

        if ($client && $user->hasPermission('Customers.view_own')) {
            return $client->addedfrom == $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionRegistry::CUSTOMERS_CREATE);
    }

    public function update(User $user, ?Client $client = null): bool
    {
        return $user->hasPermission(PermissionRegistry::CUSTOMERS_EDIT);
    }

    public function delete(User $user, ?Client $client = null): bool
    {
        return $user->hasPermission(PermissionRegistry::CUSTOMERS_DELETE);
    }
}
