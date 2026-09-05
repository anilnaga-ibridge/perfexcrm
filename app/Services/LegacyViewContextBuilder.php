<?php

namespace App\Services;

class LegacyViewContextBuilder
{
    /**
     * Build clean context for legacy views based on real CodeIgniter runtime state.
     * ZERO MOCK DATA POLICY: Never inject fake records, fake scores, or hardcoded dummy models.
     */
    public function build(string $page, array $module): array
    {
        $ci = get_instance();

        // Use real variables set by the active controller
        $context = $ci->data ?? [];

        $context['module'] = $module;
        $context['pageTitle'] = $context['title'] ?? ($module['name'] ?? 'Module');
        $context['CI'] = $ci;

        return $context;
    }
}
