<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Currencies_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($currency_id = '') {
        if (is_numeric($currency_id) && $currency_id !== '') {
            $this->db->where('id', $currency_id);
            return $this->db->get(db_prefix() . 'currency_rates')->row();
        }

        $this->db->order_by('id', 'asc');
        return $this->db->get(db_prefix() . 'currency_rates')->result();
    }

    public function add($data) {
        return $this->db->insert(db_prefix() . 'currency_rates', $data);
    }

    public function update($data, $currency_id) {
        $this->db->where('id', $currency_id);
        return $this->db->update(db_prefix() . 'currency_rates', $data);
    }

    public function delete($currency_id) {
        $this->db->where('id', $currency_id);
        return $this->db->delete(db_prefix() . 'currency_rates');
    }
}
