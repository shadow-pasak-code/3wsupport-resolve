<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Faq_model extends CI_Model
{
    protected $table = 'faq';

    public function get_active_all()
    {
        return $this->db->get_where($this->table, ['is_active' => 1])->result();
    }

    public function get_all()
    {
        return $this->db->order_by('category')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->update($this->table, ['is_active' => 0], ['id' => $id]);
    }
}
