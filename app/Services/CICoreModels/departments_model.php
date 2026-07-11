<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Departments_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($department_id = '') {
        if (is_numeric($department_id) && $department_id !== '') {
            $this->db->where('departmentid', $department_id);
            return $this->db->get(db_prefix() . 'departments')->row_array();
        }

        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . 'departments')->result_array();
    }

    public function add($data) {
        return $this->db->insert(db_prefix() . 'departments', $data);
    }

    public function update($data, $department_id) {
        $this->db->where('departmentid', $department_id);
        return $this->db->update(db_prefix() . 'departments', $data);
    }

    public function delete($department_id) {
        $this->db->where('departmentid', $department_id);
        return $this->db->delete(db_prefix() . 'departments');
    }
}
