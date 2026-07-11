<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class MenuValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Menus';
    }

    public function weight(): int
    {
        return 10;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        if (!$context->hasFile('menu.json')) {
            $result->addInfo("No 'menu.json' provided by this module.");
            return $result;
        }

        $menu = $context->getJsonFile('menu.json');
        if ($menu === null) {
            $result->addError("'menu.json' exists but contains invalid JSON.");
            return $result;
        }

        $menuList = [];
        if (isset($menu['title'])) {
            $menuList = [$menu];
        } elseif (is_array($menu)) {
            $menuList = $menu;
        } else {
            $result->addError("'menu.json' must be a JSON object or array.");
            return $result;
        }

        // Retrieve permissions to validate menu permission references
        $permissions = [];
        if ($context->hasFile('permissions.json')) {
            $permsFile = $context->getJsonFile('permissions.json');
            if (is_array($permsFile)) {
                foreach ($permsFile as $p) {
                    $key = $p['key'] ?? $p['name'] ?? null;
                    if ($key) {
                        $permissions[] = $key;
                    }
                }
            }
        }

        $routes = [];
        $titles = [];

        $validateItem = function ($item, $isChild = false) use (&$validateItem, &$routes, &$titles, $permissions, $result) {
            if (!is_array($item)) {
                $result->addError("Menu item must be a JSON object.");
                return;
            }

            if (empty($item['title'])) {
                $result->addError("Menu item is missing required field: 'title'.");
            } else {
                $title = trim($item['title']);
                if (in_array($title, $titles, true)) {
                    $result->addWarning("Duplicate menu title detected: '{$title}'.");
                }
                $titles[] = $title;
            }

            if (isset($item['permission']) && trim($item['permission']) !== '') {
                $perm = trim($item['permission']);
                if (!in_array($perm, $permissions, true)) {
                    $result->addWarning("Menu item '{$item['title']}' references permission '{$perm}' which is not declared in 'permissions.json'.");
                }
            }

            if (isset($item['route'])) {
                $route = trim($item['route']);
                if ($route !== '') {
                    if (in_array($route, $routes, true)) {
                        $result->addError("Duplicate menu route detected: '{$route}'.");
                    }
                    $routes[] = $route;

                    // Routes should be relative to the module and usually start with a slash
                    if ($route !== '#' && !str_starts_with($route, '/')) {
                        $result->addWarning("Menu route '{$route}' should preferably start with a slash '/'.");
                    }
                }
            }

            if (isset($item['children'])) {
                if (!is_array($item['children'])) {
                    $result->addError("Menu item '{$item['title']}' has 'children' which is not an array.");
                } else {
                    foreach ($item['children'] as $child) {
                        $validateItem($child, true);
                    }
                }
            }
        };

        foreach ($menuList as $item) {
            $validateItem($item);
        }

        if ($result->passed()) {
            $result->addInfo("Menu structure is compliant with " . count($routes) . " routes defined.");
        }

        return $result;
    }
}
