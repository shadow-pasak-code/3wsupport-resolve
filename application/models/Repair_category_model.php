<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repair_category_model extends CI_Model
{
    protected $table = 'repair_categories';

    public function get_all($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active']  = 1;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function set_active($id, $is_active)
    {
        return $this->db->update($this->table, [
            'is_active'  => $is_active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }
}
