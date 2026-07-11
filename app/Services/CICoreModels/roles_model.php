<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Roles_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($role_id = '') {
        if (is_numeric($role_id) && $role_id !== '') {
            $this->db->where('id', $role_id);
            $row = $this->db->get(db_prefix() . 'roles')->row_array();
            if ($row) {
                $row['roleid'] = $row['id'];
            }
            return $row;
        }

        $this->db->order_by('name', 'asc');
        $results = $this->db->get(db_prefix() . 'roles')->result_array();
        return array_map(function($r) {
            $r['roleid'] = $r['id'];
            return $r;
        }, $results);
    }

    public function update($data, $role_id) {
        $this->db->where('id', $role_id);
        return $this->db->update(db_prefix() . 'roles', $data);
    }

    public function add($data) {
        return $this->db->insert(db_prefix() . 'roles', $data);
    }

    public function delete($role_id) {
        $this->db->where('id', $role_id);
        return $this->db->delete(db_prefix() . 'roles');
    }
}
