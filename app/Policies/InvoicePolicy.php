<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use App\Permissions\PermissionRegistry;

class InvoicePolicy
{
    public function view(User $user, ?Invoice $invoice = null): bool
    {
        if ($user->hasPermission(PermissionRegistry::INVOICES_VIEW)) {
            return true;
        }

        if ($invoice && $user->hasPermission('Invoices.view_own')) {
            return $invoice->sale_agent == $user->id || $invoice->addedfrom == $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionRegistry::INVOICES_CREATE);
    }

    public function update(User $user, ?Invoice $invoice = null): bool
    {
        if ($user->hasPermission(PermissionRegistry::INVOICES_EDIT)) {
            return true;
        }

        if ($invoice && $user->hasPermission('Invoices.edit_own')) {
            return $invoice->sale_agent == $user->id || $invoice->addedfrom == $user->id;
        }

        return false;
    }

    public function delete(User $user, ?Invoice $invoice = null): bool
    {
        return $user->hasPermission(PermissionRegistry::INVOICES_DELETE);
    }
}
