<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Device_model extends CI_Model
{
    protected $table = 'devices';

    public function get_by_serial($serial)
    {
        return $this->db->get_where($this->table, ['serial_number' => $serial])->row();
    }

    public function get_by_customer($customer_id)
    {
        return $this->db->get_where($this->table, ['customer_id' => $customer_id])->result();
    }

    public function get_all()
    {
        return $this->db->select('d.*, c.name as customer_name')
                        ->from('devices d')
                        ->join('customers c', 'c.id = d.customer_id')
                        ->order_by('d.created_at', 'DESC')
                        ->get()->result();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function is_in_warranty($serial)
    {
        $device = $this->get_by_serial($serial);
        if (!$device || !$device->warranty_end) return false;
        return $device->warranty_end >= date('Y-m-d');
    }
}

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
