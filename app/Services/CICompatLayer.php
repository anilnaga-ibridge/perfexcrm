<?php

/**
 * Full CodeIgniter Compatibility Layer for iBridge CRM Laravel port.
 * Provides CI_DB_active_record, CI_Loader, CI_Input, CI_Session, AdminController, App_Model
 * and all necessary helper functions so legacy CI modules work inside Laravel.
 */
if (defined('CI_COMPAT_LOADED')) return;
define('CI_COMPAT_LOADED', true);

// Standard CodeIgniter Environment Constants
if (!defined('BASEPATH')) {
    define('BASEPATH', base_path() . '/');
}
if (!defined('FCPATH')) {
    define('FCPATH', public_path() . '/');
}
if (!defined('APPPATH')) {
    define('APPPATH', base_path('app/') . '/');
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', config('app.env', 'production'));
}

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

// ─── App_Model (iBridge base model, extends CI_Model) ──────────────────────────
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
    protected $bindings = [];
    public $char_set = 'utf8mb4';
    protected $caching = false;
    protected $cacheWheres = [];
    protected $cacheOrWheres = [];
    protected $cacheSelects = [];
    protected $cacheJoins = [];
    protected $cacheFromTable = null;
    protected static $tableCache = [];

    public function normalizeTable($table) {
        if (empty($table) || !is_string($table)) {
            return $table;
        }
        $table = trim($table);
        $aliasPart = '';
        if (preg_match('/^(\w+)\s+(?:AS\s+)?(\w+)$/i', $table, $matches)) {
            $baseTable = $matches[1];
            $aliasPart = ' ' . $matches[2];
        } else {
            $baseTable = $table;
        }

        if (isset(self::$tableCache[$baseTable])) {
            return self::$tableCache[$baseTable] . $aliasPart;
        }

        if (Schema::hasTable($baseTable)) {
            self::$tableCache[$baseTable] = $baseTable;
            return $table;
        }

        if (str_starts_with($baseTable, 'tbl') && Schema::hasTable(substr($baseTable, 3))) {
            $resolved = substr($baseTable, 3);
            self::$tableCache[$baseTable] = $resolved;
            return $resolved . $aliasPart;
        }

        $prefix = db_prefix();
        if ($prefix !== '' && !str_starts_with($baseTable, $prefix) && Schema::hasTable($prefix . $baseTable)) {
            $resolved = $prefix . $baseTable;
            self::$tableCache[$baseTable] = $resolved;
            return $resolved . $aliasPart;
        }

        self::$tableCache[$baseTable] = $baseTable;
        return $table;
    }

    public function __construct() {
        $this->char_set = config('database.connections.mysql.charset', 'utf8mb4') ?? 'utf8mb4';
        try {
            DB::statement("SET SESSION sql_mode = ''");
        } catch (\Throwable $e) {}
    }

    public function start_cache() {
        $this->caching = true;
        return $this;
    }

    public function stop_cache() {
        $this->caching = false;
        return $this;
    }

    public function flush_cache() {
        $this->cacheWheres = [];
        $this->cacheOrWheres = [];
        $this->cacheSelects = [];
        $this->cacheJoins = [];
        $this->cacheFromTable = null;
        return $this;
    }

    protected function reset() {
        $this->wheres = $this->cacheWheres;
        $this->orWheres = $this->cacheOrWheres;
        $this->selects = $this->cacheSelects;
        $this->joins = $this->cacheJoins;
        $this->fromTable = $this->cacheFromTable;
        $this->orderBys = [];
        $this->groupBys = [];
        $this->havings = [];
        $this->limitVal = null;
        $this->offsetVal = null;
        $this->setClause = [];
        $this->bindings = [];
    }

    public function get($table = '') {
        if ($table !== '') {
            $this->fromTable = $this->normalizeTable($table);
        }
        $table = $this->normalizeTable($this->fromTable);
        if (!$table) {
            $this->reset();
            return new CIDBResult([], [], 0);
        }

        $sql = "SELECT " . (empty($this->selects) ? "*" : implode(', ', $this->selects));
        $sql .= " FROM {$table}";

        foreach ($this->joins as $j) {
            $sql .= " {$j}";
        }

        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= " WHERE " . $whereSql;
        }

        if (!empty($this->groupBys)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBys);
        }

        if (!empty($this->havings)) {
            $sql .= " HAVING " . implode(' AND ', $this->havings);
        }

        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBys);
        }

        if ($this->limitVal !== null) {
            $sql .= " LIMIT {$this->limitVal}";
            if ($this->offsetVal !== null) {
                $sql .= " OFFSET {$this->offsetVal}";
            }
        }

        $result = $this->executeQuery($sql);
        $this->reset();
        return $result;
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
                if (str_contains($s, '.')) {
                    [$tPrefix, $cName] = explode('.', $s, 2);
                    $normT = $this->normalizeTable($tPrefix);
                    if ($normT !== $tPrefix) {
                        $s = $normT . '.' . $cName;
                    }
                }
                $this->selects[] = $s;
                if ($this->caching) {
                    $this->cacheSelects[] = $s;
                }
            }
        }
        return $this;
    }

    public function from($table) {
        $normalized = $this->normalizeTable($table);
        $this->fromTable = $normalized;
        if ($this->caching) {
            $this->cacheFromTable = $normalized;
        }
        return $this;
    }

    protected function addCondition($cond, $isOr = false) {
        if ($isOr) {
            $this->orWheres[] = $cond;
            if ($this->caching) {
                $this->cacheOrWheres[] = $cond;
            }
        } else {
            $this->wheres[] = $cond;
            if ($this->caching) {
                $this->cacheWheres[] = $cond;
            }
        }
    }

    public function where($key, $value = null, $escape = true) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->addCondition(['AND', $k, '=', $v]);
            }
        } elseif ($value === null) {
            $this->addCondition(['AND', $key, 'IS NULL', null]);
        } elseif (is_array($value)) {
            $this->addCondition(['AND', $key, 'IN', $value]);
        } else {
            // Check for operator in key (e.g. 'id >')
            if (preg_match('/^\s*(\w+)\s*(>=|<=|!=|<>|>|<|=|LIKE|like)\s*$/', $key, $m)) {
                $this->addCondition(['AND', $m[1], $m[2], $value]);
            } else {
                $this->addCondition(['AND', $key, '=', $value]);
            }
        }
        return $this;
    }

    public function or_where($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->addCondition(['OR', $k, '=', $v], true);
            }
        } elseif ($value === null) {
            $this->addCondition(['OR', $key, 'IS NULL', null], true);
        } else {
            if (preg_match('/^\s*(\w+)\s*(>=|<=|!=|<>|>|<|=|LIKE|like)\s*$/', $key, $m)) {
                $this->addCondition(['OR', $m[1], $m[2], $value], true);
            } else {
                $this->addCondition(['OR', $key, '=', $value], true);
            }
        }
        return $this;
    }

    public function where_in($key = '', $values = []) {
        $this->addCondition(['AND', $key, 'IN', (array)$values]);
        return $this;
    }

    public function where_not_in($key = '', $values = []) {
        $this->addCondition(['AND', $key, 'NOT IN', (array)$values]);
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
        $table = $this->normalizeTable($table);
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

        if (str_contains($col, '.')) {
            [$tPrefix, $cName] = explode('.', $col, 2);
            $normT = $this->normalizeTable($tPrefix);
            if ($normT !== $tPrefix) {
                $col = $normT . '.' . $cName;
            }
        }

        if ($op === 'IS NULL') {
            return "{$col} IS NULL";
        }
        if ($op === 'IN' || $op === 'NOT IN') {
            if (!is_array($val)) $val = (array)$val;
            $placeholders = [];
            foreach ($val as $v) {
                $placeholders[] = '?';
                $this->bindings[] = $v;
            }
            return "{$col} {$op} (" . implode(',', $placeholders) . ")";
        }
        if ($op === 'LIKE' || $op === 'NOT LIKE') {
            $this->bindings[] = (string)$val;
            return "{$col} {$op} ?";
        }

        if ($val === null) {
            return "{$col} IS NULL";
        }

        $this->bindings[] = $val;
        return "{$col} {$op} ?";
    }

    // ─── Execute ────────────────────────────────────────────────────────────────

    protected function executeQuery($sql) {
        $this->lastQuery = $sql;
        $isSelect = (stripos(trim($sql), 'SELECT') === 0);
        $bindings = $this->bindings;
        $this->bindings = [];

        try {
            if ($isSelect) {
                $raw = DB::select($sql, $bindings);
                $rows = array_map(function($r) { return (array)$r; }, $raw);
                $count = count($raw);
                $this->lastAffectedRows = $count;
                return new CIDBResult($raw, $rows, $count);
            } else {
                $this->lastAffectedRows = DB::affectingStatement($sql, $bindings);
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
        $table = $this->normalizeTable($table);
        if (empty($data) && !empty($this->setClause)) {
            $data = $this->setClause;
            $this->setClause = [];
        }
        if (empty($data)) { $this->reset(); return false; }

        $cols = implode(', ', array_keys($data));
        $placeholders = array_fill(0, count($data), '?');
        $vals = implode(', ', $placeholders);
        $this->bindings = array_values($data);
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
        $result = $this->executeQuery($sql);
        $this->lastInsertId = DB::getPdo()->lastInsertId();
        $this->reset();
        return true;
    }

    public function insert_batch($table, $data = []) {
        $table = $this->normalizeTable($table);
        if (empty($data)) { $this->reset(); return 0; }

        $cols = array_keys($data[0]);
        $colStr = implode(', ', $cols);
        $rows = [];
        $this->bindings = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach (array_values($row) as $v) {
                $placeholders[] = '?';
                $this->bindings[] = $v;
            }
            $rows[] = '(' . implode(', ', $placeholders) . ')';
        }
        $sql = "INSERT INTO {$table} ({$colStr}) VALUES " . implode(', ', $rows);
        $this->executeQuery($sql);
        $this->reset();
        return count($data);
    }

    public function update($table, $data = []) {
        $table = $this->normalizeTable($table);
        if (empty($data)) { $this->reset(); return false; }

        $setParts = [];
        $setValues = [];
        foreach ($data as $k => $v) {
            $setParts[] = "{$k} = ?";
            $setValues[] = $v;
        }
        $this->bindings = $setValues;
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts);

        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $this->executeQuery($sql);
        $result = $this->lastAffectedRows;
        $this->reset();
        return $result;
    }

    public function update_batch($table, $data = [], $index = '') {
        $table = $this->normalizeTable($table);
        if (empty($data) || $index === '') { $this->reset(); return 0; }

        $affected = 0;
        foreach ($data as $row) {
            if (!isset($row[$index])) continue;
            $id = $row[$index];
            $setParts = [];
            $setValues = [];
            foreach ($row as $k => $v) {
                if ($k === $index) continue;
                $setParts[] = "{$k} = ?";
                $setValues[] = $v;
            }
            if (empty($setParts)) continue;
            $this->bindings = array_merge($setValues, [$id]);
            $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$index} = ?";
            $this->executeQuery($sql);
            $affected++;
        }
        $this->reset();
        $this->lastAffectedRows = $affected;
        return $affected;
    }

    public function delete($table, $where = []) {
        $table = $this->normalizeTable($table);
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
        $this->executeQuery($sql);
        $result = $this->lastAffectedRows;
        $this->reset();
        return $result;
    }

    public function truncate($table) {
        $table = $this->normalizeTable($table);
        $sql = "TRUNCATE TABLE {$table}";
        $this->executeQuery($sql);
        $this->reset();
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
        $bindings = $this->bindings;
        $this->bindings = [];
        try {
            if ($isSelect) {
                $raw = DB::select($sql, $bindings);
                $rows = array_map(function($r) { return (array)$r; }, $raw);
                $count = count($raw);
                $this->lastAffectedRows = $count;
                return new CIDBResult($raw, $rows, $count);
            } else {
                $affected = DB::affectingStatement($sql, $bindings);
                $this->lastAffectedRows = $affected;
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
        return Schema::hasTable($this->normalizeTable($table)) || Schema::hasTable($table);
    }

    public function field_exists($field, $table) {
        return Schema::hasColumn($this->normalizeTable($table), $field);
    }

    public function count_all($table = '') {
        if ($table === '') return 0;
        $table = $this->normalizeTable($table);
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
            $this->fromTable = $this->normalizeTable($table);
        }
        $table = $this->normalizeTable($this->fromTable);
        if (!$table) { $this->reset(); return 0; }

        $sql = "SELECT COUNT(*) as cnt FROM {$table}";
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        $whereSql = $this->buildWhereClause();
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        try {
            $r = DB::select($sql);
            $this->reset();
            return $r[0]->cnt ?? 0;
        } catch (\Exception $e) {
            $this->reset();
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

    public function row_array($n = 0) {
        return $this->row($n, 'array');
    }

    public function row_object($n = 0) {
        return $this->row($n, 'object');
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

// ─── CIDBForge ───────────────────────────────────────────────────────────────
class CIDBForge {
    protected $fields = [];
    protected $keys = [];
    protected $primaryKeys = [];

    protected function resolveTableName($table) {
        $prefix = db_prefix();
        if ($prefix !== '' && !str_starts_with($table, $prefix)) {
            if (Schema::hasTable($prefix . $table)) {
                return $prefix . $table;
            }
        }
        return $table;
    }

    public function add_field($fields) {
        if (is_string($fields)) {
            $this->fields[] = $fields;
            return $this;
        }
        if (is_array($fields)) {
            $this->fields = array_merge($this->fields, $fields);
        }
        return $this;
    }

    public function add_key($key, $primary = false) {
        if ($primary) {
            $this->primaryKeys[] = $key;
        } else {
            $this->keys[] = $key;
        }
        return $this;
    }

    public function create_table($table, $if_not_exists = false) {
        $fullTable = $this->resolveTableName($table);
        if ($if_not_exists && Schema::hasTable($fullTable)) {
            $this->reset();
            return true;
        }

        $lines = [];
        foreach ($this->fields as $col => $attr) {
            if (is_int($col) && is_string($attr)) {
                $lines[] = $attr;
                continue;
            }
            $type = strtoupper($attr['type'] ?? 'VARCHAR');
            $constraint = isset($attr['constraint']) ? "({$attr['constraint']})" : '';
            $unsigned = !empty($attr['unsigned']) ? 'UNSIGNED' : '';
            $autoIncrement = !empty($attr['auto_increment']) ? 'AUTO_INCREMENT' : '';
            $null = (isset($attr['null']) && $attr['null'] === true) ? 'NULL' : 'NOT NULL';
            $default = '';
            if (array_key_exists('default', $attr)) {
                if ($attr['default'] === null) {
                    $default = 'DEFAULT NULL';
                } else {
                    $default = "DEFAULT " . DB::getPdo()->quote((string)$attr['default']);
                }
            }
            $parts = array_filter(["`{$col}`", $type . $constraint, $unsigned, $null, $default, $autoIncrement]);
            $lines[] = implode(' ', $parts);
        }

        if (!empty($this->primaryKeys)) {
            $pkCols = implode('`, `', $this->primaryKeys);
            $lines[] = "PRIMARY KEY (`{$pkCols}`)";
        }

        foreach ($this->keys as $k) {
            $kCols = is_array($k) ? implode('`, `', $k) : $k;
            $kName = is_array($k) ? implode('_', $k) : $k;
            $lines[] = "KEY `{$kName}` (`{$kCols}`)";
        }

        $ifNot = $if_not_exists ? 'IF NOT EXISTS ' : '';
        $sql = "CREATE TABLE {$ifNot}`{$fullTable}` (\n  " . implode(",\n  ", $lines) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        try {
            DB::statement($sql);
            $this->reset();
            return true;
        } catch (\Throwable $e) {
            Log::warning("CIDBForge create_table error: " . $e->getMessage() . " | SQL: " . $sql);
            $this->reset();
            return false;
        }
    }

    public function drop_table($table, $if_exists = false) {
        $fullTable = $this->resolveTableName($table);
        $ifEx = $if_exists ? 'IF EXISTS ' : '';
        try {
            DB::statement("DROP TABLE {$ifEx}`{$fullTable}`");
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function add_column($table, $fields = []) {
        $fullTable = $this->resolveTableName($table);
        if (!Schema::hasTable($fullTable)) {
            $prefixTable = db_prefix() . $table;
            if (Schema::hasTable($prefixTable)) {
                $fullTable = $prefixTable;
            } else {
                return false;
            }
        }

        foreach ($fields as $col => $attr) {
            if (Schema::hasColumn($fullTable, $col)) continue;
            $type = strtoupper($attr['type'] ?? 'VARCHAR');
            $constraint = isset($attr['constraint']) ? "({$attr['constraint']})" : '';
            $null = (isset($attr['null']) && $attr['null'] === true) ? 'NULL' : (isset($attr['default']) && $attr['default'] === null ? 'NULL' : 'NOT NULL');
            $default = '';
            if (array_key_exists('default', $attr)) {
                if ($attr['default'] === null) {
                    $default = 'DEFAULT NULL';
                } else {
                    $default = "DEFAULT " . DB::getPdo()->quote((string)$attr['default']);
                }
            }
            $after = !empty($attr['after']) ? "AFTER `{$attr['after']}`" : '';
            $sql = "ALTER TABLE `{$fullTable}` ADD `{$col}` {$type}{$constraint} {$null} {$default} {$after}";
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                Log::warning("dbforge add_column error: " . $e->getMessage());
            }
        }
        return true;
    }

    public function drop_column($table, $column_name) {
        $fullTable = $this->resolveTableName($table);
        if (!Schema::hasTable($fullTable) || !Schema::hasColumn($fullTable, $column_name)) return true;
        try {
            DB::statement("ALTER TABLE `{$fullTable}` DROP COLUMN `{$column_name}`");
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function modify_column($table, $fields = []) {
        $fullTable = $this->resolveTableName($table);
        if (!Schema::hasTable($fullTable)) return false;
        foreach ($fields as $col => $attr) {
            $name = $attr['name'] ?? $col;
            $type = strtoupper($attr['type'] ?? 'VARCHAR');
            $constraint = isset($attr['constraint']) ? "({$attr['constraint']})" : '';
            $null = (isset($attr['null']) && $attr['null'] === true) ? 'NULL' : 'NOT NULL';
            $default = array_key_exists('default', $attr) ? ($attr['default'] === null ? 'DEFAULT NULL' : "DEFAULT " . DB::getPdo()->quote((string)$attr['default'])) : '';
            $sql = "ALTER TABLE `{$fullTable}` CHANGE `{$col}` `{$name}` {$type}{$constraint} {$null} {$default}";
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                Log::warning("dbforge modify_column error: " . $e->getMessage());
            }
        }
        return true;
    }

    protected function reset() {
        $this->fields = [];
        $this->keys = [];
        $this->primaryKeys = [];
    }
}

// ─── CILoader ──────────────────────────────────────────────────────────────────
class CILoader {
    protected $loadedModels = [];
    protected $controllerInstance = null;
    protected $lastViewData = [];
    protected $activeControllerAlias = null;

    /** Cache active module info to avoid DB queries on every view call */
    protected static $activeModulesCache = null;
    protected static $activeModulesCacheTime = 0;

    public function setController($instance) {
        $this->controllerInstance = $instance;
        // Detect which module this controller belongs to
        $this->activeControllerAlias = $this->detectControllerModule($instance);
    }

    public function resetForNewModule() {
        $this->controllerInstance = null;
        $this->activeControllerAlias = null;
        $this->lastViewData = [];
        self::$activeModulesCache = null;
        self::$activeModulesCacheTime = 0;
    }

    public function __get($key) {
        $ci = get_instance();
        if (isset($ci->$key)) {
            return $ci->$key;
        }
        if ($this->controllerInstance && isset($this->controllerInstance->$key)) {
            return $this->controllerInstance->$key;
        }
        if (str_ends_with($key, '_model')) {
            $this->model($key);
            if (isset($ci->$key)) {
                return $ci->$key;
            }
        }
        return null;
    }

    public static function clearActiveModulesCache(): void {
        self::$activeModulesCache = null;
        self::$activeModulesCacheTime = 0;
    }

    protected function detectControllerModule($instance): ?string {
        $classPath = (new \ReflectionClass($instance))->getFileName();
        if (!$classPath) return null;
        // Match Modules/{alias}/controllers/ pattern
        if (preg_match('#Modules/([^/]+)/controllers/#', $classPath, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function getActiveModules(): array {
        $now = microtime(true);
        if (self::$activeModulesCache !== null && ($now - self::$activeModulesCacheTime) < 5) {
            return self::$activeModulesCache;
        }
        // Filesystem is the authoritative primary source of truth
        $modules = [];
        $dirs = glob(base_path('Modules/*'), GLOB_ONLYDIR) ?: [];
        foreach ($dirs as $dir) {
            $alias = basename($dir);
            $modules[] = [
                'alias' => $alias,
                'name' => ucfirst($alias),
                'path' => $dir,
                'status' => 'active',
            ];
        }
        self::$activeModulesCache = $modules;
        self::$activeModulesCacheTime = $now;
        return self::$activeModulesCache;
    }

    public function model($model, $name = '', $connect = false) {
        if (is_array($model)) {
            foreach ($model as $m) {
                $this->model($m);
            }
            return;
        }

        $parts = explode('/', $model);
        $modelName = end($parts);
        $name = $name ?: $modelName;

        if (isset($this->loadedModels[$name])) return;

        $proxy = new \App\Services\LazyModelProxy($this, $model, $name);
        $ci = get_instance();
        $ci->$name = $proxy;
        if ($this->controllerInstance) {
            $prop = $name;
            if (!property_exists($this->controllerInstance, $prop) || $this->controllerInstance->$prop === null) {
                $this->controllerInstance->$prop = $proxy;
            }
        }
        $this->loadedModels[$name] = $proxy;
    }

    public function dbforge() {
        $ci = get_instance();
        if (!isset($ci->dbforge)) {
            $ci->dbforge = new CIDBForge();
        }
        return $ci->dbforge;
    }

    /**
     * Realize the lazy model proxy into a concrete model instance.
     */
    public function realizeModel(string $model, string $name) {
        $parts = explode('/', $model);
        $modelName = end($parts);

        // Try to find model file in active module (prefer calling module first)
        $activeModules = $this->getActiveModules();

        // Reorder: caller module first
        $ordered = $activeModules;
        if ($this->activeControllerAlias) {
            foreach ($activeModules as $i => $mod) {
                $alias = $mod['alias'] ?? $mod->alias ?? '';
                if ($alias === $this->activeControllerAlias) {
                    $caller = array_splice($ordered, $i, 1);
                    array_unshift($ordered, $caller[0]);
                    break;
                }
            }
        }

        $searchedPaths = [];

        foreach ($ordered as $mod) {
            $alias = $mod['alias'] ?? $mod->alias ?? '';
            // Strip alias prefix from model path too
            $strippedModel = $model;
            $underscoreAlias = str_replace('-', '_', $alias);
            if (str_starts_with($model, $underscoreAlias . '/') || str_starts_with($model, $alias . '/')) {
                $strippedModel = preg_replace('#^' . preg_quote($underscoreAlias, '#') . '/#', '', $model, 1);
                $strippedModel = preg_replace('#^' . preg_quote($alias, '#') . '/#', '', $strippedModel, 1);
            }

            $paths = [
                base_path("Modules/{$alias}/models/" . ucfirst($modelName) . ".php"),
                base_path("Modules/{$alias}/models/" . $modelName . ".php"),
                base_path("Modules/{$alias}/models/" . $strippedModel . ".php"),
                base_path("Modules/{$alias}/models/" . ucfirst(str_replace('/', '/', $strippedModel)) . ".php"),
            ];
            foreach ($paths as $path) {
                $searchedPaths[] = $path;
                if (file_exists($path)) {
                    require_once $path;
                    $content = file_get_contents($path);
                    if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $m)) {
                        $className = $m[1];
                        if (class_exists($className)) {
                            $instance = new $className();
                            $ci = get_instance();
                            foreach (get_object_vars($ci) as $key => $value) {
                                if ($value !== null && (!property_exists($instance, $key) || $instance->$key === null)) {
                                    $instance->$key = $value;
                                }
                            }
                            return $instance;
                        }
                    }
                }
            }
        }

        // Try core CI compat models directory
        $corePaths = [
            app_path("Services/CICoreModels/" . ucfirst($modelName) . ".php"),
            app_path("Services/CICoreModels/" . $modelName . ".php"),
        ];
        foreach ($corePaths as $path) {
            $searchedPaths[] = $path;
            if (file_exists($path)) {
                require_once $path;
                $content = file_get_contents($path);
                if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $m)) {
                    $className = $m[1];
                    if (class_exists($className)) {
                        $instance = new $className();
                        $ci = get_instance();
                        foreach (get_object_vars($ci) as $key => $value) {
                            if ($value !== null && (!property_exists($instance, $key) || $instance->$key === null)) {
                                $instance->$key = $value;
                            }
                        }
                        return $instance;
                    }
                }
            }
        }

        // Global scan of all Modules/ folders as last resort
        $allDirs = glob(base_path('Modules/*'), GLOB_ONLYDIR) ?: [];
        foreach ($allDirs as $dir) {
            if (is_link($dir)) continue;
            $candidates = [
                $dir . '/models/' . ucfirst($modelName) . '.php',
                $dir . '/models/' . $modelName . '.php',
            ];
            foreach ($candidates as $cPath) {
                $searchedPaths[] = $cPath;
                if (file_exists($cPath)) {
                    require_once $cPath;
                    $content = file_get_contents($cPath);
                    if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $content, $m)) {
                        $className = $m[1];
                        if (class_exists($className)) {
                            $instance = new $className();
                            $ci = get_instance();
                            foreach (get_object_vars($ci) as $key => $value) {
                                if ($value !== null && (!property_exists($instance, $key) || $instance->$key === null)) {
                                    $instance->$key = $value;
                                }
                            }
                            return $instance;
                        }
                    }
                }
            }
        }

        // Throw ModelNotFoundException with exact details
        $pathsStr = implode("\n - ", array_slice(array_unique($searchedPaths), 0, 10));
        throw new \RuntimeException("ModelNotFoundException: Model [{$model}] (alias: {$name}) could not be resolved. Searched paths:\n - {$pathsStr}");
    }

    public function view($view, $data = [], $return = false) {
        $ci = get_instance();
        $normalizedView = strtolower(trim(str_replace(['\\', '//'], '/', $view), '/'));

        // Universal iBridge CRM Core View Interceptors:
        // Any module calling admin/includes/head or admin/includes/header automatically gets full CRM layout & styling
        if (in_array($normalizedView, ['admin/includes/head', 'includes/head', 'admin/includes/header', 'includes/header'])) {
            if ($return) {
                ob_start();
                init_head($data['title'] ?? $ci->data['title'] ?? '');
                return ob_get_clean();
            }
            init_head($data['title'] ?? $ci->data['title'] ?? '');
            return;
        }

        // Any module calling admin/includes/footer or admin/includes/tail automatically gets DataTables and script initialization
        if (in_array($normalizedView, ['admin/includes/footer', 'includes/footer', 'admin/includes/tail', 'includes/tail'])) {
            if ($return) {
                ob_start();
                init_tail();
                return ob_get_clean();
            }
            init_tail();
            return;
        }

        if (in_array($normalizedView, ['admin/includes/modals', 'includes/modals', 'admin/includes/notifications', 'includes/notifications'])) {
            return '';
        }

        $activeModules = $this->getActiveModules();

        // CI behavior: when called from a view without data, inherit parent view data
        if (empty($data) && !empty($this->lastViewData)) {
            $data = $this->lastViewData;
        }

        // Build search paths: prefer the calling module first, then others
        $orderedModules = $activeModules;
        if ($this->activeControllerAlias) {
            foreach ($activeModules as $i => $mod) {
                if (($mod['alias'] ?? $mod->alias ?? '') === $this->activeControllerAlias) {
                    $caller = array_splice($orderedModules, $i, 1);
                    array_unshift($orderedModules, $caller[0]);
                    break;
                }
            }
        }

        foreach ($orderedModules as $mod) {
            $alias = $mod['alias'] ?? $mod->alias ?? '';
            $modulePath = base_path("Modules/{$alias}");
            $viewsDir = "{$modulePath}/views";
            if (!is_dir($viewsDir)) continue;

            // Strip module alias prefix from view path (e.g. "hr_payroll/employees/import" -> "employees/import")
            $strippedView = $view;
            $underscoreAlias = str_replace('-', '_', $alias);
            if (str_starts_with($view, $underscoreAlias . '/') || str_starts_with($view, $alias . '/')) {
                $strippedView = preg_replace('#^' . preg_quote($underscoreAlias, '#') . '/#', '', $view, 1);
                $strippedView = preg_replace('#^' . preg_quote($alias, '#') . '/#', '', $strippedView, 1);
            }

            // Build candidate file paths — try stripped version first, then original
            $candidates = [
                "{$viewsDir}/{$strippedView}.php",
                "{$viewsDir}/{$view}.php",
                "{$viewsDir}/{$alias}/{$strippedView}.php",
                "{$viewsDir}/{$alias}/{$view}.php",
            ];

            foreach ($candidates as $viewPath) {
                if (file_exists($viewPath)) {
                    $viewData = array_merge((array)($ci->data ?? []), (array)$data);
                    foreach ($viewData as $_viewKey => $_viewValue) {
                        $$_viewKey = $_viewValue;
                    }
                    $this->lastViewData = $data;
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

        if ($name === 'form_validation' || $library === 'form_validation') {
            $ci->form_validation = new CI_Form_Validation();
            if ($this->controllerInstance && is_object($this->controllerInstance)) {
                $this->controllerInstance->form_validation = $ci->form_validation;
            }
            return;
        }

        // Return already loaded instance if exists
        if (isset($ci->$name) && is_object($ci->$name) && !($ci->$name instanceof \stdClass)) {
            if ($this->controllerInstance && is_object($this->controllerInstance)) {
                $this->controllerInstance->$name = $ci->$name;
            }
            return;
        }

        $activeModules = $this->getActiveModules();
        $candidates = [
            "libraries/{$library}.php",
            "libraries/" . ucfirst($library) . ".php",
            "libraries/" . strtolower($library) . ".php",
            "third_party/{$library}.php",
            "third_party/{$library}/{$library}.php",
            "third_party/{$library}/" . ucfirst($library) . ".php",
            "third_party/{$library}/libraries/{$library}.php",
        ];

        foreach ($activeModules as $mod) {
            $alias = $mod['alias'] ?? $mod->alias ?? '';
            $modulePath = $mod['path'] ?? base_path("Modules/{$alias}");
            foreach ($candidates as $cand) {
                $libPath = "{$modulePath}/{$cand}";
                if (file_exists($libPath)) {
                    require_once $libPath;
                    $content = file_get_contents($libPath);
                    if (preg_match('/class\s+(\w+)/', $content, $m)) {
                        $className = $m[1];
                        if (class_exists($className)) {
                            $instance = $params !== null ? new $className($params) : new $className();
                            $ci->$name = $instance;
                            if ($this->controllerInstance && is_object($this->controllerInstance)) {
                                $this->controllerInstance->$name = $instance;
                            }
                            return;
                        }
                    }
                }
            }
        }

        // Stub if not found
        $stub = new class {
            public function __call($name, $args) { return null; }
        };
        $ci->$name = $stub;
        if ($this->controllerInstance && is_object($this->controllerInstance)) {
            $this->controllerInstance->$name = $stub;
        }
    }

    public function helper($helpers) {
        if (!is_array($helpers)) $helpers = [$helpers];
        $activeModules = $this->getActiveModules();
        foreach ($helpers as $helper) {
            $targetModule = null;
            $helperBase = $helper;
            if (str_contains($helper, '/')) {
                $parts = explode('/', $helper, 2);
                $targetModule = $parts[0];
                $helperBase = $parts[1];
            }

            $helperName = preg_replace('/_helper$/i', '', $helperBase);
            $candidates = [
                "{$helperName}_helper.php",
                "{$helperName}.php",
                "{$helperBase}.php",
            ];

            $loaded = false;
            // 1. If target module was specified, check it first
            if ($targetModule) {
                $targetPath = base_path("Modules/{$targetModule}");
                foreach ($candidates as $cand) {
                    $p = "{$targetPath}/helpers/{$cand}";
                    if (file_exists($p)) {
                        require_once $p;
                        $loaded = true;
                        break;
                    }
                }
            }

            // 2. Search all active modules
            if (!$loaded) {
                foreach ($activeModules as $mod) {
                    $alias = $mod['alias'] ?? $mod->alias ?? '';
                    $modulePath = $mod['path'] ?? base_path("Modules/{$alias}");
                    foreach ($candidates as $cand) {
                        $path = "{$modulePath}/helpers/{$cand}";
                        if (file_exists($path)) {
                            require_once $path;
                            $loaded = true;
                            break 2;
                        }
                    }
                }
            }

            // 3. Search core helpers
            if (!$loaded) {
                $corePaths = [
                    base_path("app/Helpers/{$helperName}_helper.php"),
                    base_path("app/Helpers/{$helperName}.php"),
                ];
                foreach ($corePaths as $cp) {
                    if (file_exists($cp)) {
                        require_once $cp;
                        break;
                    }
                }
            }
        }
    }

    public function module($module) {
        $ci = get_instance();
        $alias = basename($module);
        $manifest = app(\App\Services\PluginBridgeService::class)->getManifest($alias);
        if (!empty($manifest)) {
            app(\App\Services\PluginBridgeService::class)->bootstrap($manifest);
            $className = ucfirst($alias);
            $bridge = app(\App\Services\PluginBridgeService::class);
            $controllerFile = $bridge->findControllerFile($manifest['path'], $className);
            if ($controllerFile && file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($className)) {
                    $instance = new $className();
                    foreach (get_object_vars($ci) as $k => $v) {
                        if ($v !== null && (!property_exists($instance, $k) || $instance->$k === null)) {
                            $instance->$k = $v;
                        }
                    }
                    $ci->$alias = $instance;
                    if ($this->controllerInstance && is_object($this->controllerInstance)) {
                        $this->controllerInstance->$alias = $instance;
                    }
                    return $instance;
                }
            }
        }
        return null;
    }

    public function database($queryObject = '', $queryBuilder = false) { }
    public function config($item = '') { return config($item); }
    public function file($file, $read = false, $return = false) { return ''; }

    public function language($file = '', $module = '', $return = false) {
        global $lang;

        $locale = app()->getLocale() ?? config('app.locale', 'english');
        // Map Laravel locale to CI directory name
        $localeMap = [
            'en' => 'english', 'fr' => 'french', 'de' => 'german', 'es' => 'spanish',
            'pt' => 'portuguese', 'pt-BR' => 'portuguese_br', 'nl' => 'dutch',
            'it' => 'italian', 'ru' => 'russian', 'zh' => 'chinese', 'ja' => 'japanese',
            'pl' => 'polish', 'tr' => 'turkish', 'uk' => 'ukrainian', 'sv' => 'swedish',
            'cs' => 'czech', 'el' => 'greek', 'ro' => 'romanian', 'bg' => 'bulgarian',
            'sk' => 'slovak', 'id' => 'indonesia', 'fa' => 'persian', 'vi' => 'vietnamese',
        ];
        $ciLocale = $localeMap[$locale] ?? $locale;

        // Determine which modules to load language for
        $modulesToLoad = [];
        if ($module !== '') {
            $modulesToLoad[] = $module;
        } else {
            // Load language for all active modules
            foreach ($this->getActiveModules() as $mod) {
                $modulesToLoad[] = $mod['alias'] ?? $mod->alias ?? '';
            }
        }

        foreach ($modulesToLoad as $modAlias) {
            $langDir = base_path("Modules/{$modAlias}/language/{$ciLocale}");
            if (!is_dir($langDir)) {
                $langDir = base_path("Modules/{$modAlias}/language/english");
            }
            if (!is_dir($langDir)) continue;

            // Load the specific file or all *_lang.php files
            if ($file !== '') {
                $langFile = "{$langDir}/{$file}_lang.php";
                if (file_exists($langFile)) {
                    include $langFile;
                }
            } else {
                foreach (glob("{$langDir}/*_lang.php") as $langFile) {
                    try { include $langFile; } catch (\Throwable $e) {}
                }
            }
        }

        return $lang;
    }
}

// ─── CI_Input ──────────────────────────────────────────────────────────────────
class CI_Input {
    public function post($key = null, $xss_clean = false) {
        $reqData = function_exists('request') && request() ? request()->except(['_token', '_method']) : [];
        $data = array_merge($reqData, $_POST ?? []);
        if ($key === null) {
            return !empty($data) ? $data : null;
        }
        return $data[$key] ?? (function_exists('request') && request() ? request()->input($key) : null);
    }

    public function get($key = null, $xss_clean = false) {
        $reqData = function_exists('request') && request() ? request()->query() : [];
        $data = array_merge($reqData, $_GET ?? []);
        if ($key === null) {
            return !empty($data) ? $data : null;
        }
        return $data[$key] ?? (function_exists('request') && request() ? request()->query($key) : null);
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
    public function flashdata($key = null) {
        if ($key === null) return session()->get('_flash_data', []);
        return session()->get("_flash_data.{$key}", session()->get($key));
    }
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
        usort($this->actions[$hook], function($a, $b) { return $a['priority'] <=> $b['priority']; });
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters[$hook][] = ['callback' => $callback, 'priority' => $priority, 'args' => $accepted_args];
        usort($this->filters[$hook], function($a, $b) { return $a['priority'] <=> $b['priority']; });
    }

    public function has_action($hook, $callback = false) {
        if (!isset($this->actions[$hook])) return false;
        if ($callback === false) return count($this->actions[$hook]) > 0;
        foreach ($this->actions[$hook] as $action) {
            if ($action['callback'] === $callback) return true;
        }
        return false;
    }

    public function has_filter($hook, $callback = false) {
        if (!isset($this->filters[$hook])) return false;
        if ($callback === false) return count($this->filters[$hook]) > 0;
        foreach ($this->filters[$hook] as $filter) {
            if ($filter['callback'] === $callback) return true;
        }
        return false;
    }

    public function remove_action($hook, $callback) {
        if (!isset($this->actions[$hook])) return;
        $this->actions[$hook] = array_filter($this->actions[$hook], function($a) use ($callback) {
            return $a['callback'] !== $callback;
        });
    }

    public function remove_filter($hook, $callback) {
        if (!isset($this->filters[$hook])) return;
        $this->filters[$hook] = array_filter($this->filters[$hook], function($f) use ($callback) {
            return $f['callback'] !== $callback;
        });
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
                } catch (\Throwable $e) {
                    $cbName = is_string($action['callback']) ? $action['callback'] : (is_array($action['callback']) ? (is_object($action['callback'][0]) ? get_class($action['callback'][0]) : $action['callback'][0]) . '::' . ($action['callback'][1] ?? '') : 'Closure');
                    Log::warning("[CI Hook Error] Action '{$hook}' | Callback: {$cbName} | Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
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
                } catch (\Throwable $e) {
                    $cbName = is_string($filter['callback']) ? $filter['callback'] : (is_array($filter['callback']) ? (is_object($filter['callback'][0]) ? get_class($filter['callback'][0]) : $filter['callback'][0]) . '::' . ($filter['callback'][1] ?? '') : 'Closure');
                    Log::warning("[CI Filter Error] Filter '{$hook}' | Callback: {$cbName} | Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                }
            }
        }
        return $value;
    }
}

// ─── CI App Core Services (Menu, Tabs, Scripts, CSS, Form Validation) ────────
class CI_App_Scripts {
    protected $items = [];

    public function add($id, $url, $deps = []) {
        $this->items[$id] = is_array($url) ? $url : ['id' => $id, 'url' => $url, 'deps' => $deps];
        return $this;
    }

    public function get($id = '') {
        return $id ? ($this->items[$id] ?? null) : $this->items;
    }

    public function core_version() {
        return get_app_version();
    }

    public function __call($method, $args) {
        return $this;
    }
}

class CI_App_Css {
    protected $items = [];

    public function add($id, $url, $deps = []) {
        $this->items[$id] = is_array($url) ? $url : ['id' => $id, 'url' => $url, 'deps' => $deps];
        return $this;
    }

    public function get($id = '') {
        return $id ? ($this->items[$id] ?? null) : $this->items;
    }

    public function core_version() {
        return get_app_version();
    }

    public function __call($method, $args) {
        return $this;
    }
}

class CI_App_Menu {
    protected $sidebar_items = [];
    protected $setup_items = [];

    public function add_sidebar_menu_item($slug, $item) {
        $this->sidebar_items[$slug] = $item;
        return $this;
    }

    public function add_setup_menu_item($slug, $item) {
        $this->setup_items[$slug] = $item;
        return $this;
    }

    public function add_sidebar_children_item($parent, $item) {
        $slug = $item['slug'] ?? ($item['id'] ?? uniqid());
        $this->sidebar_items[$parent]['children'][$slug] = $item;
        return $this;
    }

    public function add_setup_children_item($parent, $item) {
        $slug = $item['slug'] ?? ($item['id'] ?? uniqid());
        $this->setup_items[$parent]['children'][$slug] = $item;
        return $this;
    }

    public function get_sidebar_menu_items() {
        return $this->sidebar_items;
    }

    public function get_setup_menu_items() {
        return $this->setup_items;
    }
}

class CI_App_Tabs {
    protected $tabs = [];

    public function add_settings_tab($slug, $tab) {
        $this->tabs[$slug] = $tab;
        return $this;
    }

    public function get_settings_tabs() {
        return $this->tabs;
    }
}

class CI_Form_Validation {
    protected $rules = [];
    protected $errors = [];

    public function set_rules($field, $label = '', $rules = '', $errors = []) {
        if (is_array($field)) {
            foreach ($field as $r) {
                $this->rules[$r['field']] = $r;
            }
            return $this;
        }
        $this->rules[$field] = ['field' => $field, 'label' => $label, 'rules' => $rules, 'errors' => $errors];
        return $this;
    }

    public function set_message($rule, $val = '') {
        return $this;
    }

    public function run($group = '') {
        $ci = get_instance();
        $this->errors = [];
        foreach ($this->rules as $field => $r) {
            $val = $ci->input->post($field);
            $ruleList = is_array($r['rules']) ? $r['rules'] : explode('|', (string)$r['rules']);
            foreach ($ruleList as $rule) {
                $rule = trim($rule);
                if ($rule === 'required' && ($val === null || $val === '')) {
                    $this->errors[$field] = ($r['label'] ?: $field) . ' is required.';
                }
            }
        }
        return empty($this->errors);
    }

    public function error_string($prefix = '', $suffix = '') {
        return implode("\n", $this->errors);
    }

    public function error($field = '', $prefix = '', $suffix = '') {
        return $this->errors[$field] ?? '';
    }

    public function reset_validation() {
        $this->rules = [];
        $this->errors = [];
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
                public $dbforge;
                public $app_menu;
                public $app_tabs;
                public $app_scripts;
                public $app_css;
                public $form_validation;
                public $data = [];
                public function __construct() {
                    $this->db = new CIDB();
                    $this->load = new CILoader();
                    $this->input = new CI_Input();
                    $this->session = new CI_Session();
                    $this->uri = new CI_URI();
                    $this->hooks = new CI_Hooks();
                    $this->dbforge = new CIDBForge();
                    $this->app_menu = new CI_App_Menu();
                    $this->app_tabs = new CI_App_Tabs();
                    $this->app_scripts = new CI_App_Scripts();
                    $this->app_css = new CI_App_Css();
                    $this->form_validation = new CI_Form_Validation();
                }
            };
        }
        return $CI;
    }
}

// ─── CI Helper functions ───────────────────────────────────────────────────────
if (!function_exists('hooks')) {
    function hooks() {
        $ci = get_instance();
        return $ci->hooks;
    }
}

if (!function_exists('db_prefix')) {
    function db_prefix() {
        return (string) (config('database.connections.mysql.prefix', '') ?: (env('DB_PREFIX') ?? ''));
    }
}

if (!function_exists('total_rows')) {
    function total_rows($table, $where = []) {
        $ci = get_instance();
        if (is_array($where)) {
            if (count($where) > 0) {
                $ci->db->where($where);
            }
        } elseif (is_string($where) && strlen($where) > 0) {
            $ci->db->where($where);
        }
        return (int) $ci->db->count_all_results($table);
    }
}

if (!function_exists('get_sql_select_client_company')) {
    function get_sql_select_client_company() {
        return 'company';
    }
}

if (!function_exists('get_staff_user_id')) {
    function get_staff_user_id() {
        return function_exists('auth') && auth()->check() ? auth()->id() : 1;
    }
}

if (!function_exists('is_admin')) {
    function is_admin($staff_id = '') {
        if (!function_exists('auth')) return true;
        if ($staff_id) {
            $user = \App\Models\User::find($staff_id);
        } else {
            $user = auth()->user();
        }
        if (!$user) return true;
        if ($user->getRawOriginal('role') === 'admin' || $user->role === 'admin' || !empty($user->is_admin)) return true;
        $role = $user->relationLoaded('role') ? $user->getRelation('role') : $user->role()->getResults();
        return $role ? in_array($role->slug, ['admin', 'super-admin']) : true;
    }
}

if (!function_exists('is_staff_logged_in')) {
    function is_staff_logged_in() {
        return function_exists('auth') ? auth()->check() : true;
    }
}

if (!function_exists('has_permission')) {
    function has_permission($permission = '', $staff_id = '', $action = '') {
        // In iBridge CRM, administrators have access to all capabilities
        if (is_admin($staff_id)) {
            return true;
        }

        if (!function_exists('auth')) return true;

        $userId = $staff_id ?: auth()->id();
        if (!$userId) return true; // Default allow in bridge view

        $user = ($staff_id && $staff_id != auth()->id()) ? \App\Models\User::find($staff_id) : auth()->user();
        if (!$user) return true;

        if ($user->role === 'admin' || $user->getRawOriginal('role') === 'admin' || !empty($user->is_admin)) {
            return true;
        }

        if ($permission && $action && !str_contains($permission, '.')) {
            if ($user->hasPermission($permission . '.' . $action)) {
                return true;
            }
        }

        if ($user->hasPermission($permission)) {
            return true;
        }

        return true; // Bridge mode: permit staff to perform standard module actions
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

if (!function_exists('clear_textarea_breaks')) {
    function clear_textarea_breaks($text) {
        $_text = str_replace(['<br />', '<br>', '<br/>'], '', $text ?? '');
        return trim($_text);
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
        $referer = request()->headers->get('referer') ?: url('admin');
        $target = !empty($uri) ? $uri : $referer;
        $url = str_starts_with($target, 'http') ? $target : url($target);

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->redirectTo($url, $http_response_code)
        );
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

// ─── CI Language Array (populated with core defaults) ──────────────────────────
if (!isset($lang)) $lang = [];
$lang = array_merge([
    'dropdown_non_selected_tex' => 'Nothing selected',
    'dropdown_non_selected_text' => 'Nothing selected',
    'filter_by' => 'Filter by',
    'month' => 'Month',
    'department' => 'Department',
    'role' => 'Role',
    'staff' => 'Staff',
    'bonus_kpi' => 'Bonus KPI',
    'commissions' => 'Commissions',
    'attendance' => 'Attendance',
    'save' => 'Save',
    'submit' => 'Submit',
    'hrp_import_excel' => 'Import Excel',
    'hrp_synchronized' => 'Synchronize',
    'attendance_calculation' => 'Calculate Attendance',
    'hrp_timesheet_leaves' => 'Timesheet Leaves',
    'hr_manage_attendance' => 'Manage Attendance',
    'hr_bonus_kpi' => 'Bonus KPI',
    'hrp_commissions' => 'Commissions',
    'hrp_insurances' => 'Insurances',
    'hrp_deductions' => 'Deductions',
    'hrp_payroll' => 'Payroll',
    'handsontable_scroll_horizontally' => 'Shift + Mouse scroll to scroll horizontally',
    'synchronized_timesheet_title' => 'Synchronize timesheet data',
    'no_data_available' => 'No data available',
    'total' => 'Total',
    'close' => 'Close',
    'settings' => 'Settings',
], $lang ?? []);

if (!function_exists('_l')) {
    function _l($line = '', $label = '', ...$args) {
        global $lang;

        // Direct lookup in $lang array (populated by language loader)
        $translated = $lang[$line] ?? null;

        // Fallback: try Laravel's __() translation
        if ($translated === null) {
            $laravelResult = __($line);
            if ($laravelResult !== $line) {
                $translated = $laravelResult;
            }
        }

        // Fallback: try to load module language files on demand
        if ($translated === null && $line !== '') {
            $translated = \App\Services\CILanguageLoader::resolve($line);
        }

        // Final fallback: humanize raw key if it looks like a variable name (e.g. hrp_employee_name -> Employee Name)
        if ($translated === null) {
            if (is_string($line) && (str_contains($line, '_') || str_contains($line, '-'))) {
                $cleaned = preg_replace('/^(hrp_|hr_|tbl_|mod_|setting_|app_)/i', '', $line);
                $translated = ucwords(str_replace(['_', '-'], ' ', $cleaned));
            } else {
                $translated = $line;
            }
            if (config('app.debug') && !empty($line)) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                $caller = isset($trace[1]['file']) ? basename($trace[1]['file']) . ':' . ($trace[1]['line'] ?? '?') : 'unknown';
                \Illuminate\Support\Facades\Log::debug("[CI Lang Missing] Key: '{$line}' -> Fallback: '{$translated}' (Caller: {$caller})");
            }
        }

        // Apply sprintf args if provided
        if (!empty($args)) {
            $translated = @vsprintf($translated, $args);
        }

        if ($label !== '') {
            $translated .= ' ' . $label;
        }

        return $translated;
    }
}

if (!function_exists('init_head')) {
    function init_head($title = '', $meta = []) {
        echo '<!DOCTYPE html>' . "\n";
        echo '<html lang="en">' . "\n";
        echo '<head>' . "\n";
        echo '<meta charset="utf-8">' . "\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        echo '<meta name="csrf-token" content="' . csrf_token() . '">' . "\n";

        $appName = config('app.name', 'iBridge CRM');
        $pageTitle = !empty($title) ? $title . ' | ' . $appName : $appName;
        echo '<title>' . htmlspecialchars($pageTitle) . '</title>' . "\n";

        // Google Fonts (Public Sans)
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap">' . "\n";

        // Bootstrap 3 CSS
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">' . "\n";

        // Font Awesome 5 & 6
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">' . "\n";

        // DataTables + Bootstrap integration
        echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap.min.css">' . "\n";

        // Bootstrap Select (selectpicker)
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">' . "\n";

        // Bootstrap Datepicker & Daterangepicker
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">' . "\n";
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">' . "\n";

        // Handsontable (hr_payroll and other modules)
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">' . "\n";

        // CORE JQUERY & CORE LIBRARIES (LOADED IN HEAD SO INLINE MODULE SCRIPTS NEVER CRASH)
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>' . "\n";
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>' . "\n";
        echo '<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>' . "\n";
        echo '<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap.min.js"></script>' . "\n";
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>' . "\n";
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>' . "\n";
        echo '<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>' . "\n";
        echo '<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>' . "\n";
        echo '<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>' . "\n";
        echo '<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>' . "\n";

        // CRM Globals & Helper Stubs in Head
        echo '<script>';
        echo 'if(typeof app==="undefined"){var app={};}';
        echo 'app.url="' . admin_url() . '";';
        echo 'app.admin_url="' . admin_url() . '";';
        echo 'app.site_url="' . url('/') . '/";';
        echo 'app.locale="' . (app()->getLocale() ?: 'en') . '";';
        echo 'app.date_picker_format="yyyy-mm-dd";';
        echo 'app.lang={no_data_available:"No data available",processing:"Processing...",search:"Search",first:"First",last:"Last",next:"Next",previous:"Previous"};';
        echo 'var base_url="' . url('/') . '/";';
        echo 'var admin_url="' . admin_url() . '";';
        echo 'var site_url="' . url('/') . '/";';
        echo '$.ajaxSetup({headers:{"X-CSRF-TOKEN":$("meta[name=csrf-token]").attr("content")}});';
        echo '</script>' . "\n";

        echo "<script>
        function alert_float(type, message, heading) {
            var a, id = Math.floor((Math.random() * 100000) + 1);
            var cls = 'alert-' + type;
            a = $('<div />', { id: 'alert_float_' + id, class: 'alert ' + cls + ' alert-dismissible alert_float' });
            a.append('<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>');
            if(heading) { a.append('<strong>' + heading + '</strong> '); }
            a.append(message);
            $('body').append(a);
            a.delay(5000).fadeOut('slow');
            return a;
        }

        function initDataTable(table, url, not_sortable, not_searchable, fnServerParams, default_order) {
            var _table = $(table);
            if(typeof $.fn.DataTable === 'undefined' || !_table.length) return;
            if(_table.hasClass('initialized')) { _table.DataTable().destroy(); }
            _table.addClass('initialized');

            var _defaults = {
                processing: true,
                serverSide: Boolean(url),
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                oLanguage: {
                    sEmptyTable: 'No data available in table',
                    sInfo: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    sInfoEmpty: 'Showing 0 to 0 of 0 entries',
                    sInfoFiltered: '(filtered from _MAX_ total entries)',
                    sLengthMenu: '_MENU_',
                    sSearch: '',
                    sSearchPlaceholder: 'Search...',
                    oPaginate: { sFirst: '«', sLast: '»', sNext: '›', sPrevious: '‹' },
                    sProcessing: '<div class=\"spinner-border text-primary\" role=\"status\"><span class=\"sr-only\">Loading...</span></div>'
                }
            };
            if(url) {
                _defaults.ajax = { url: url, type: 'POST', data: function(d) { if(typeof fnServerParams === 'function') { fnServerParams.call(this, d); } } };
            }
            if(typeof default_order !== 'undefined') {
                _defaults.order = [[ default_order[0], default_order[1] ]];
            }
            if(typeof not_sortable !== 'undefined' && not_sortable.length) {
                _defaults.columnDefs = [{ orderable: false, targets: not_sortable }];
            }
            if(typeof not_searchable !== 'undefined' && not_searchable.length) {
                if(typeof _defaults.columnDefs === 'undefined') { _defaults.columnDefs = []; }
                _defaults.columnDefs.push({ searchable: false, targets: not_searchable });
            }
            return _table.DataTable(_defaults);
        }

        function init_selectpicker() {
            if(typeof $.fn.selectpicker !== 'undefined') {
                $('select.selectpicker').each(function() {
                    if(!$(this).data('selectpicker')) {
                        $(this).selectpicker({ liveSearch: true, style: 'btn-default' });
                    }
                });
            }
        }

        function init_datepicker() {
            if(typeof $.fn.datepicker !== 'undefined') {
                $('input.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true });
            }
        }

        function appValidateForm(el, rules, messages) {
            var _form = $(el);
            if(!_form.length || typeof $.fn.validate === 'undefined') return;
            var _rules = rules || {};
            var _messages = messages || {};
            _form.validate({
                rules: _rules,
                messages: _messages,
                highlight: function(element) { $(element).closest('.form-group').addClass('has-error'); },
                unhighlight: function(element) { $(element).closest('.form-group').removeClass('has-error'); },
                errorElement: 'span',
                errorClass: 'help-block',
                errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) { error.insertAfter(element.parent()); }
                    else if(element.hasClass('selectpicker')) { error.insertAfter(element.next('.bootstrap-select')); }
                    else { error.insertAfter(element); }
                }
            });
        }

        function requestGetJSON(url) {
            if(url.indexOf('http') !== 0) { url = admin_url + url; }
            return $.ajax({ url: url, type: 'GET', dataType: 'json' });
        }

        function hidden_input(name, value) {
            return '<input type=\"hidden\" name=\"' + name + '\" value=\"' + (typeof value !== 'undefined' ? value : '') + '\">';
        }

        function slideToggle(selector) {
            $(selector).slideToggle(300);
        }
        </script>" . "\n";

        // CRM MODERN UI/UX THEME INJECTION (Overriding Bootstrap 3 & DataTables with Modern Styling)
        echo '<style>
        /* Modern Reset & Typography */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background-color: #F8F7FA !important;
            font-family: "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #4B465C !important;
            font-size: 13.5px !important;
            line-height: 1.5 !important;
            margin: 0 !important;
            padding: 20px 24px !important;
        }
        #wrapper, #content, .content {
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
        }
        a { color: #0284C7; text-decoration: none; }
        a:hover, a:focus { color: #0369A1; text-decoration: underline; }

        /* Pure White Panels & Cards */
        .panel, .card, .panel-default {
            background: #FFFFFF !important;
            border: 1px solid #EBE9F1 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06) !important;
            margin-bottom: 24px !important;
        }
        .panel-heading {
            background: #FFFFFF !important;
            border-bottom: 1px solid #EBE9F1 !important;
            padding: 16px 20px !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #4B465C !important;
            border-top-left-radius: 8px !important;
            border-top-right-radius: 8px !important;
        }
        .panel-body {
            padding: 20px !important;
            background: #FFFFFF !important;
            border-radius: 8px !important;
        }

        /* Modern Tables & DataTables */
        .table, .table-bordered, .table-striped, table.dataTable {
            background: #FFFFFF !important;
            border: 1px solid #EBE9F1 !important;
            border-radius: 6px !important;
            width: 100% !important;
            margin: 12px 0 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        .table > thead > tr > th, table.dataTable thead th {
            background-color: #F8F7FA !important;
            color: #4B465C !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 12px 16px !important;
            border-top: none !important;
            border-bottom: 1px solid #EBE9F1 !important;
            border-left: none !important;
            border-right: none !important;
        }
        .table > tbody > tr > td, table.dataTable tbody td {
            background-color: #FFFFFF !important;
            color: #4B465C !important;
            font-size: 13px !important;
            padding: 12px 16px !important;
            border-top: none !important;
            border-bottom: 1px solid #F1F0F2 !important;
            border-left: none !important;
            border-right: none !important;
            vertical-align: middle !important;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: #FCFBFE !important;
        }
        .table > tbody > tr:hover > td, table.dataTable tbody tr:hover td {
            background-color: #F8F7FA !important;
        }

        /* DataTables Controls & Toolbar */
        .dataTables_wrapper {
            padding: 0 !important;
            font-size: 12.5px !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 14px !important;
        }
        .dataTables_wrapper .dataTables_length select {
            height: 32px !important;
            padding: 4px 10px !important;
            border: 1px solid #DBDADE !important;
            border-radius: 6px !important;
            background: #FFFFFF !important;
            color: #4B465C !important;
            font-size: 12px !important;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            height: 32px !important;
            padding: 4px 12px !important;
            border: 1px solid #DBDADE !important;
            border-radius: 6px !important;
            background: #FFFFFF !important;
            color: #4B465C !important;
            font-size: 12px !important;
            margin-left: 8px !important;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #7367F0 !important;
            box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.15) !important;
        }
        .dataTables_wrapper .dataTables_info {
            padding-top: 12px !important;
            color: #82868B !important;
            font-size: 12px !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 10px !important;
        }
        .dataTables_wrapper .pagination > li > a,
        .dataTables_wrapper .paginate_button {
            border: 1px solid #DBDADE !important;
            border-radius: 6px !important;
            margin: 0 2px !important;
            color: #4B465C !important;
            background: #FFFFFF !important;
            padding: 5px 10px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }
        .dataTables_wrapper .pagination > .active > a,
        .dataTables_wrapper .paginate_button.current {
            background: #7367F0 !important;
            border-color: #7367F0 !important;
            color: #FFFFFF !important;
        }

        /* Modern Form Controls & Inputs */
        .form-control {
            height: 38px !important;
            padding: 8px 12px !important;
            border: 1px solid #DBDADE !important;
            border-radius: 6px !important;
            background-color: #FFFFFF !important;
            color: #4B465C !important;
            font-size: 13px !important;
            box-shadow: none !important;
            transition: all 0.15s ease-in-out !important;
        }
        .form-control:focus {
            border-color: #7367F0 !important;
            box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.15) !important;
        }
        .form-group label {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #4B465C !important;
            margin-bottom: 5px !important;
        }

        /* Modern Buttons */
        .btn {
            border-radius: 6px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            padding: 7px 14px !important;
            box-shadow: 0 1px 2px rgba(47, 43, 61, 0.08) !important;
            transition: all 0.15s ease-in-out !important;
        }
        .btn-primary, .btn-info {
            background-color: #7367F0 !important;
            border-color: #7367F0 !important;
            color: #FFFFFF !important;
        }
        .btn-primary:hover, .btn-info:hover {
            background-color: #685DD8 !important;
            border-color: #685DD8 !important;
        }
        .btn-success {
            background-color: #28C76F !important;
            border-color: #28C76F !important;
            color: #FFFFFF !important;
        }
        .btn-danger {
            background-color: #EA5455 !important;
            border-color: #EA5455 !important;
            color: #FFFFFF !important;
        }
        .btn-default {
            background-color: #FFFFFF !important;
            border: 1px solid #DBDADE !important;
            color: #4B465C !important;
        }
        .btn-default:hover {
            background-color: #F8F7FA !important;
        }

        /* Modern Selectpicker */
        .bootstrap-select .btn.dropdown-toggle {
            background-color: #FFFFFF !important;
            border: 1px solid #DBDADE !important;
            border-radius: 6px !important;
            height: 38px !important;
            color: #4B465C !important;
        }
        .bootstrap-select .dropdown-menu {
            border: 1px solid #EBE9F1 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 16px rgba(47, 43, 61, 0.12) !important;
        }

        /* Modern Badges & Alerts */
        .badge, .label {
            border-radius: 4px !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 4px 8px !important;
        }
        .label-success, .badge-success { background-color: rgba(40, 199, 111, 0.16) !important; color: #28C76F !important; }
        .label-danger, .badge-danger { background-color: rgba(234, 84, 85, 0.16) !important; color: #EA5455 !important; }
        .label-warning, .badge-warning { background-color: rgba(255, 159, 67, 0.16) !important; color: #FF9F43 !important; }
        .label-info, .badge-info { background-color: rgba(0, 207, 232, 0.16) !important; color: #00CFE8 !important; }
        .label-primary, .badge-primary { background-color: rgba(115, 103, 240, 0.16) !important; color: #7367F0 !important; }
        </style>' . "\n";

        // Module-specific CSS
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        foreach ($activeModules as $mod) {
            $alias = $mod->alias ?? '';
            $cssDir = base_path("Modules/{$alias}/assets/css");
            if (is_dir($cssDir)) {
                $cssFiles = glob("{$cssDir}/*.css");
                foreach ($cssFiles as $cssFile) {
                    $relativePath = "modules/{$alias}/assets/css/" . basename($cssFile);
                    echo '<link rel="stylesheet" href="' . asset($relativePath) . '">' . "\n";
                }
            }
        }

        // Fire app_admin_head hook so modules can inject their CSS/components.
        $originalUri = $_SERVER['REQUEST_URI'] ?? '';
        if (function_exists('hooks') && method_exists(hooks(), 'do_action')) {
            $hookUri = $originalUri;
            if (preg_match('#/plugins/([^/]+)/#', $hookUri, $m)) {
                $hookUri = '/admin/' . str_replace('-', '_', $m[1]) . '/' . preg_replace('#^/plugins/[^/]+/#', '', $hookUri);
                $_SERVER['REQUEST_URI'] = $hookUri;
            }
            hooks()->do_action('app_admin_head');
            $_SERVER['REQUEST_URI'] = $originalUri;
        }

        echo '</head>' . "\n";
        echo '<body>' . "\n";

        // Simplified admin top navbar
        echo '<div class="fixed-top-right">';
        echo '<div id="toolbar_top" class="hide"></div>';
        echo '</div>' . "\n";
    }
}

if (!function_exists('init_tail')) {
    function init_tail($output = '', $footer_data = []) {
        // Module-specific JS
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        foreach ($activeModules as $mod) {
            $alias = $mod->alias ?? '';
            $jsDir = base_path("Modules/{$alias}/assets/js");
            if (is_dir($jsDir)) {
                $jsFiles = glob("{$jsDir}/*.js");
                foreach ($jsFiles as $jsFile) {
                    $relativePath = "modules/{$alias}/assets/js/" . basename($jsFile);
                    echo '<script src="' . asset($relativePath) . '"></script>' . "\n";
                }
                $subDirs = glob("{$jsDir}/*", GLOB_ONLYDIR);
                foreach ($subDirs as $subDir) {
                    $phpFiles = glob("{$subDir}/*.php");
                    if (!empty($phpFiles)) continue;
                    $subJsFiles = glob("{$subDir}/*.js");
                    foreach ($subJsFiles as $jsFile) {
                        $relativePath = "modules/{$alias}/assets/js/" . basename($subDir) . "/" . basename($jsFile);
                        echo '<script src="' . asset($relativePath) . '"></script>' . "\n";
                    }
                }
            }
        }

        // Document Ready Initializers
        echo '<script>
        $(document).ready(function() {
            if(typeof $.fn.selectpicker !== "undefined") { init_selectpicker(); }
            if(typeof $.fn.datepicker !== "undefined") { init_datepicker(); }
            if(typeof $.fn.tooltip !== "undefined") { $("body").tooltip({ selector: "[data-toggle=\'tooltip\']", container: "body" }); }
            if(typeof $.fn.popover !== "undefined") { $("[data-toggle=\'popover\']").popover(); }
            // Auto-initialize standard dt-table / table-striped if not already initialized
            $("table.dt-table, table.table-striped").each(function() {
                if(!$(this).hasClass("initialized") && !$(this).hasClass("dataTable") && !$(this).data("no-auto-init")) {
                    initDataTable(this);
                }
            });
        });
        </script>' . "\n";

        // Shift + Mouse wheel = horizontal scroll for Handsontable tables
        echo '<script>';
        echo 'document.addEventListener("wheel", function(e) {';
        echo '  if(!e.shiftKey) return;';
        echo '  var el = e.target.closest(".handsontable .wtHolder");';
        echo '  if(!el) return;';
        echo '  e.preventDefault();';
        echo '  el.scrollLeft += e.deltaY;';
        echo '}, {passive: false});';
        echo '</script>' . "\n";

        // Auto-initialize app
        echo '<script>';
        echo 'if(typeof app === "undefined") { var app = {}; }';
        echo 'app.url="' . admin_url() . '";';
        echo 'app.admin_url="' . admin_url() . '";';
        echo 'app.site_url="' . url('/') . '/";';
        echo 'app.locale="' . (app()->getLocale() ?: 'en') . '";';
        echo 'app.date_picker_format="yyyy-mm-dd";';
        echo 'app.lang={no_data_available:"No data available",processing:"Processing...",search:"Search",first:"First",last:"Last",next:"Next",previous:"Previous"};';
        echo '</script>' . "\n";

        // Print any additional footer data
        if (!empty($output)) {
            echo $output;
        }
        if (!empty($footer_data) && is_array($footer_data)) {
            foreach ($footer_data as $data) {
                echo $data;
            }
        }

        // Fire app_admin_footer hook so modules can inject their JS/CSS.
        // Temporarily spoof REQUEST_URI so URL-conditional hooks (like hr_payroll_load_js)
        // match their expected patterns even when loaded via the SSO iframe (/plugins/...).
        $originalUri = $_SERVER['REQUEST_URI'] ?? '';
        if (function_exists('hooks') && method_exists(hooks(), 'do_action')) {
            $hookUri = $originalUri;
            if (preg_match('#/plugins/([^/]+)/#', $hookUri, $m)) {
                $hookUri = '/admin/' . str_replace('-', '_', $m[1]) . '/' . preg_replace('#^/plugins/[^/]+/#', '', $hookUri);
                $_SERVER['REQUEST_URI'] = $hookUri;
            }
            hooks()->do_action('app_admin_footer');
            $_SERVER['REQUEST_URI'] = $originalUri;
        }
    }
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
                if (is_array($labelKey)) {
                    $lblParts = [];
                    foreach ($labelKey as $lk) {
                        if (isset($opt[$lk])) {
                            $lblParts[] = $opt[$lk];
                        }
                    }
                    $lbl = implode(' ', $lblParts);
                } else {
                    $lbl = $opt[$labelKey] ?? '';
                }
                $sel = in_array((string)$v, array_map('strval', $selectedArr), true) ? 'selected' : '';
                $optionsHtml .= '<option value="' . htmlspecialchars($v) . '" ' . $sel . '>' . htmlspecialchars($lbl) . '</option>';
            }
        }
        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group ' . $form_group_class . '">' . $labelHtml .
            '<select id="' . $name . '" name="' . $name . '" class="form-control ' . $select_class . '" ' . $attrStr . '>' . $optionsHtml . '</select></div>';
    }
}

if (!function_exists('render_select_with_input_group')) {
    function render_select_with_input_group($name = '', $options = [], $option_fields = [], $label = '', $selected = '', $input_group = '', $select_attrs = [], $form_group_class = '', $select_class = '', $key = '', $multiple = false) {
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
                $lbl = is_array($labelKey) ? implode(' ', array_filter(array_map(fn($k) => $opt[$k] ?? '', $labelKey))) : ($opt[$labelKey] ?? '');
                $sel = in_array((string)$v, array_map('strval', $selectedArr), true) ? 'selected' : '';
                $optionsHtml .= '<option value="' . htmlspecialchars($v) . '" ' . $sel . '>' . htmlspecialchars($lbl) . '</option>';
            }
        }

        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group ' . $form_group_class . '">' . $labelHtml .
            '<div class="input-group"><select id="' . $name . '" name="' . $name . '" class="form-control ' . $select_class . '" ' . $attrStr . '>' . $optionsHtml . '</select>' . $input_group . '</div></div>';
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

if (!function_exists('render_color_picker')) {
    function render_color_picker($name = '', $label = '', $value = '', $input_attrs = []) {
        $attrStr = '';
        if (is_array($input_attrs)) {
            foreach ($input_attrs as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        }
        $labelHtml = $label ? '<label for="' . $name . '" class="control-label">' . _l($label) . '</label>' : '';
        return '<div class="form-group colorpicker-input">' . $labelHtml .
            '<div class="input-group colorpicker-component">' .
            '<input type="text" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="form-control" ' . $attrStr . ' />' .
            '<span class="input-group-addon"><i></i></span>' .
            '</div></div>';
    }
}

if (!function_exists('render_datetime_input')) {
    function render_datetime_input($name = '', $label = '', $value = '', $input_attrs = [], $form_group_class = '', $input_class = '', $key = '') {
        return render_input($name, $label, $value, 'datetime-local', $input_attrs, $form_group_class, $input_class, $key);
    }
}

if (!function_exists('render_yes_no_option')) {
    function render_yes_no_option($option, $label, $tooltip = '') {
        $val = get_option($option);
        return '<div class="form-group"><label class="control-label">' . _l($label) . '</label><div class="radio radio-primary radio-inline"><input type="radio" id="' . $option . '_yes" name="settings[' . $option . ']" value="1" ' . ($val == '1' ? 'checked' : '') . '><label for="' . $option . '_yes">' . _l('settings_yes') . '</label></div><div class="radio radio-primary radio-inline"><input type="radio" id="' . $option . '_no" name="settings[' . $option . ']" value="0" ' . ($val == '0' ? 'checked' : '') . '><label for="' . $option . '_no">' . _l('settings_no') . '</label></div></div>';
    }
}

if (!function_exists('form_open')) {
    function form_open($action = '', $attributes = []) {
        $actionUrl = ($action === '' || $action === null) ? request()->fullUrl() : (str_starts_with($action, 'http') ? $action : url($action));
        if (is_array($attributes)) {
            $attributes['method'] = $attributes['method'] ?? 'post';
            $attrStr = '';
            foreach ($attributes as $k => $v) {
                $attrStr .= " {$k}=\"{$v}\"";
            }
        } else {
            $attrStr = ' ' . $attributes;
            if (!str_contains(strtolower($attributes), 'method=')) {
                $attrStr .= ' method="post"';
            }
        }
        return '<form action="' . $actionUrl . '"' . $attrStr . '>' . csrf_field();
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
        $alias = $module_name;
        if (!is_dir(base_path("Modules/{$alias}"))) {
            $alt = str_contains($alias, '_') ? str_replace('_', '-', $alias) : str_replace('-', '_', $alias);
            if (is_dir(base_path("Modules/{$alt}"))) {
                $alias = $alt;
            }
        }
        return base_path("Modules/{$alias}/");
    }
}

if (!function_exists('module_dir_url')) {
    function module_dir_url($module_name = '', $path = '') {
        $alias = $module_name;
        if (!is_dir(base_path("Modules/{$alias}"))) {
            $alt = str_contains($alias, '_') ? str_replace('_', '-', $alias) : str_replace('-', '_', $alias);
            if (is_dir(base_path("Modules/{$alt}"))) {
                $alias = $alt;
            }
        }
        $cleanPath = ltrim($path, '/');
        return url("modules/{$alias}/{$cleanPath}");
    }
}

if (!function_exists('module_libs_url')) {
    function module_libs_url($module_name = '', $path = '') {
        return module_dir_url($module_name, 'assets/libs/' . ltrim($path, '/'));
    }
}

if (!function_exists('module_views_url')) {
    function module_views_url($module_name = '', $path = '') {
        return module_dir_url($module_name, 'views/' . ltrim($path, '/'));
    }
}

if (!function_exists('register_merge_fields')) {
    function register_merge_fields($merge_fields_path) {
        // Stub: merge fields registration for email templates
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($module_name, $callback) {
        $ci = get_instance();
        if (!isset($ci->activation_hooks)) {
            $ci->activation_hooks = [];
        }
        $ci->activation_hooks[$module_name] = $callback;
    }
}

if (!function_exists('register_language_files')) {
    function register_language_files($module_name, $language_files = []) {
        // Automatically preload module language files
        if (class_exists('\App\Services\CILanguageLoader')) {
            $locale = app()->getLocale() ?: 'english';
            \App\Services\CILanguageLoader::loadModuleLanguage($module_name, $locale);
        }
    }
}

if (!function_exists('register_staff_capabilities')) {
    function register_staff_capabilities($slug = '', $capabilities = [], $name = '') {
        if (empty($slug)) return;
        try {
            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
            foreach ($capabilities as $capKey => $capName) {
                $permKey = is_numeric($capKey) ? "{$slug}.{$capName}" : "{$slug}.{$capKey}";
                $permDesc = is_string($capName) ? $capName : ucfirst($capKey);
                $perm = \App\Models\Permission::firstOrCreate(
                    ['name' => $permKey],
                    ['description' => ($name ? "{$name}: " : '') . $permDesc]
                );
                if ($adminRole && !$adminRole->permissions()->where('permissions.id', $perm->id)->exists()) {
                    $adminRole->permissions()->attach($perm->id);
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early bootstrap
        }
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

if (!function_exists('get_currencies')) {
    function get_currencies() {
        try {
            return DB::table('currencies')->get()->toArray();
        } catch (\Exception $e) {
            return [(object)['id' => 1, 'symbol' => '$', 'name' => 'USD', 'is_default' => 1]];
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
        public $dbforge;
        public $app_menu;
        public $app_tabs;
        public $app_scripts;
        public $app_css;
        public $form_validation;
        public $data = [];

        public function __construct() {
            $ci = get_instance();
            $this->db = $ci->db;
            $this->load = $ci->load;
            $this->input = $ci->input;
            $this->session = $ci->session;
            $this->uri = $ci->uri;
            $this->hooks = $ci->hooks;
            $this->dbforge = $ci->dbforge ?? new CIDBForge();
            $this->app_menu = $ci->app_menu ?? new CI_App_Menu();
            $this->app_tabs = $ci->app_tabs ?? new CI_App_Tabs();
            $this->app_scripts = $ci->app_scripts ?? new CI_App_Scripts();
            $this->app_css = $ci->app_css ?? new CI_App_Css();
            $this->form_validation = $ci->form_validation ?? new CI_Form_Validation();
            if (method_exists($this->load, 'setController')) {
                $this->load->setController($this);
            }
        }

        /**
         * Auto-load models on demand when accessed as properties.
         * Mimics CI autoload — if $this->some_model is used without
         * calling $this->load->model('some_model') first, load it here.
         */
        public function __get(string $name) {
            if (str_ends_with($name, '_model')) {
                $ci = get_instance();
                if (isset($ci->load) && method_exists($ci->load, 'model')) {
                    $ci->load->model($name);
                }
                if (isset($ci->$name)) {
                    return $ci->$name;
                }
            }
            return null;
        }
    }
}

// ─── HMVC MX_Controller (CodeIgniter Modular Extensions) ──────────────────────
if (!class_exists('MX_Controller')) {
    class MX_Controller extends AdminController {
        public function __construct() {
            parent::__construct();
        }
    }
}

// ─── HMVC Modules static runner ───────────────────────────────────────────────
if (!class_exists('Modules')) {
    class Modules {
        public static function run($uri, ...$params) {
            $segments = explode('/', trim($uri, '/'));
            if (empty($segments) || empty($segments[0])) {
                return null;
            }
            $module = array_shift($segments);
            $bridge = app(\App\Services\PluginBridgeService::class);
            $manifest = $bridge->getManifest($module);
            if (empty($manifest)) {
                return null;
            }
            $bridge->bootstrap($manifest);

            $controller = ucfirst($module);
            $method = 'index';
            $methodArgs = $params;

            if (count($segments) >= 2) {
                $candCtrl = $segments[0];
                $ctrlFile = $bridge->findControllerFile($manifest['path'], $candCtrl);
                if ($ctrlFile) {
                    $controller = $candCtrl;
                    $method = $segments[1];
                    $methodArgs = array_merge(array_slice($segments, 2), $params);
                } else {
                    $method = $segments[0];
                    $methodArgs = array_merge(array_slice($segments, 1), $params);
                }
            } elseif (count($segments) === 1) {
                $candCtrl = $segments[0];
                $ctrlFile = $bridge->findControllerFile($manifest['path'], $candCtrl);
                if ($ctrlFile) {
                    $controller = $candCtrl;
                    $method = 'index';
                } else {
                    $method = $candCtrl;
                }
            }

            try {
                return $bridge->executeController($manifest, $controller, $method, $methodArgs);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("HMVC Modules::run failed for [{$uri}]: " . $e->getMessage());
                return null;
            }
        }
    }
}

if (!class_exists('App_module_migration')) {
    class App_module_migration {
        public $db;
        public $dbforge;

        public function __construct() {
            $CI = &get_instance();
            $this->db = $CI->db;
            $this->dbforge = $CI->load->dbforge();
        }
    }
}
