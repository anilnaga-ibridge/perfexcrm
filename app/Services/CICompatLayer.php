<?php

/**
 * Full CodeIgniter Compatibility Layer for Perfex CRM Laravel port.
 * Provides CI_DB_active_record, CI_Loader, CI_Input, CI_Session, AdminController, App_Model
 * and all necessary helper functions so legacy CI modules work inside Laravel.
 */
if (defined('CI_COMPAT_LOADED')) return;
define('CI_COMPAT_LOADED', true);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

// ─── CI_Model (base model class) ──────────────────────────────────────────────
if (!class_exists('CI_Model')) {
    class CI_Model {
        public $db;
        public $load;
        public $input;
        public function __construct() {
            $ci = get_instance();
            $this->db = $ci->db;
            $this->load = $ci->load;
            $this->input = $ci->input;
        }
    }
}

// ─── App_Model (Perfex base model, extends CI_Model) ──────────────────────────
if (!class_exists('App_Model')) {
    class App_Model extends CI_Model {
        public function __construct() {
            parent::__construct();
        }
    }
}

// ─── CIDB — Active Record compat layer ────────────────────────────────────────
class CIDB {
    protected $wheres = [];
    protected $orWheres = [];
    protected $selects = [];
    protected $joins = [];
    protected $orderBys = [];
    protected $groupBys = [];
    protected $havings = [];
    protected $limitVal = null;
    protected $offsetVal = null;
    protected $fromTable = null;
    protected $setClause = [];
    protected $lastQuery = '';
    protected $lastAffectedRows = 0;
    protected $lastInsertId = 0;
    public $char_set = 'utf8mb4';

    public function __construct() {
        $this->char_set = config('database.connections.mysql.charset', 'utf8mb4') ?? 'utf8mb4';
    }

    protected function reset() {
        $this->wheres = [];
        $this->orWheres = [];
        $this->selects = [];
        $this->joins = [];
        $this->orderBys = [];
        $this->groupBys = [];
        $this->havings = [];
        $this->limitVal = null;
        $this->offsetVal = null;
        $this->fromTable = null;
        $this->setClause = [];
    }

    public function get($table = '') {
        if ($table !== '') {
            $this->fromTable = $table;
        }
        $table = $this->fromTable;
        if (!$table) {
            $this->reset();
            return $this->emptyResult();
        }
        $sql = $this->buildSelect($table);
        $this->reset();
        return $this->executeQuery($sql);
    }

    public function get_where($table = '', $where = []) {
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    $this->where($k, $v);
                } else {
                    $this->where($k, $v);
                }
            }
        }
        return $this->get($table);
    }

    public function select($selects) {
        if (is_string($selects)) {
            $selects = array_map('trim', explode(',', $selects));
        }
        foreach ((array)$selects as $s) {
            $s = trim($s);
            if ($s !== '') {
                $this->selects[] = $s;
            }
        }
        return $this;
    }

    public function from($table) {
        $this->fromTable = $table;
        return $this;
    }

    public function where($key, $value = null, $escape = true) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->wheres[] = ['AND', $k, '=', $v];
            }
        } elseif ($value === null) {
            $this->wheres[] = ['AND', $key, 'IS NULL', null];
        } elseif (is_array($value)) {
            $escaped = array_map(function($v) { return is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'"; }, $value);
            $this->wheres[] = ['AND', $key, 'IN', '(' . implode(',', $escaped) . ')'];
        } else {
            // Check for operator in key (e.g. 'id >')
            if (preg_match('/^\s*(\w+)\s*(>=|<=|!=|<>|>|<|=|LIKE|like)\s*$/', $key, $m)) {
                $this->wheres[] = ['AND', $m[1], $m[2], $value];
            } else {
                $this->wheres[] = ['AND', $key, '=', $value];
            }
        }
        return $this;
    }

    public function or_where($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->orWheres[] = ['OR', $k, '=', $v];
            }
        } elseif ($value === null) {
            $this->orWheres[] = ['OR', $key, 'IS NULL', null];
        } else {
            if (preg_match('/^\s*(\w+)\s*(>=|<=|!=|<>|>|<|=|LIKE|like)\s*$/', $key, $m)) {
                $this->orWheres[] = ['OR', $m[1], $m[2], $value];
            } else {
                $this->orWheres[] = ['OR', $key, '=', $value];
            }
        }
        return $this;
    }

    public function where_in($key = '', $values = []) {
        $escaped = array_map(function($v) { return is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'"; }, (array)$values);
        $this->wheres[] = ['AND', $key, 'IN', '(' . implode(',', $escaped) . ')'];
        return $this;
    }

    public function where_not_in($key = '', $values = []) {
        $escaped = array_map(function($v) { return is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'"; }, (array)$values);
        $this->wheres[] = ['AND', $key, 'NOT IN', '(' . implode(',', $escaped) . ')'];
        return $this;
    }

    public function like($field, $match = '', $side = 'both') {
        $pattern = $side === 'before' ? "%{$match}" : ($side === 'after' ? "{$match}%" : "%{$match}%");
        $this->wheres[] = ['AND', $field, 'LIKE', $pattern];
        return $this;
    }

    public function not_like($field, $match = '', $side = 'both') {
        $pattern = $side === 'before' ? "%{$match}" : ($side === 'after' ? "{$match}%" : "%{$match}%");
        $this->wheres[] = ['AND', $field, 'NOT LIKE', $pattern];
        return $this;
    }

    public function or_like($field, $match = '', $side = 'both') {
        $pattern = $side === 'before' ? "%{$match}" : ($side === 'after' ? "{$match}%" : "%{$match}%");
        $this->orWheres[] = ['OR', $field, 'LIKE', $pattern];
        return $this;
    }

    public function order_by($field, $direction = 'ASC') {
        $direction = strtoupper(trim($direction));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        $this->orderBys[] = "{$field} {$direction}";
        return $this;
    }

    public function join($table, $cond, $type = '') {
        $type = strtoupper(trim($type));
        if (!in_array($type, ['LEFT', 'RIGHT', 'INNER', 'OUTER', ''])) {
            $type = '';
        }
        $prefix = $type !== '' ? "{$type} " : '';
        $this->joins[] = "{$prefix}JOIN {$table} ON {$cond}";
        return $this;
    }

    public function group_by($by) {
        $this->groupBys[] = $by;
        return $this;
    }

    public function having($key, $value = '', $escape = true) {
        if ($value !== '') {
            $this->havings[] = "{$key} {$value}";
        } else {
            $this->havings[] = $key;
        }
        return $this;
    }

    public function limit($val, $offset = 0) {
        $this->limitVal = (int)$val;
        $this->offsetVal = (int)$offset;
        return $this;
    }

    public function offset($val) {
        $this->offsetVal = (int)$val;
        return $this;
    }

    public function set($key, $value = null, $escape = true) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setClause[$k] = $v;
            }
        } else {
            $this->setClause[$key] = $value;
        }
        return $this;
    }

    // ─── Build SQL helpers ────────────────────────────────────────────────────────

    protected function buildSelect($table) {
        $cols = !empty($this->selects) ? implode(', ', $this->selects) : '*';
        $sql = "SELECT {$cols} FROM {$table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        if (!empty($this->groupBys)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBys);
        }

        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }

        if (!empty($this->orderBys)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBys);
        }

        if ($this->limitVal !== null) {
            $sql .= ' LIMIT ' . $this->limitVal;
            if ($this->offsetVal > 0) {
                $sql .= ' OFFSET ' . $this->offsetVal;
            }
        }

        return $sql;
    }

    protected function buildWhereClause() {
        $parts = [];
        foreach ($this->wheres as $w) {
            $parts[] = $this->compileWhere($w);
        }
        foreach ($this->orWheres as $w) {
            $parts[] = 'OR ' . $this->compileWhere($w);
        }
        if (empty($parts)) return '';

        $sql = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            if (strtoupper(substr($parts[$i], 0, 3)) === 'OR ') {
                $sql .= ' ' . $parts[$i];
            } else {
                $sql .= ' AND ' . $parts[$i];
            }
        }
        // Remove leading AND/OR
        $sql = preg_replace('/^\s*AND\s+/i', '', $sql);
        $sql = preg_replace('/^\s*OR\s+/i', '', $sql);
        return $sql;
    }

    protected function compileWhere($w) {
        [$logic, $col, $op, $val] = $w;
        $op = strtoupper(trim($op));

        if ($op === 'IS NULL') {
            return "{$col} IS NULL";
        }
        if ($op === 'IN' || $op === 'NOT IN') {
            return "{$col} {$op} {$val}";
        }
        if ($op === 'LIKE' || $op === 'NOT LIKE') {
            $escaped = addslashes((string)$val);
            return "{$col} {$op} '{$escaped}'";
        }

        if ($val === null) {
            return "{$col} IS NULL";
        }

        $escaped = addslashes((string)$val);
        return "{$col} {$op} '{$escaped}'";
    }

    // ─── Execute ────────────────────────────────────────────────────────────────

    protected function executeQuery($sql) {
        $this->lastQuery = $sql;
        $isSelect = (stripos(trim($sql), 'SELECT') === 0);

        try {
            if ($isSelect) {
                $raw = DB::select($sql);
                $rows = array_map(function($r) { return (array)$r; }, $raw);
                $count = count($raw);
                $this->lastAffectedRows = $count;
                return new CIDBResult($raw, $rows, $count);
            } else {
                DB::statement($sql);
                $this->lastAffectedRows = DB::affectingStatement() ?? 0;
                // For insert, get last insert ID
                if (stripos(trim($sql), 'INSERT') === 0) {
                    $this->lastInsertId = DB::getPdo()->lastInsertId();
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("CIDB query error: " . $e->getMessage() . " | SQL: " . $sql);
            $this->lastAffectedRows = 0;
            if ($isSelect) {
                return new CIDBResult([], [], 0);
            }
            return false;
        }
    }

    // ─── CRUD operations ────────────────────────────────────────────────────────

    public function insert($table, $data = []) {
        if (empty($data) && !empty($this->setClause)) {
            $data = $this->setClause;
            $this->setClause = [];
        }
        if (empty($data)) { $this->reset(); return false; }

        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_map(function($v) { return is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'"; }, array_values($data)));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
        $this->reset();
        $this->executeQuery($sql);
        $this->lastInsertId = DB::getPdo()->lastInsertId();
        return true;
    }

    public function insert_batch($table, $data = []) {
        if (empty($data)) { $this->reset(); return 0; }

        $cols = array_keys($data[0]);
        $colStr = implode(', ', $cols);
        $rows = [];
        foreach ($data as $row) {
            $vals = array_map(function($v) { return is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'"; }, array_values($row));
            $rows[] = '(' . implode(', ', $vals) . ')';
        }
        $sql = "INSERT INTO {$table} ({$colStr}) VALUES " . implode(', ', $rows);
        $this->reset();
        $this->executeQuery($sql);
        return count($data);
    }

    public function update($table, $data = []) {
        if (empty($data)) { $this->reset(); return false; }

        $setParts = [];
        foreach ($data as $k => $v) {
            $escaped = is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'";
            $setParts[] = "{$k} = {$escaped}";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts);

        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $this->reset();
        $this->executeQuery($sql);
        return $this->lastAffectedRows;
    }

    public function update_batch($table, $data = [], $index = '') {
        if (empty($data) || $index === '') { $this->reset(); return 0; }

        $affected = 0;
        foreach ($data as $row) {
            if (!isset($row[$index])) continue;
            $id = $row[$index];
            $setParts = [];
            foreach ($row as $k => $v) {
                if ($k === $index) continue;
                $escaped = is_null($v) ? 'NULL' : "'" . addslashes((string)$v) . "'";
                $setParts[] = "{$k} = {$escaped}";
            }
            if (empty($setParts)) continue;
            $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$index} = '" . addslashes((string)$id) . "'";
            $this->executeQuery($sql);
            $affected++;
        }
        $this->reset();
        $this->lastAffectedRows = $affected;
        return $affected;
    }

    public function delete($table, $where = []) {
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $this->where($k, $v);
            }
        }

        $sql = "DELETE FROM {$table}";
        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $this->reset();
        $this->executeQuery($sql);
        return $this->lastAffectedRows;
    }

    public function truncate($table) {
        $sql = "TRUNCATE TABLE {$table}";
        $this->reset();
        $this->executeQuery($sql);
        return true;
    }

    // ─── Query result ────────────────────────────────────────────────────────────

    public function query($sql) {
        $this->lastQuery = $sql;

        // Rewrite modules table
        if (preg_match('/from\s+`?(tbl)?modules`?/i', $sql)) {
            $sql = preg_replace('/from\s+`?(tbl)?modules`?/i', 'from modules', $sql);
            $sql = str_replace('module_name', 'alias', $sql);
            $sql = str_replace('active = 1', "status = 'active'", $sql);
            $sql = str_replace('active = 0', "status = 'inactive'", $sql);
            $sql = str_replace('active =', "status =", $sql);
        }

        $isSelect = (stripos(trim($sql), 'SELECT') === 0);
        try {
            if ($isSelect) {
                $raw = DB::select($sql);
                $rows = array_map(function($r) { return (array)$r; }, $raw);
                $count = count($raw);
                $this->lastAffectedRows = $count;
                return new CIDBResult($raw, $rows, $count);
            } else {
                DB::statement($sql);
                $this->lastAffectedRows = DB::affectingStatement() ?? 0;
                if (stripos(trim($sql), 'INSERT') === 0) {
                    $this->lastInsertId = DB::getPdo()->lastInsertId();
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("CIDB compat error: " . $e->getMessage() . " | SQL: " . $sql);
            $this->lastAffectedRows = 0;
            if ($isSelect) {
                return new CIDBResult([], [], 0);
            }
            return false;
        }
    }

    public function affected_rows() {
        return $this->lastAffectedRows;
    }

    public function insert_id() {
        return $this->lastInsertId;
    }

    public function table_exists($table) {
        return Schema::hasTable($table);
    }

    public function field_exists($field, $table) {
        return Schema::hasColumn($table, $field);
    }

    public function count_all($table = '') {
        if ($table === '') return 0;
        $sql = "SELECT COUNT(*) as cnt FROM {$table}";
        try {
            $r = DB::select($sql);
            return $r[0]->cnt ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function count_all_results($table = '') {
        if ($table !== '') {
            $this->fromTable = $table;
        }
        $table = $this->fromTable;
        if (!$table) { $this->reset(); return 0; }

        $sql = "SELECT COUNT(*) as cnt FROM {$table}";
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $this->reset();
        try {
            $r = DB::select($sql);
            return $r[0]->cnt ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function list_fields($table) {
        try {
            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function field_data($table) {
        try {
            $columns = Schema::getColumns($table);
            return array_map(function($c) {
                return (object)$c;
            }, $columns);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function trans_start() { DB::beginTransaction(); return true; }
    public function trans_complete() { DB::commit(); return true; }
    public function trans_status() { return true; }

    public function get_compiled_select($table = '', $reset = true) {
        if ($table !== '') $this->fromTable = $table;
        $sql = $this->buildSelect($this->fromTable);
        if ($reset) $this->reset();
        return $sql;
    }

    public function get_compiled_insert($table = '', $reset = true) { $this->reset(); return ''; }
    public function get_compiled_update($table = '', $reset = true) { $this->reset(); return ''; }
    public function get_compiled_delete($table = '', $reset = true) { $this->reset(); return ''; }
}

// ─── CIDBResult — query result object ──────────────────────────────────────────
class CIDBResult {
    protected $raw;
    protected $rows;
    protected $count;
    protected $pointer = 0;

    public function __construct($raw, $rows, $count) {
        $this->raw = $raw;
        $this->rows = $rows;
        $this->count = $count;
    }

    public function row($n = 0, $type = 'object') {
        if (!isset($this->raw[$n])) return null;
        return $type === 'array' ? $this->rows[$n] : (object)$this->raw[$n];
    }

    public function result($type = 'object') {
        if ($type === 'array') return $this->rows;
        return array_map(function($r) { return (object)$r; }, $this->raw);
    }

    public function result_array() {
        return $this->rows;
    }

    public function result_object() {
        return array_map(function($r) { return (object)$r; }, $this->raw);
    }

    public function num_rows() {
        return $this->count;
    }

    public function num_fields() {
        return !empty($this->raw) ? count((array)$this->raw[0]) : 0;
    }

    public function field_data() {
        return [];
    }

    public function unbuffered_row($type = 'object') {
        if (!isset($this->raw[$this->pointer])) return null;
        $row = $type === 'array' ? $this->rows[$this->pointer] : (object)$this->raw[$this->pointer];
        $this->pointer++;
        return $row;
    }

    public function first_row($type = 'object') {
        return $this->row(0, $type);
    }

    public function last_row($type = 'object') {
        return $this->row($this->count - 1, $type);
    }

    public function next_row($type = 'object') {
        return $this->unbuffered_row($type);
    }

    public function prev_row($type = 'object') {
        if ($this->pointer < 2) return null;
        $this->pointer -= 2;
        return $this->unbuffered_row($type);
    }

    public function free_result() {
        $this->raw = [];
        $this->rows = [];
        $this->count = 0;
    }
}

// ─── CILoader ──────────────────────────────────────────────────────────────────
class CILoader {
    protected $loadedModels = [];
    protected $controllerInstance = null;
    protected $lastViewData = [];

    public function setController($instance) {
        $this->controllerInstance = $instance;
    }

    public function model($model, $name = '', $connect = false) {
        if (is_array($model)) {
            foreach ($model as $m) {
                $this->model($m);
            }
            return;
        }

        // Parse model name: 'currencies_model' or 'staff/staff_model'
        $parts = explode('/', $model);
        $modelName = end($parts);
        $name = $name ?: $modelName;

        if (isset($this->loadedModels[$name])) return;

        // Try to find model file in active module
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        $found = false;
        foreach ($activeModules as $mod) {
            // Try different path patterns
            $paths = [
                base_path("Modules/{$mod->alias}/models/" . ucfirst($modelName) . ".php"),
                base_path("Modules/{$mod->alias}/models/" . $modelName . ".php"),
                base_path("Modules/{$mod->alias}/models/" . str_replace('/', '/', $model) . ".php"),
                base_path("Modules/{$mod->alias}/models/" . ucfirst(str_replace('/', '/', $model)) . ".php"),
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    // Find the class name
                    $content = file_get_contents($path);
                    if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $m)) {
                        $className = $m[1];
                        if (class_exists($className)) {
                            $ci = get_instance();
                            $propertyName = $name;
                            $ci->$propertyName = new $className();
                            if ($this->controllerInstance) {
                                $prop = $propertyName;
                                if (!property_exists($this->controllerInstance, $prop) || $this->controllerInstance->$prop === null) {
                                    $this->controllerInstance->$prop = $ci->$propertyName;
                                }
                            }
                            $this->loadedModels[$name] = true;
                            $found = true;
                            break 2;
                        }
                    }
                }
            }
        }

        if (!$found) {
            // Try core CI compat models directory
            $corePaths = [
                app_path("Services/CICoreModels/" . ucfirst($modelName) . ".php"),
                app_path("Services/CICoreModels/" . $modelName . ".php"),
            ];
            foreach ($corePaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $content = file_get_contents($path);
                    if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $m)) {
                        $className = $m[1];
                        if (class_exists($className)) {
                            $ci = get_instance();
                            $ci->$name = new $className();
                            if ($this->controllerInstance) {
                                $prop = $name;
                                if (!property_exists($this->controllerInstance, $prop) || $this->controllerInstance->$prop === null) {
                                    $this->controllerInstance->$prop = $ci->$name;
                                }
                            }
                            $this->loadedModels[$name] = true;
                            $found = true;
                            break;
                        }
                    }
                }
            }
        }

        if (!$found) {
            // Create an empty stub to prevent "property does not exist" errors
            $ci = get_instance();
            $ci->$name = new class {
                public function __call($name, $args) { return null; }
            };
            $this->loadedModels[$name] = true;
        }
    }

    public function view($view, $data = [], $return = false) {
        $ci = get_instance();
        $activeModules = \App\Models\Module::where('status', 'active')->get();

        // CI behavior: when called from a view without data, inherit parent view data
        if (empty($data) && !empty($this->lastViewData)) {
            $data = $this->lastViewData;
        }

        // Try loading from module views
        foreach ($activeModules as $mod) {
            // Try common view path patterns
            $paths = [
                base_path("Modules/{$mod->alias}/views/{$view}.php"),
                base_path("Modules/{$mod->alias}/views/{$mod->alias}/{$view}.php"),
            ];
            foreach ($paths as $viewPath) {
                if (file_exists($viewPath)) {
                    extract((array)$data);
                    extract((array)$ci->data ?? []);
                    $this->lastViewData = $data;
                    // Set $this->load so CI views can call $this->load->view()
                    $this->load = $this;
                    if ($return) {
                        ob_start();
                        include $viewPath;
                        return ob_get_clean();
                    }
                    include $viewPath;
                    return;
                }
            }
        }
        return '';
    }

    public function library($library, $params = null, $name = '') {
        if (is_array($library)) {
            foreach ($library as $l) {
                $this->library($l);
            }
            return;
        }

        $parts = explode('/', $library);
        $libName = end($parts);
        $name = $name ?: $libName;

        $ci = get_instance();
        // Try to find library in module
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        foreach ($activeModules as $mod) {
            $libPath = base_path("Modules/{$mod->alias}/libraries/" . str_replace('/', '/', $library) . ".php");
            if (file_exists($libPath)) {
                require_once $libPath;
                // Find class name
                $content = file_get_contents($libPath);
                if (preg_match('/class\s+(\w+)/', $content, $m)) {
                    $className = $m[1];
                    if (class_exists($className)) {
                        $ci->$name = new $className();
                        return;
                    }
                }
            }
        }

        // Stub if not found
        $ci->$name = new class {
            public function __call($name, $args) { return null; }
        };
    }

    public function helper($helpers) {
        if (!is_array($helpers)) $helpers = [$helpers];
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        foreach ($helpers as $helper) {
            foreach ($activeModules as $mod) {
                $path = base_path("Modules/{$mod->alias}/helpers/{$helper}.php");
                if (file_exists($path)) {
                    require_once $path;
                    return;
                }
            }
        }
    }

    public function database($queryObject = '', $queryBuilder = false) { }
    public function config($item = '') { return config($item); }
    public function file($file, $read = false, $return = false) { return ''; }
    public function language($file = '', $module = '', $return = false) { return []; }
}

// ─── CI_Input ──────────────────────────────────────────────────────────────────
class CI_Input {
    public function post($key = null, $xss_clean = false) {
        if ($key === null) {
            return request()->except(['_token', '_method']);
        }
        return request()->input($key);
    }

    public function get($key = null, $xss_clean = false) {
        if ($key === null) {
            return request()->query();
        }
        return request()->query($key);
    }

    public function server($index = null, $xss_clean = false) {
        if ($index === null) {
            return request()->server();
        }
        return request()->server($index);
    }

    public function cookie($index = null, $xss_clean = false) {
        if ($index === null) {
            return request()->cookies();
        }
        return request()->cookie($index);
    }

    public function is_ajax_request() {
        return request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function is_cli_request() {
        return php_sapi_name() === 'cli';
    }

    public function ip_address() {
        return request()->ip();
    }

    public function method() {
        return request()->method();
    }

    public function post_get($key = '', $xss_clean = false) {
        return request()->input($key) ?? request()->query($key);
    }

    public function get_post($key = '', $xss_clean = false) {
        return $this->post_get($key, $xss_clean);
    }

    public function raw_input() {
        return file_get_contents('php://input');
    }
}

// ─── CI_Session stub ───────────────────────────────────────────────────────────
class CI_Session {
    protected $data = [];
    public function __construct() { $this->data = session()->all(); }
    public function userdata($key = null) {
        if ($key === null) return $this->data;
        return $this->data[$key] ?? null;
    }
    public function set_userdata($data, $value = null) {
        if (is_array($data)) {
            foreach ($data as $k => $v) session()->put($k, $v);
        } else {
            session()->put($data, $value);
        }
    }
    public function unset_userdata($data) {
        if (is_array($data)) {
            foreach ($data as $k) session()->forget($k);
        } else {
            session()->forget($data);
        }
    }
    public function flashdata($key = null) { return session()->getFlashBag()->get($key); }
    public function set_flashdata($data, $value = null) {
        if (is_array($data)) {
            foreach ($data as $k => $v) session()->flash($k, $v);
        } else {
            session()->flash($data, $value);
        }
    }
    public function has_userdata($key) { return session()->has($key); }
    public function all_userdata() { return session()->all(); }
    public function sess_destroy() { session()->flush(); }
}

// ─── CI_URI stub ───────────────────────────────────────────────────────────────
class CI_URI {
    public function segment($n = 0, $no_result = false) { return request()->segment($n) ?: $no_result; }
    public function uri_string() { return request()->path(); }
    public function rsegment($n = 0, $no_result = false) { return request()->segment($n) ?: $no_result; }
}

// ─── CI_Hooks stub ─────────────────────────────────────────────────────────────
class CI_Hooks {
    protected $actions = [];
    protected $filters = [];
    protected $actionResults = [];

    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions[$hook][] = ['callback' => $callback, 'priority' => $priority, 'args' => $accepted_args];
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters[$hook][] = ['callback' => $callback, 'priority' => $priority, 'args' => $accepted_args];
    }

    public function do_action($hook, ...$args) {
        if (isset($this->actions[$hook])) {
            foreach ($this->actions[$hook] as $action) {
                try {
                    if (is_callable($action['callback'])) {
                        call_user_func_array($action['callback'], array_slice($args, 0, $action['args']));
                    } elseif (is_string($action['callback']) && function_exists($action['callback'])) {
                        call_user_func_array($action['callback'], array_slice($args, 0, $action['args']));
                    }
                } catch (\Exception $e) {
                    Log::warning("Hook action error [{$hook}]: " . $e->getMessage());
                }
            }
        }
    }

    public function apply_filters($hook, $value, ...$args) {
        if (isset($this->filters[$hook])) {
            foreach ($this->filters[$hook] as $filter) {
                try {
                    if (is_callable($filter['callback'])) {
                        $value = call_user_func_array($filter['callback'], array_merge([$value], array_slice($args, 0, $filter['args'])));
                    } elseif (is_string($filter['callback']) && function_exists($filter['callback'])) {
                        $value = call_user_func_array($filter['callback'], array_merge([$value], array_slice($args, 0, $filter['args'])));
                    }
                } catch (\Exception $e) {
                    Log::warning("Hook filter error [{$hook}]: " . $e->getMessage());
                }
            }
        }
        return $value;
    }
}

// ─── get_instance() global function ────────────────────────────────────────────
if (!function_exists('get_instance')) {
    function &get_instance() {
        global $CI;
        if (!isset($CI)) {
            $CI = new class {
                public $db;
                public $load;
                public $input;
                public $session;
                public $uri;
                public $hooks;
                public $data = [];
                public function __construct() {
                    $this->db = new CIDB();
                    $this->load = new CILoader();
                    $this->input = new CI_Input();
                    $this->session = new CI_Session();
                    $this->uri = new CI_URI();
                    $this->hooks = new CI_Hooks();
                }
            };
        }
        return $CI;
    }
}

// ─── CI Helper functions ───────────────────────────────────────────────────────
if (!function_exists('hooks')) {
    function hooks() {
        static $hookInstance = null;
        if ($hookInstance === null) {
            $hookInstance = new CI_Hooks();
        }
        return $hookInstance;
    }
}

if (!function_exists('db_prefix')) {
    function db_prefix() { return ''; }
}

if (!function_exists('get_staff_user_id')) {
    function get_staff_user_id() { return auth()->id() ?? 1; }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        $user = auth()->user();
        if (!$user) return false;
        // Check if user has admin role
        if (method_exists($user, 'role')) {
            return $user->role == 'admin' || $user->role == 1;
        }
        if (isset($user->role_id) && $user->role_id == 1) return true;
        // Check role_permissions table
        return $user->is_admin ?? false;
    }
}

if (!function_exists('is_staff_logged_in')) {
    function is_staff_logged_in() {
        return auth()->check();
    }
}

if (!function_exists('has_permission')) {
    function has_permission($permission = '', $staff_id = '', $action = '') {
        // In admin mode, always grant
        if (is_admin()) return true;
        // Check role_permissions table
        try {
            $userId = $staff_id ?: auth()->id();
            if (!$userId) return false;
            $perm = DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->join('users', 'users.id', '=', 'role_permissions.role_id')
                ->where('permissions.name', $permission)
                ->where('users.id', $userId)
                ->first();
            return $perm !== null;
        } catch (\Exception $e) {
            return true; // Fail open
        }
    }
}

if (!function_exists('access_denied')) {
    function access_denied($message = '') {
        http_response_code(403);
        echo '<div class="alert alert-danger">';
        echo '<strong>Access Denied</strong> - ' . htmlspecialchars($message ?: 'You do not have permission to access this resource.');
        echo '</div>';
    }
}

if (!function_exists('set_alert')) {
    function set_alert($type = 'success', $message = '', $is_view = true) {
        // Store in session flash
        $alerts = session()->get('alert_float_messages', []);
        $alerts[] = ['type' => $type, 'message' => $message];
        session()->put('alert_float_messages', $alerts);
    }
}

if (!function_exists('alert_float')) {
    function alert_float($type = null, $message = null, $stay = false) {
        if ($type === null && $message === null) {
            $messages = session()->get('alert_float_messages', []);
            session()->forget('alert_float_messages');
            return $messages;
        }
        set_alert($type, $message);
        return '';
    }
}

if (!function_exists('staff_profile_image')) {
    function staff_profile_image($staff_id = 0, $staff_info = []) {
        return '<img src="https://ui-avatars.com/api/?name=Staff&size=40&background=6c757d&color=fff" alt="Staff" class="staff-profile-image">';
    }
}

if (!function_exists('get_staff_full_name')) {
    function get_staff_full_name($staff_id = 0) {
        try {
            $staff = DB::table('users')->where('id', $staff_id)->first();
            return $staff ? trim(($staff->firstname ?? '') . ' ' . ($staff->lastname ?? '')) : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}

if (!function_exists('get_staff_email')) {
    function get_staff_email($staff_id = 0) {
        try {
            $staff = DB::table('users')->where('id', $staff_id)->first();
            return $staff ? $staff->email : '';
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('staff_email')) {
    function staff_email($staff_id = 0) { return get_staff_email($staff_id); }
}

if (!function_exists('log_activity')) {
    function log_activity($description = '', $staff_id = null, $type = 'info', $additional_data = []) {
        // Silently ignore or store in a simple log
    }
}

if (!function_exists('app_format_money')) {
    function app_format_money($amount = 0, $symbol = '$') {
        return $symbol . number_format((float)$amount, 2);
    }
}

if (!function_exists('_currency')) {
    function _currency($amount = 0, $symbol = '$', $decimals = 2) {
        return $symbol . number_format((float)$amount, $decimals);
    }
}

if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'location', $http_response_code = 302) {
        $url = str_starts_with($uri, 'http') ? $uri : url($uri);
        header("Location: {$url}", true, $http_response_code);
        exit;
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') { return url($path); }
}

if (!function_exists('site_url')) {
    function site_url($path = '') { return url($path); }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') { return url('admin/' . $path); }
}

if (!function_exists('_l')) {
    function _l($line = '', $label = '', ...$args) {
        $translated = __($line);
        if ($label !== '') {
            $translated .= ' ' . $label;
        }
        return $translated;
    }
}

if (!function_exists('init_head')) {
    function init_head($title = '', $meta = []) { return ''; }
}

if (!function_exists('init_tail')) {
    function init_tail($output = '', $footer_data = []) { return ''; }
}

if (!function_exists('html_escape')) {
    function html_escape($var = '') { return htmlspecialchars($var ?? '', ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('to_sql_date')) {
    function to_sql_date($date = '', $format = 'Y-m-d') {
        if (empty($date)) return date($format);
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : $date;
    }
}

if (!function_exists('_d')) {
    function _d($date = '', $format = '') {
        if (empty($date)) return '';
        $fmt = $format ?: 'd/m/Y';
        $ts = is_numeric($date) ? $date : strtotime($date);
        return $ts ? date($fmt, $ts) : $date;
    }
}

if (!function_exists('_dt')) {
    function _dt($datetime = '', $format = '') {
        if (empty($datetime)) return '';
        $fmt = $format ?: 'd/m/Y H:i';
        $ts = is_numeric($datetime) ? $datetime : strtotime($datetime);
        return $ts ? date($fmt, $ts) : $datetime;
    }
}

if (!function_exists('new_explode')) {
    function new_explode($delimiter = ',', $str = '', $limit = 0) {
        $limit = (int)$limit;
        if ($limit <= 0) {
            return explode($delimiter, $str);
        }
        return explode($delimiter, $str, $limit);
    }
}

if (!function_exists('new_str_replace')) {
    function new_str_replace($search = '', $replace = '', $subject = '') {
        if (is_array($search) && is_array($replace)) {
            return str_replace($search, $replace, $subject);
        }
        if (is_array($subject)) {
            return array_map(function($s) use ($search, $replace) { return str_replace($search, $replace, $s); }, $subject);
        }
        return str_replace($search, $replace, $subject);
    }
}

if (!function_exists('new_strlen')) {
    function new_strlen($str = '') { return mb_strlen((string)$str, 'UTF-8'); }
}

if (!function_exists('new_html_entity_decode')) {
    function new_html_entity_decode($val = '') { return html_entity_decode((string)$val, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('render_datatable')) {
    function render_datatable($headings = [], $class = '', $additional_classes = [], $table_attributes = []) {
        $attrStr = '';
        if (is_array($table_attributes)) {
            foreach ($table_attributes as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        $classStr = is_array($additional_classes) ? implode(' ', $additional_classes) : $additional_classes;
        $html = '<table class="table table-striped table-bordered dt-table ' . $class . ' ' . $classStr . '" ' . $attrStr . '>';
        $html .= '<thead><tr>';
        foreach ($headings as $heading) {
            $html .= '<th>' . _l($heading) . '</th>';
        }
        $html .= '</tr></thead><tbody></tbody></table>';
        return $html;
    }
}

if (!function_exists('render_input')) {
    function render_input($name = '', $label = '', $value = '', $type = 'text', $input_attrs = [], $form_group_class = '', $input_class = '', $key = '') {
        if (is_array($form_group_class)) $form_group_class = implode(' ', $form_group_class);
        if (is_array($input_class)) $input_class = implode(' ', $input_class);
        $attrStr = '';
        if (is_array($input_attrs)) {
            foreach ($input_attrs as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group ' . $form_group_class . '">' . $labelHtml .
            '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="form-control ' . $input_class . '" ' . $attrStr . ' /></div>';
    }
}

if (!function_exists('render_select')) {
    function render_select($name = '', $options = [], $option_fields = [], $label = '', $selected = '', $select_attrs = [], $form_group_class = '', $select_class = '', $key = '', $multiple = false) {
        if (is_array($form_group_class)) $form_group_class = implode(' ', $form_group_class);
        if (is_array($select_class)) $select_class = implode(' ', $select_class);
        $attrStr = '';
        if (is_array($select_attrs)) {
            foreach ($select_attrs as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        if ($multiple) $attrStr .= ' multiple';

        $optionsHtml = '';
        $valKey = $option_fields[0] ?? 'id';
        $labelKey = $option_fields[1] ?? 'name';
        $selectedArr = is_array($selected) ? $selected : [$selected];

        if (is_array($options) || $options instanceof \Illuminate\Support\Collection) {
            foreach ($options as $opt) {
                $opt = (array)$opt;
                $v = $opt[$valKey] ?? '';
                $lbl = $opt[$labelKey] ?? '';
                $sel = in_array($v, $selectedArr) ? 'selected' : '';
                $optionsHtml .= '<option value="' . htmlspecialchars($v) . '" ' . $sel . '>' . htmlspecialchars($lbl) . '</option>';
            }
        }
        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group ' . $form_group_class . '">' . $labelHtml .
            '<select id="' . $name . '" name="' . $name . '" class="form-control ' . $select_class . '" ' . $attrStr . '>' . $optionsHtml . '</select></div>';
    }
}

if (!function_exists('render_date_input')) {
    function render_date_input($name = '', $label = '', $value = '', $input_attrs = [], $form_group_class = '', $input_class = '', $key = '') {
        return render_input($name, $label, $value, 'date', $input_attrs, $form_group_class, $input_class, $key);
    }
}

if (!function_exists('render_textarea')) {
    function render_textarea($name = '', $label = '', $value = '', $textarea_attrs = [], $form_group_class = '', $textarea_class = '', $key = '') {
        $attrStr = '';
        if (is_array($textarea_attrs)) {
            foreach ($textarea_attrs as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group ' . $form_group_class . '">' . $labelHtml .
            '<textarea id="' . $name . '" name="' . $name . '" class="form-control ' . $textarea_class . '" ' . $attrStr . '>' . htmlspecialchars($value) . '</textarea></div>';
    }
}

if (!function_exists('form_open')) {
    function form_open($action = '', $attributes = []) {
        $attrStr = '';
        if (is_array($attributes)) {
            foreach ($attributes as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        return '<form action="' . url($action) . '"' . $attrStr . '>' . csrf_field();
    }
}

if (!function_exists('form_open_multipart')) {
    function form_open_multipart($action = '', $attributes = []) {
        $attributes['enctype'] = 'multipart/form-data';
        return form_open($action, $attributes);
    }
}

if (!function_exists('form_hidden')) {
    function form_hidden($name = '', $value = '') {
        if (is_array($name)) {
            $out = '';
            foreach ($name as $k => $v) {
                $out .= '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars((string)$v) . '" />';
            }
            return $out;
        }
        return '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars((string)$value) . '" />';
    }
}

if (!function_exists('form_close')) {
    function form_close() { return '</form>'; }
}

if (!function_exists('data_tables_init')) {
    function data_tables_init($aColumns = [], $sIndexColumn = '', $sTable = '', $join = [], $where = [], $custom_select = []) {
        $selectCols = array_merge($aColumns, $custom_select);
        $colStr = implode(', ', $selectCols);
        $sql = "SELECT {$colStr} FROM {$sTable}";
        if (!empty($join)) {
            $sql .= ' ' . implode(' ', $join);
        }
        if (!empty($where)) {
            $whereStr = implode(' ', $where);
            $whereStr = preg_replace('/^\s*AND\s+/', 'WHERE ', $whereStr);
            $sql .= ' ' . $whereStr;
        }
        try {
            $rResult = DB::select($sql);
            $rResult = array_map(function($r) { return (array)$r; }, $rResult);
        } catch (\Exception $e) {
            $rResult = [];
        }
        $output = [
            'sEcho' => intval(request('draw', 1)),
            'iTotalRecords' => count($rResult),
            'iTotalDisplayRecords' => count($rResult),
            'aaData' => [],
        ];
        return ['output' => $output, 'rResult' => $rResult];
    }
}

if (!function_exists('icon_btn')) {
    function icon_btn($href = '#', $icon = 'fa fa-edit', $class = 'btn-default', $attributes = []) {
        $attrStr = '';
        foreach ($attributes as $k => $v) {
            $attrStr .= " {$k}=\"{$v}\"";
        }
        return '<a href="' . $href . '" class="btn ' . $class . '"' . $attrStr . '><i class="' . $icon . '"></i></a>';
    }
}

if (!function_exists('show_404')) {
    function show_404($page = '', $log_error = true) {
        http_response_code(404);
        echo '<h1>404 Page Not Found</h1>';
        exit;
    }
}

if (!function_exists('module_dir_path')) {
    function module_dir_path($module_name = '') {
        $alias = str_replace('_', '-', $module_name);
        return base_path("Modules/{$alias}/");
    }
}

if (!function_exists('app_pdf')) {
    function app_pdf() {
        return null;
    }
}

if (!function_exists('get_base_currency')) {
    function get_base_currency() {
        try {
            $currencies = DB::table('currencies')->where('is_default', 1)->first();
            if (!$currencies) $currencies = DB::table('currencies')->first();
            return $currencies;
        } catch (\Exception $e) {
            return (object)['id' => 1, 'symbol' => '$', 'name' => 'USD', 'thousandseparator' => ',', 'decimalseparator' => '.'];
        }
    }
}

if (!function_exists('get_currency')) {
    function get_currency($id = 0) {
        try {
            return DB::table('currencies')->where('id', $id)->first();
        } catch (\Exception $e) {
            return (object)['id' => $id, 'symbol' => '$', 'name' => 'USD'];
        }
    }
}

if (!function_exists('add_option')) {
    function add_option($name, $value = '', $autoload = true) {
        $t = 'settings';
        if (!Schema::hasTable($t)) {
            DB::statement("CREATE TABLE IF NOT EXISTS `{$t}` (
                `settingid` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `settingname` VARCHAR(200) NOT NULL,
                `settingvalue` LONGTEXT,
                `autoload` TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        $exists = DB::table($t)->where('settingname', $name)->first();
        if (!$exists) {
            DB::table($t)->insert([
                'settingname' => $name,
                'settingvalue' => (string)$value,
                'autoload' => $autoload ? 1 : 0,
            ]);
        }
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($name, $default = false) {
        $t = 'settings';
        if (!Schema::hasTable($t)) { return $default; }
        $row = DB::table($t)->where('settingname', $name)->first();
        return $row ? $row->settingvalue : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($name, $value) {
        $t = 'settings';
        if (!Schema::hasTable($t)) { return add_option($name, $value); }
        $exists = DB::table($t)->where('settingname', $name)->first();
        if ($exists) {
            DB::table($t)->where('settingname', $name)->update(['settingvalue' => (string)$value]);
        } else {
            return add_option($name, $value);
        }
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($name) {
        $t = 'settings';
        if (!Schema::hasTable($t)) { return false; }
        DB::table($t)->where('settingname', $name)->delete();
        return true;
    }
}

// ─── AdminController ───────────────────────────────────────────────────────────
if (!class_exists('AdminController')) {
    class AdminController {
        public $db;
        public $load;
        public $input;
        public $session;
        public $uri;
        public $hooks;
        public $data = [];
        public $hr_payroll_model;
        public $currencies_model;
        public $staff_model;

        public function __construct() {
            $ci = get_instance();
            $this->db = $ci->db;
            $this->load = $ci->load;
            $this->input = $ci->input;
            $this->session = $ci->session;
            $this->uri = $ci->uri;
            $this->hooks = $ci->hooks;
            if (method_exists($this->load, 'setController')) {
                $this->load->setController($this);
            }
        }
    }
}
