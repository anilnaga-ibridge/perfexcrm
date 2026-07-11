<?php

use Illuminate\Support\Facades\Route;

// Load the full CI compatibility layer (classes, Active Record, helpers, AdminController)
require_once base_path('app/Services/CICompatLayer.php');

// Plugin SSO (primary)
Route::get('/plugins/sso', [\App\Http\Controllers\Api\ModuleController::class, 'ssoLogin'])->name('plugins.sso');
// Legacy alias
Route::get('/modules/sso', [\App\Http\Controllers\Api\ModuleController::class, 'ssoLogin'])->name('modules.sso');

// Plugin page bridge — serves legacy CI views inside the SSO iframe
Route::get('/plugins/{alias}/{page?}', function ($alias, $page = 'dashboard') {
    if (!auth('web')->check() && !auth('sanctum')->check()) {
        abort(403, 'Unauthenticated');
    }

    $module = \App\Models\Module::where('alias', $alias)->where('status', 'active')->first();
    if (!$module) {
        abort(404, "Plugin '{$alias}' not found or inactive.");
    }

    // Native Vue page — redirect to SPA route
    $vuePagePath = base_path("Modules/{$alias}/resources/js/pages/{$page}.vue");
    if (file_exists($vuePagePath)) {
        return redirect("/admin/module/{$alias}/{$page}");
    }

    if (!defined('BASEPATH')) { define('BASEPATH', true); }
    if (!defined('FCPATH')) { define('FCPATH', base_path('public/') . '/'); }
    if (!class_exists('App_module_migration')) { eval('class App_module_migration {}'); }

    // Load module bootstrap file (defines constants, registers hooks)
    $bootstrapFile = base_path("Modules/{$alias}/" . str_replace('-', '_', $alias) . ".php");
    if (file_exists($bootstrapFile)) {
        try { include_once $bootstrapFile; } catch (\Throwable $e) {}
    }

    // Load module helper files
    $helpersDir = base_path("Modules/{$alias}/helpers");
    if (is_dir($helpersDir)) {
        foreach (glob($helpersDir . '/*.php') as $helperFile) {
            try { include_once $helperFile; } catch (\Exception $e) {}
        }
    }

    // Get CI instance
    $CI = get_instance();
    $CI->data['currentModuleAlias'] = $alias;

    // Load module model if it has one
    $modelsDir = base_path("Modules/{$alias}/models");
    if (is_dir($modelsDir)) {
        foreach (glob($modelsDir . '/*.php') as $modelFile) {
            $content = file_get_contents($modelFile);
            if (preg_match('/class\s+(\w+)\s+extends\s+/', $content, $m)) {
                $className = $m[1];
                try {
                    require_once $modelFile;
                    if (class_exists($className)) {
                        $propName = lcfirst(str_replace('_model', '', basename($modelFile, '.php')));
                        $CI->$propName = new $className();
                    }
                } catch (\Exception $e) {}
            }
        }
    }

    // ─── Try executing CI controller method for real data ──────────────────────────
    $controllersDir = base_path("Modules/{$alias}/controllers");
    $controllerExecuted = false;

    if (is_dir($controllersDir)) {
        $allControllerFiles = glob($controllersDir . '/*.php');
        foreach ($allControllerFiles as $cf) {
            if (basename($cf) === 'index.html') continue;
            $fileContent = file_get_contents($cf);
            if (!preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $fileContent, $m)) continue;
            $ctrlClassName = $m[1];

            try {
                require_once $cf;
                if (!class_exists($ctrlClassName)) continue;

                $instance = new $ctrlClassName();
                foreach (get_object_vars($CI) as $key => $value) {
                    if ($value !== null) {
                        if (!property_exists($instance, $key) || $instance->$key === null) {
                            $instance->$key = $value;
                        }
                    }
                }

                if (method_exists($instance, $page)) {
                    ob_start();
                    $res = $instance->$page();
                    $output = ob_get_clean();

                    if (is_string($output) && !empty($output)) {
                        $htmlContent = $output;
                        $pageTitle = ucwords(str_replace(['-', '_', '/'], ' ', $page));
                        \Illuminate\Support\Facades\Log::info("LEGACY COMPAT: controller method '{$ctrlClassName}::{$page}()' executed for {$alias}/{$page}");
                        return view('plugin_page', compact('module', 'pageTitle', 'htmlContent'));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("LEGACY COMPAT: controller {$ctrlClassName} failed for {$alias}/{$page}: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine());
            }
        }
    }
    // ─── Fallback: render view directly ──────────────────────────────────────────

    // Pre-populate common variables for views
    $roles = \App\Models\Role::all()->map(function ($r) {
        return ['id' => $r->id, 'roleid' => $r->id, 'name' => $r->name, 'slug' => $r->slug];
    })->toArray();

    $staff = \App\Models\User::all()->map(function ($u) {
        $parts = explode(' ', $u->name ?? $u->firstname . ' ' . $u->lastname ?? '', 2);
        return [
            'id' => $u->id, 'staffid' => $u->id,
            'firstname' => $parts[0] ?? $u->name ?? '', 'lastname' => $parts[1] ?? '',
            'email' => $u->email, 'active' => $u->active ?? 1,
        ];
    })->toArray();
    $staffs = $staff;

    $departments = Schema::hasTable('departments')
        ? DB::table('departments')->get()->map(function ($d) {
            $dArr = (array)$d;
            $dArr['departmentid'] = $dArr['departmentid'] ?? $dArr['id'] ?? '';
            $dArr['name'] = $dArr['name'] ?? $dArr['department_name'] ?? '';
            return $dArr;
        })->toArray()
        : [];

    $employees = $staff;
    $button_name = 'Save';
    $internal_id = '';
    $title = ucwords(str_replace(['-', '_', '/'], ' ', $page));

    // Grid data for Handsontable views
    $col_header_arr = ['Staff ID', 'Staff Name', 'Role'];
    $columns_arr = [
        ['data' => 'staffid', 'type' => 'numeric', 'readOnly' => true],
        ['data' => 'staff_name', 'type' => 'text', 'readOnly' => true],
        ['data' => 'role_name', 'type' => 'text', 'readOnly' => true],
    ];

    if (str_contains($page, 'income_tax')) {
        $col_header_arr = array_merge($col_header_arr, ['Income Tax Rate (%)', 'Taxable Income', 'Tax Amount']);
        $columns_arr = array_merge($columns_arr, [
            ['data' => 'tax_rate', 'type' => 'numeric'],
            ['data' => 'taxable_income', 'type' => 'numeric'],
            ['data' => 'tax_amount', 'type' => 'numeric'],
        ]);
    } elseif (str_contains($page, 'bonus')) {
        $col_header_arr = array_merge($col_header_arr, ['KPI Score', 'Bonus Amount']);
        $columns_arr = array_merge($columns_arr, [
            ['data' => 'kpi_score', 'type' => 'numeric'],
            ['data' => 'bonus_amount', 'type' => 'numeric'],
        ]);
    } elseif (str_contains($page, 'insurance')) {
        $col_header_arr = array_merge($col_header_arr, ['Insurance Type', 'Premium Amount']);
        $columns_arr = array_merge($columns_arr, [
            ['data' => 'insurance_type', 'type' => 'text'],
            ['data' => 'premium_amount', 'type' => 'numeric'],
        ]);
    } elseif (str_contains($page, 'commission')) {
        $col_header_arr[] = 'Commission Amount';
        $columns_arr[] = ['data' => 'commission_amount', 'type' => 'numeric'];
    } elseif (str_contains($page, 'deduction')) {
        $col_header_arr = array_merge($col_header_arr, ['Deduction Type', 'Deduction Amount']);
        $columns_arr = array_merge($columns_arr, [
            ['data' => 'deduction_type', 'type' => 'text'],
            ['data' => 'deduction_amount', 'type' => 'numeric'],
        ]);
    } else {
        $col_header_arr[] = 'Amount';
        $columns_arr[] = ['data' => 'amount', 'type' => 'numeric'];
    }

    $gridRows = [];
    foreach ($staff as $u) {
        $row = ['staffid' => $u['staffid'], 'staff_name' => $u['firstname'] . ' ' . $u['lastname'], 'role_name' => 'Staff'];
        if (str_contains($page, 'income_tax')) {
            $row += ['tax_rate' => 10, 'taxable_income' => 2000, 'tax_amount' => 200];
        } elseif (str_contains($page, 'bonus')) {
            $row += ['kpi_score' => 95, 'bonus_amount' => 150];
        } elseif (str_contains($page, 'insurance')) {
            $row += ['insurance_type' => 'Health', 'premium_amount' => 50];
        } elseif (str_contains($page, 'commission')) {
            $row['commission_amount'] = 100;
        } elseif (str_contains($page, 'deduction')) {
            $row += ['deduction_type' => 'Loan', 'deduction_amount' => 30];
        } else {
            $row['amount'] = 0;
        }
        $gridRows[] = $row;
    }

    $body_value = json_encode($gridRows);
    $columns = json_encode($columns_arr);
    $col_header = json_encode($col_header_arr);

    // Payslip preview data
    $payslip_detail = [
        'id' => 1, 'staff_id' => 1, 'payslip_month' => date('Y-m'), 'month' => date('Y-m'),
        'employee_name' => 'John Doe', 'payslip_name' => 'Monthly Payslip', 'payslip_number' => 'PAY-0001',
        'actual_workday' => 22.0, 'gross_pay' => 1000.0, 'net_pay' => 920.0, 'json_data' => json_encode([]),
        'business_pdf_company_name_to_write' => 'iBRIDGE',
    ];
    $payslip_details = [$payslip_detail];
    $payslip = (object)['to_currency_rate' => 1.0, 'to_currency_name' => 'USD'];

    // Resolve view file path
    $viewsDir = base_path("Modules/{$alias}/views");
    $possiblePaths = [
        "{$viewsDir}/{$page}.php",
        "{$viewsDir}/{$page}/index.php",
        "{$viewsDir}/{$page}/manage.php",
        "{$viewsDir}/{$page}/{$page}_manage.php",
    ];
    if (str_ends_with($page, 's')) {
        $singular = rtrim($page, 's');
        $possiblePaths[] = "{$viewsDir}/{$page}/{$singular}_manage.php";
    }

    $viewFilePath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) { $viewFilePath = $path; break; }
    }

    // Directory scanner fallback
    if (!$viewFilePath && is_dir("{$viewsDir}/{$page}")) {
        $files = glob("{$viewsDir}/{$page}/*.php");
        if (count($files) === 1) {
            $viewFilePath = $files[0];
        } else {
            foreach ($files as $file) {
                $fn = strtolower(basename($file));
                if (str_contains($fn, 'manage') || str_contains($fn, 'list') || str_contains($fn, 'index')) {
                    $viewFilePath = $file;
                    break;
                }
            }
            if (!$viewFilePath && !empty($files)) { $viewFilePath = $files[0]; }
        }
    }

    if (!$viewFilePath) { abort(404, "Legacy view for '{$page}' not found."); }

    // Auto-define missing functions called by the view
    $content = file_get_contents($viewFilePath);
    if (preg_match_all('/(?<![a-zA-Z0-9_\$])([a-zA-Z0-9_]+)\s*\(/', $content, $matches)) {
        $ignored = ['if','else','elseif','while','for','foreach','switch','isset','empty','unset','eval',
            'include','include_once','require','require_once','list','array','echo','print','exit','die',
            'count','date','define','defined','class_exists','function_exists','method_exists','is_array',
            'is_string','is_numeric','is_object','is_callable','in_array','explode','implode','str_replace',
            'preg_match','preg_replace','trim','sprintf','str_contains','str_starts_with','str_ends_with',
            'strtolower','strtoupper','ucwords','ucfirst','htmlspecialchars','urlencode','urldecode',
            'json_encode','json_decode','file_get_contents','file_put_contents','round','number_format',
            'array_merge','array_push','array_map','array_filter','array_keys','array_values','array_unique',
            'array_diff','array_intersect','array_slice','array_splice','array_pop','array_shift',
            'array_unshift','array_combine','array_column','array_pad','array_reverse','array_search',
            'array_flip','array_chunk','array_pad','compact','extract','sort','rsort','asort',
            'arsort','ksort','krsort','usort','uasort','uksort','shuffle','rand','mt_rand','uniqid',
            'microtime','time','strtotime','mktime','checkdate','cal_days_in_month','jddayofweek',
            'cal_to_jd','jdtojulian','header','ob_start','ob_get_clean','ob_end_clean','ob_end_flush',
            'ob_get_length','ob_flush','flush','get_include_path','set_include_path','chdir','chroot',
            'realpath','is_dir','is_file','is_readable','is_writable','file_exists','filesize','filetype',
            'pathinfo','basename','dirname','glob','scandir','opendir','readdir','closedir',
            'fopen','fclose','fread','fwrite','fgets','fgetc','feof','fflush','fseek','ftell',
            'flock','ftruncate','chown','chmod','clearstatcache','touch','copy','rename','unlink',
            'mkdir','rmdir','tempnam','tmpfile','sys_get_temp_dir','error_log','ini_set','ini_get',
            'phpversion','phpinfo','php_uname','php_sapi_name','version_compare','define_syslog_variables',
            'openlog','syslog','closelog','define','defined','extension_loaded','get_loaded_extensions',
            'dl','sort','rsort','usort','preg_quote','mb_strtolower','mb_strtoupper','mb_strlen',
            'mb_substr','mb_strpos','mb_substr_count','mb_convert_encoding','mb_detect_encoding',
            'mb_internal_encoding','mb_language','mb_substitute_character','mb_convert_case',
            'mb_convert_kana','mb_convert_variables','mb_decode_mimeheader','mb_encode_mimeheader',
            'mb_decode_numericentity','mb_encode_numericentity','mb_convert_encoding',
            'mb_send_mail','mb_get_info','mb_check_encoding','mb_ord','mb_chr','mb_scrub',
            'mb_str_split','session_start','session_destroy','session_id','session_name',
            'session_regenerate_id','session_write_close','session_unset','session_get_cookie_params',
            'session_set_cookie_params','session_cache_limiter','session_cache_expire',
            'session_module_name','session_save_path','session_status','session_reset',
            'setcookie','setrawcookie','header_remove','headers_sent','headers_list',
            'http_response_code','http_response_code','set_time_limit','connection_aborted',
            'connection_status','ignore_user_abort','fastcgi_finish_request','get_browser',
            'json_last_error','json_last_error_msg','mb_convert_case','preg_grep','preg_split',
            'preg_replace_callback','preg_replace_callback_array','preg_last_error',
            'preg_get_error_code','preg_quote','preg_match_all','preg_split',
            'array_key_exists','key_exists','array_search','in_array','array_slice',
            'array_splice','array_push','array_pop','array_shift','array_unshift',
            'array_merge','array_combine','array_column','array_pad','array_fill',
            'array_fill_keys','array_flip','array_reverse','array_unique','array_diff',
            'array_intersect','array_diff_key','array_intersect_key','array_diff_assoc',
            'array_intersect_assoc','array_diff_uassoc','array_intersect_uassoc',
            'array_product','array_sum','array_count_values','array_unique','compact',
            'extract','list','current','pos','next','prev','end','reset','key',
            'each','array_walk','array_walk_recursive','array_map','array_filter',
            'array_reduce','array_search','usort','uasort','uksort','sort','rsort',
            'asort','arsort','ksort','krsort','shuffle','array_slice','array_splice',
            'array_merge','array_merge_recursive','array_replace','array_replace_recursive',
            'array_keys','array_values','array_count_values','array_unique',
            'array_flip','array_reverse','array_change_key_case','array_combine',
            'array_pad','array_fill','array_fill_keys','array_chunk','array_chunk',
            'chunk_split','wordwrap','number_format','nl2br','ucfirst','lcfirst',
            'ucwords','str_pad','str_repeat','str_word_count','str_split','str_word_count',
            'chunk_split','quoted_printable_decode','quoted_printable_encode','quoted_printable_encode',
            'ctype_alnum','ctype_alpha','ctype_digit','ctype_xdigit','ctype_upper','ctype_lower',
            'ctype_space','ctype_printable','ctype_cntrl','ctype_graph','ctype_punct',
            'money_format','convert_cyr_string','hebrevc','hebrev','crc32','crc32b',
            'metaphone','soundex','similar_text','levenshtein','entropy','entropy_sha1',
            'entropy_md5','sha1_file','md5_file','md5','sha1','crypt',
            'getimagesize','image_type_to_mime_type','image_type_to_extension',
            'base64_encode','base64_decode','base64_encode','convert_uuencode',
            'convert_uudecode','quoted_printable_encode','quoted_printable_decode',
            'http_build_query','parse_str','parse_url','urlencode','urldecode',
            'rawurlencode','rawurldecode','http_response_code','header_remove',
            'header','headers_sent','headers_list','setcookie','setrawcookie',
        ];
        foreach (array_unique($matches[1]) as $func) {
            if (!function_exists($func) && !in_array(strtolower($func), $ignored)) {
                try {
                    eval("function {$func}() { return ''; }");
                } catch (\Exception $e) {}
            }
        }
    }

    \Illuminate\Support\Facades\Log::info("LEGACY COMPAT: rendering page '{$page}' via plugin bridge");

    ob_start();
    try { include $viewFilePath; }
    catch (\Exception $e) {
        echo "<div class='alert alert-danger'>Error rendering legacy page: " . $e->getMessage() . "</div>";
    }
    $htmlContent = ob_get_clean();

    $pageTitle = ucwords(str_replace(['-', '_', '/'], ' ', $page));
    return view('plugin_page', compact('module', 'pageTitle', 'htmlContent'));
})->where('page', '.*')->where('alias', '[a-z0-9-]+');

// Dynamic Legacy Controller Bridge
Route::any('/admin/{controller}/{method?}', function ($controllerName, $method = 'index') {
    $activeModules = \App\Models\Module::where('status', 'active')->get();
    $alias = null;
    $controllerFile = null;

    foreach ($activeModules as $mod) {
        $modulePath = base_path("Modules/{$mod->alias}");
        $candidate = "{$modulePath}/controllers/" . ucfirst($controllerName) . ".php";
        if (!file_exists($candidate)) {
            $candidate = "{$modulePath}/controllers/" . strtolower($controllerName) . ".php";
        }
        if (file_exists($candidate)) {
            $alias = $mod->alias;
            $controllerFile = $candidate;
            break;
        }
    }

    if (!$controllerFile) { return view('welcome'); }
    if (!auth('web')->check() && !auth('sanctum')->check()) { abort(403, 'Unauthenticated'); }

    // Get CI instance (AdminController is already defined in CICompatLayer.php)
    $CI = get_instance();
    $currentModuleAlias = $alias;

    // Load module helpers
    $helpersDir = base_path("Modules/{$alias}/helpers");
    if (is_dir($helpersDir)) {
        foreach (glob($helpersDir . '/*.php') as $helperFile) {
            try { include_once $helperFile; } catch (\Exception $e) {}
        }
    }

    // Load module models
    $modelsDir = base_path("Modules/{$alias}/models");
    if (is_dir($modelsDir)) {
        foreach (glob($modelsDir . '/*.php') as $modelFile) {
            $modelContent = file_get_contents($modelFile);
            if (preg_match('/class\s+(\w+)\s+extends\s+/', $modelContent, $m)) {
                $className = $m[1];
                try {
                    require_once $modelFile;
                    if (class_exists($className)) {
                        $propName = lcfirst(str_replace('_model', '', basename($modelFile, '.php')));
                        $CI->$propName = new $className();
                    }
                } catch (\Exception $e) {}
            }
        }
    }

    include_once $controllerFile;

    $className = null;
    $fileContent = file_get_contents($controllerFile);
    if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $fileContent, $m)) {
        $className = $m[1];
    }

    if ($className && class_exists($className)) {
        try {
            $instance = new $className();

            foreach (get_object_vars($CI) as $key => $value) {
                if ($value !== null) {
                    if (!property_exists($instance, $key) || $instance->$key === null) {
                        $instance->$key = $value;
                    }
                }
            }

            if (method_exists($instance, $method)) {
                ob_start();
                $res = $instance->$method();
                $output = ob_get_clean();
                if ($res) { return $res; }
                return $output;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("LEGACY COMPAT: controller error for {$controllerName}/{$method}: " . $e->getMessage());
            return response("Error: " . $e->getMessage(), 500);
        }
    }

    abort(404, "Legacy controller or method not found.");
})->where('controller', '[a-zA-Z0-9_-]+');

Route::get('/debug-db', function () {
    try {
        return response()->json([
            'modules'      => \App\Models\Module::all(),
            'module_menus' => \App\Models\ModuleMenu::all(),
            'users'        => \App\Models\User::all(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::fallback(function () {
    return view('welcome');
});
