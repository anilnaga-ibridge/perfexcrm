<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LegacyViewRenderer
{
    /**
     * List of PHP keywords and control structures that should never be stubbed.
     */
    protected const PHP_KEYWORDS = [
        'if', 'array', 'empty', 'isset', 'list', 'include', 'require',
        'include_once', 'require_once', 'eval', 'exit', 'die', 'echo',
        'print', 'unset', 'foreach', 'while', 'for', 'switch', 'catch',
        'declare', 'return', 'throw', 'use', 'function', 'class', 'as',
        'clone', 'new', 'or', 'and', 'xor', 'match', 'try', 'finally'
    ];

    /**
     * Render a legacy CodeIgniter view file, compiling missing functions to safe stub calls.
     */
    public function render(string $viewFilePath, array $variables = []): string
    {
        $cacheDir = base_path('storage/framework/views/plugins');
        if (!File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true, true);
        }

        // Generate hash-based compiled filename using filepath and modification time
        $mtime = File::exists($viewFilePath) ? File::lastModified($viewFilePath) : time();
        $hash = md5($viewFilePath . '_' . $mtime);
        $compiledPath = $cacheDir . '/' . basename($viewFilePath, '.php') . '_' . $hash . '.php';

        // Compile only if cache does not exist
        if (!File::exists($compiledPath)) {
            $startTime = microtime(true);
            $this->compile($viewFilePath, $compiledPath);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("LEGACY COMPAT: Compiled legacy view [{$viewFilePath}] in {$duration}ms");
        }

        // Auto-inject standard CodeIgniter / iBridge CRM global view variables if missing
        if (!isset($variables['currencies'])) {
            try {
                $variables['currencies'] = \Illuminate\Support\Facades\DB::table('currencies')->get()->toArray();
            } catch (\Throwable $ex) {
                $variables['currencies'] = [
                    (object)['id' => 1, 'symbol' => '$', 'name' => 'USD', 'is_default' => 1, 'thousandseparator' => ',', 'decimalseparator' => '.']
                ];
            }
        }
        if (!isset($variables['base_currency'])) {
            $variables['base_currency'] = function_exists('get_base_currency') ? get_base_currency() : (object)['id' => 1, 'symbol' => '$', 'name' => 'USD'];
        }

        // Render the compiled view file with variables extracted
        ob_start();
        try {
            extract($variables, EXTR_SKIP);
            include $compiledPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            Log::error("LEGACY COMPAT: Error rendering compiled view [{$viewFilePath}]: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return "<div style='padding:16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-family:sans-serif;'>"
                 . "<strong style='color:#dc2626;'>Error rendering legacy view:</strong> "
                 . htmlspecialchars($e->getMessage())
                 . "</div>";
        }

        return ob_get_clean();
    }

    /**
     * Compile a legacy view by rewriting undefined function calls.
     */
    protected function compile(string $sourcePath, string $destinationPath): void
    {
        if (!File::exists($sourcePath)) {
            File::put($destinationPath, '<?php // Missing view file ?>');
            return;
        }

        $content = File::get($sourcePath);

        // Find all function calls in the view file (e.g. some_func() )
        if (preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches)) {
            $foundFunctions = array_unique($matches[1]);
            $rewrites = [];

            foreach ($foundFunctions as $func) {
                $lowerFunc = strtolower($func);

                // Skip if it is a built-in PHP keyword/structure
                if (in_array($lowerFunc, self::PHP_KEYWORDS, true)) {
                    continue;
                }

                // Skip if the function is already defined in PHP
                if (function_exists($func)) {
                    continue;
                }

                $rewrites[] = $func;
            }

            // Perform boundary-safe regex replacements for each undefined function call
            foreach ($rewrites as $func) {
                $quoted = preg_quote($func, '/');
                $boundaryGuard = '(?<!function\s)(?<!function\s\s)(?<!->)(?<!::)(?<!\$)(?<!\\\\)\b';

                // 1. Replace empty argument calls: func() -> \App\Services\MissingFunctionRegistry::call('func')
                $emptyPattern = '/' . $boundaryGuard . $quoted . '\s*\(\s*\)/i';
                $emptyReplacement = "\\App\\Services\\MissingFunctionRegistry::call('{$func}')";
                $content = preg_replace($emptyPattern, $emptyReplacement, $content);

                // 2. Replace parameterized calls: func(arg) -> \App\Services\MissingFunctionRegistry::call('func', arg
                $paramPattern = '/' . $boundaryGuard . $quoted . '\s*\(/i';
                $paramReplacement = "\\App\\Services\\MissingFunctionRegistry::call('{$func}', ";
                $content = preg_replace($paramPattern, $paramReplacement, $content);
            }
        }

        File::put($destinationPath, $content);
    }
}
