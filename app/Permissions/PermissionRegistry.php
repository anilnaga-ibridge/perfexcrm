<?php

namespace App\Permissions;

class PermissionRegistry
{
    // Canonical Module Constants
    public const CUSTOMERS        = 'Customers';
    public const PROPOSALS        = 'Proposals';
    public const ESTIMATES        = 'Estimates';
    public const INVOICES         = 'Invoices';
    public const PAYMENTS         = 'Payments';
    public const CREDIT_NOTES     = 'Credit Notes';
    public const ITEMS            = 'Items';
    public const SUBSCRIPTIONS    = 'Subscriptions';
    public const EXPENSES         = 'Expenses';
    public const CONTRACTS        = 'Contracts';
    public const PROJECTS         = 'Projects';
    public const TASKS            = 'Tasks';
    public const SUPPORT          = 'Support';
    public const KNOWLEDGE_BASE   = 'Knowledge Base';
    public const LEADS            = 'Leads';
    public const STAFF            = 'Staff';
    public const STAFF_ROLES      = 'Staff Roles';
    public const SETTINGS         = 'Settings';
    public const REPORTS          = 'Reports';
    public const UTILITIES        = 'Utilities';
    public const ESTIMATE_REQUEST = 'Estimate Request';
    public const SURVEYS          = 'Surveys';
    public const GOALS            = 'Goals';
    public const ANNOUNCEMENTS    = 'Announcements';

    // Canonical Permission Action Constants
    public const CUSTOMERS_VIEW   = 'Customers.view';
    public const CUSTOMERS_CREATE = 'Customers.create';
    public const CUSTOMERS_EDIT   = 'Customers.edit';
    public const CUSTOMERS_DELETE = 'Customers.delete';

    public const PROPOSALS_VIEW   = 'Proposals.view';
    public const PROPOSALS_CREATE = 'Proposals.create';
    public const PROPOSALS_EDIT   = 'Proposals.edit';
    public const PROPOSALS_DELETE = 'Proposals.delete';

    public const ESTIMATES_VIEW   = 'Estimates.view';
    public const ESTIMATES_CREATE = 'Estimates.create';
    public const ESTIMATES_EDIT   = 'Estimates.edit';
    public const ESTIMATES_DELETE = 'Estimates.delete';

    public const INVOICES_VIEW    = 'Invoices.view';
    public const INVOICES_CREATE  = 'Invoices.create';
    public const INVOICES_EDIT    = 'Invoices.edit';
    public const INVOICES_DELETE  = 'Invoices.delete';

    public const PAYMENTS_VIEW    = 'Payments.view';
    public const PAYMENTS_CREATE  = 'Payments.create';
    public const PAYMENTS_EDIT    = 'Payments.edit';
    public const PAYMENTS_DELETE  = 'Payments.delete';

    public const CREDIT_NOTES_VIEW   = 'Credit Notes.view';
    public const CREDIT_NOTES_CREATE = 'Credit Notes.create';
    public const CREDIT_NOTES_EDIT   = 'Credit Notes.edit';
    public const CREDIT_NOTES_DELETE = 'Credit Notes.delete';

    public const ITEMS_VIEW    = 'Items.view';
    public const ITEMS_CREATE  = 'Items.create';
    public const ITEMS_EDIT    = 'Items.edit';
    public const ITEMS_DELETE  = 'Items.delete';

    public const SUBSCRIPTIONS_VIEW   = 'Subscriptions.view';
    public const SUBSCRIPTIONS_CREATE = 'Subscriptions.create';
    public const SUBSCRIPTIONS_EDIT   = 'Subscriptions.edit';
    public const SUBSCRIPTIONS_DELETE = 'Subscriptions.delete';

    public const EXPENSES_VIEW   = 'Expenses.view';
    public const EXPENSES_CREATE = 'Expenses.create';
    public const EXPENSES_EDIT   = 'Expenses.edit';
    public const EXPENSES_DELETE = 'Expenses.delete';

    public const CONTRACTS_VIEW   = 'Contracts.view';
    public const CONTRACTS_CREATE = 'Contracts.create';
    public const CONTRACTS_EDIT   = 'Contracts.edit';
    public const CONTRACTS_DELETE = 'Contracts.delete';

    public const PROJECTS_VIEW   = 'Projects.view';
    public const PROJECTS_CREATE = 'Projects.create';
    public const PROJECTS_EDIT   = 'Projects.edit';
    public const PROJECTS_DELETE = 'Projects.delete';

    public const TASKS_VIEW   = 'Tasks.view';
    public const TASKS_CREATE = 'Tasks.create';
    public const TASKS_EDIT   = 'Tasks.edit';
    public const TASKS_DELETE = 'Tasks.delete';

    public const SUPPORT_VIEW   = 'Support.view';
    public const SUPPORT_CREATE = 'Support.create';
    public const SUPPORT_EDIT   = 'Support.edit';
    public const SUPPORT_DELETE = 'Support.delete';

    public const KNOWLEDGE_BASE_VIEW   = 'Knowledge Base.view';
    public const KNOWLEDGE_BASE_CREATE = 'Knowledge Base.create';
    public const KNOWLEDGE_BASE_EDIT   = 'Knowledge Base.edit';
    public const KNOWLEDGE_BASE_DELETE = 'Knowledge Base.delete';

    public const LEADS_VIEW   = 'Leads.view';
    public const LEADS_CREATE = 'Leads.create';
    public const LEADS_EDIT   = 'Leads.edit';
    public const LEADS_DELETE = 'Leads.delete';

    public const STAFF_VIEW   = 'Staff.view';
    public const STAFF_CREATE = 'Staff.create';
    public const STAFF_EDIT   = 'Staff.edit';
    public const STAFF_DELETE = 'Staff.delete';

    public const STAFF_ROLES_VIEW   = 'Staff Roles.view';
    public const STAFF_ROLES_CREATE = 'Staff Roles.create';
    public const STAFF_ROLES_EDIT   = 'Staff Roles.edit';
    public const STAFF_ROLES_DELETE = 'Staff Roles.delete';

    public const SETTINGS_VIEW = 'Settings.view';
    public const SETTINGS_EDIT = 'Settings.edit';

    /**
     * Map feature names to their canonical synonyms
     */
    public static function getAliases(string $feature): array
    {
        $n = strtolower(str_replace(['_', '-'], ' ', trim($feature)));
        
        if (in_array($n, ['customers', 'customer', 'clients', 'client'])) {
            return ['customers', 'customer', 'clients', 'client'];
        }
        if (in_array($n, ['staff roles', 'staff role', 'roles', 'role'])) {
            return ['staff roles', 'staff role', 'roles', 'role'];
        }
        if (in_array($n, ['staff', 'users', 'user'])) {
            return ['staff', 'users', 'user'];
        }
        if (in_array($n, ['knowledge base', 'knowledgebase', 'kb'])) {
            return ['knowledge base', 'knowledgebase', 'kb'];
        }
        if (in_array($n, ['credit notes', 'creditnotes', 'credit note'])) {
            return ['credit notes', 'creditnotes', 'credit note'];
        }
        if (in_array($n, ['estimate request', 'estimaterequest', 'estimate requests'])) {
            return ['estimate request', 'estimaterequest', 'estimate requests'];
        }
        if (in_array($n, ['items', 'item', 'predefined items'])) {
            return ['items', 'item', 'predefined items'];
        }
        if (in_array($n, ['task checklist templates', 'checklist templates', 'task checklist'])) {
            return ['task checklist templates', 'checklist templates', 'task checklist'];
        }
        if (in_array($n, ['e-invoice', 'einvoice', 'e invoice'])) {
            return ['e-invoice', 'einvoice', 'e invoice'];
        }

        $plural = $n . 's';
        $singular = rtrim($n, 's');
        return array_values(array_unique([$n, $plural, $singular]));
    }
}
