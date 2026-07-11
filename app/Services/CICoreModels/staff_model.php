<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($staff_id = '', $where = '') {
        if (is_numeric($staff_id) && $staff_id !== '') {
            $this->db->where(db_prefix() . 'staff.staffid', $staff_id);
            return $this->db->get(db_prefix() . 'staff')->row_array();
        }

        if ($where) {
            $this->db->where($where);
        }

        $this->db->order_by('firstname', 'asc');
        return $this->db->get(db_prefix() . 'staff')->result_array();
    }

    public function get_staff_permissions($id) {
        $this->db->where('staff_id', $id);
        return $this->db->get(db_prefix() . 'staff_permissions')->result_array();
    }

    public function update_permissions($staff_id, $permissions = []) {
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . 'staff_permissions');

        if (!empty($permissions)) {
            foreach ($permissions as $feature => $caps) {
                foreach ($caps as $cap => $val) {
                    if ($val == '1') {
                        $this->db->insert(db_prefix() . 'staff_permissions', [
                            'staff_id'   => $staff_id,
                            'feature'    => $feature,
                            'capability' => $cap,
                        ]);
                    }
                }
            }
        }
        return true;
    }

    public function update($data, $staff_id) {
        $this->db->where('staffid', $staff_id);
        return $this->db->update(db_prefix() . 'staff', $data);
    }

    public function add($data) {
        return $this->db->insert(db_prefix() . 'staff', $data);
    }

    public function delete($staff_id) {
        $this->db->where('staffid', $staff_id);
        return $this->db->delete(db_prefix() . 'staff');
    }
}
