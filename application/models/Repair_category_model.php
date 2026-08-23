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

    // กรองหมวดหมู่ตามอาการที่ลูกค้าแจ้ง (issue_desc) โดยจับคำสำคัญ (keywords, คั่นด้วย comma) แบบ substring match
    // หมวดหมู่ที่ keywords ว่าง (NULL) = fallback ทั่วไป โชว์เฉพาะตอนไม่มีหมวดหมู่ไหน match เลย
    public function get_matching_categories($issue_desc)
    {
        $all      = $this->get_all(true);
        $issue    = mb_strtolower((string) $issue_desc, 'UTF-8');
        $matched  = [];
        $fallback = [];

        foreach ($all as $c) {
            if (empty($c->keywords)) {
                $fallback[] = $c;
                continue;
            }
            foreach (explode(',', $c->keywords) as $kw) {
                $kw = trim($kw);
                if ($kw !== '' && mb_strpos($issue, mb_strtolower($kw, 'UTF-8')) !== false) {
                    $matched[] = $c;
                    break;
                }
            }
        }

        return [
            'categories'   => $matched ?: $fallback,
            'auto_matched' => !empty($matched),
        ];
    }
}
