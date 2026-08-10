<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
    protected $table = 'customers';

    public function get_by_line_uid($line_uid)
    {
        return $this->db->get_where($this->table, ['line_uid' => $line_uid])->row();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function get_all()
    {
        return $this->db->order_by('company_name')->get($this->table)->result();
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

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function get_all_with_devices()
    {
        return $this->db
            ->select('c.*, COUNT(d.id) as device_count')
            ->from('customers c')
            ->join('devices d', 'd.customer_id = c.id', 'left')
            ->group_by('c.id')
            ->order_by('c.company_name')
            ->get()->result();
    }

    // อัปเดต line_uid เมื่อลูกค้า add Line OA ครั้งแรก
    public function bind_line_uid($customer_id, $line_uid)
    {
        return $this->db->update($this->table,
            ['line_uid' => $line_uid],
            ['id' => $customer_id]
        );
    }
}
