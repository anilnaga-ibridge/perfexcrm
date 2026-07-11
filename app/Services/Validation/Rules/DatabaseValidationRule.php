<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;
use Illuminate\Support\Str;

class DatabaseValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Database';
    }

    public function weight(): int
    {
        return 5;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $migrationPaths = [
            'Database/Migrations',
            'database/migrations',
        ];

        $migrationDir = null;
        foreach ($migrationPaths as $path) {
            if ($context->hasDirectory($path)) {
                $migrationDir = $context->getPath() . '/' . $path;
                break;
            }
        }

        if ($migrationDir === null) {
            $result->addInfo("No database migrations folder found.");
            return $result;
        }

        $files = glob($migrationDir . '/*.php');
        if (empty($files)) {
            $result->addInfo("No migration files found.");
            return $result;
        }

        $alias = $context->getAlias();
        $prefix = str_replace('-', '_', $alias) . '_';
        $coreTables = ['users', 'roles', 'permissions', 'companies', 'modules', 'module_menus', 'module_settings', 'personal_access_tokens', 'migrations'];

        foreach ($files as $filePath) {
            $fileName = basename($filePath);

            // 1. Verify timestamp format: YYYY_MM_DD_HHMMSS_name.php
            if (!preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.+\.php$/', $fileName)) {
                $result->addWarning("Migration file '{$fileName}' does not match the standard Laravel timestamp format (YYYY_MM_DD_HHMMSS_name.php).");
            }

            $content = file_get_contents($filePath);

            // 2. Scan Schema::create
            if (preg_match_all('/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $createMatches)) {
                foreach ($createMatches[1] as $table) {
                    $cleanPrefix = rtrim($prefix, '_');
                    $singularTable = Str::singular($table);

                    // Check if prefixed with module alias prefix (accounting for pluralization)
                    if (!str_starts_with($table, $prefix) && 
                        !str_starts_with($singularTable, $prefix) &&
                        !str_starts_with($table, $cleanPrefix) &&
                        !str_starts_with($singularTable, $cleanPrefix)) {
                        $result->addWarning("Migration '{$fileName}' creates table '{$table}' which is not prefixed with the module prefix '{$prefix}'. This could cause database table name collisions.");
                    }

                    // Check if trying to recreate core tables
                    if (in_array($table, $coreTables, true)) {
                        $result->addError("Migration '{$fileName}' attempts to create core table '{$table}'. Modules must not create or replace system tables.");
                    }
                }
            }

            // 3. Scan Schema::table for core table modifications
            if (preg_match_all('/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $tableMatches)) {
                foreach ($tableMatches[1] as $table) {
                    if (in_array($table, $coreTables, true)) {
                        $result->addWarning("Migration '{$fileName}' alters core table '{$table}'. Modules should avoid modifying core database tables directly.");
                    }
                }
            }
        }

        if ($result->passed()) {
            $result->addInfo("Validated " . count($files) . " database migration(s).");
        }

        return $result;
    }
}
