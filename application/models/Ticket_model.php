<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket_model extends CI_Model
{
    protected $table = 'tickets';

    // สถานะทั้งหมดของ ticket
    const STATUS_PENDING        = 'pending';        // รออนุมัติ
    const STATUS_APPROVED       = 'approved';       // อนุมัติแล้ว รอ assign
    const STATUS_ASSIGNED       = 'assigned';       // assign ช่างแล้ว
    const STATUS_IN_PROGRESS    = 'in_progress';    // ช่างรับงานแล้ว
    const STATUS_WAITING_PARTS  = 'waiting_parts';  // เกินกำหนดตามหมวดหมู่การซ่อม รออะไหล่
    const STATUS_WAIT_QUOTE     = 'wait_quote';     // รอใบเสนอราคา (Partner)
    const STATUS_WAIT_REVIEW    = 'wait_review';    // ช่าง/Partner ส่งใบเสนอราคาแล้ว รอ Admin ตรวจสอบ/ดีดราคา
    const STATUS_WAIT_CONFIRM   = 'wait_confirm';   // Admin ส่งใบเสนอราคาให้ลูกค้าแล้ว รอลูกค้ายืนยันราคา
    const STATUS_ESCALATED      = 'escalated';      // ช่างส่งต่อ Partner
    const STATUS_COMPLETED      = 'completed';      // เสร็จสิ้น
    const STATUS_CLOSED         = 'closed';         // ปิด ticket
    const STATUS_QUOTE_ACCEPTED = 'quote_accepted';
    const STATUS_QUOTE_REJECTED = 'quote_rejected';

    // ประเภท ticket
    const TYPE_HARDWARE = 'hardware';
    const TYPE_SOFTWARE = 'software';

    public function get_all($filters = [])
    {
        $this->db->select('t.*, c.company_name as customer_name, c.line_uid,
                       d.name as device_name, d.serial_number,
                       u.name as technician_name, p.company_name as partner_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('users u', 'u.ref_id = t.technician_id AND u.role = "technician"', 'left')
            ->join('partners p', 'p.id = t.partner_id', 'left');

        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('t.ticket_type', $filters['type']);
        }
        if (!empty($filters['technician_id'])) {
            $this->db->where('t.technician_id', $filters['technician_id']);
        }
        if (!empty($filters['partner_id'])) {
            $this->db->where('t.partner_id', $filters['partner_id']);
        }

        return $this->db->order_by('t.created_at', 'DESC')->get()->result();
    }

    public function get_by_id($id)
    {
        // หมายเหตุ: ต้อง alias d.partner_id (Partner ประจำอุปกรณ์) ไม่ให้ชื่อชนกับ t.partner_id
        // (Partner ที่ถูก assign ให้ดูแล ticket นี้จริง) ไม่งั้นค่า t.partner_id จะถูกทับ
        // และทำให้ _check_ownership() ของ partner เตะ partner ที่ถูก assign ไว้จริงออกจากตั๋วตัวเอง
        return $this->db->select('t.*, c.company_name as customer_name, c.line_uid, c.phone,
                              d.name as device_name, d.serial_number, d.warranty_end, d.partner_id as device_partner_id,
                              u.name as technician_name, p.company_name as partner_name,
                              rc.name as repair_category_name, rc.max_days as repair_category_max_days')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('users u', 'u.ref_id = t.technician_id AND u.role = "technician"', 'left')
            ->join('partners p', 'p.id = t.partner_id', 'left')
            ->join('repair_categories rc', 'rc.id = t.repair_category_id', 'left')
            ->where('t.id', $id)
            ->get()->row();
    }

    // ตรวจงานที่เกินกำหนดตามหมวดหมู่การซ่อม (tech_end_date ผ่านไปแล้วแต่ยังไม่เสร็จ) แล้วเปลี่ยนสถานะเป็น "รออะไหล่"
    // เรียกจากหน้า list/detail หลักๆ แทนการใช้ cron เพราะแอปนี้ไม่มี background job runner
    public function flag_overdue_repairs()
    {
        $overdue = $this->db
            ->select('t.id as ticket_id, c.line_uid')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->where('t.status', self::STATUS_IN_PROGRESS)
            ->where('t.tech_end_date IS NOT NULL', null, false)
            ->where('t.tech_end_date <', date('Y-m-d'))
            ->get()->result();

        if (empty($overdue)) return;

        $this->load->library('Line_notify');

        foreach ($overdue as $t) {
            $this->update_status($t->ticket_id, self::STATUS_WAITING_PARTS);

            $this->db->insert('ticket_logs', [
                'ticket_id'  => $t->ticket_id,
                'user_id'    => null,
                'old_status' => self::STATUS_IN_PROGRESS,
                'new_status' => self::STATUS_WAITING_PARTS,
                'message'    => 'ระบบเปลี่ยนสถานะเป็น "รออะไหล่" อัตโนมัติ เนื่องจากเกินระยะเวลาที่กำหนดของหมวดหมู่การซ่อม',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($t->line_uid)) {
                $this->line_notify->push(
                    $t->line_uid,
                    "⏳ Ticket #{$t->ticket_id} ใช้เวลาซ่อมเกินกำหนดที่แจ้งไว้ครับ\n" .
                        "ระบบเปลี่ยนสถานะเป็น \"รออะไหล่\" ทีมงานกำลังตรวจสอบและจะแจ้งความคืบหน้าให้ทราบครับ"
                );
            }
        }
    }

    // รวม ticket_logs (เปลี่ยนสถานะ) + ticket_updates (อัพเดทรูป/ข้อความที่ส่งลูกค้า) เป็นเส้น timeline เดียว เรียงตามเวลา
    public function get_timeline($ticket_id)
    {
        $logs = $this->db
            ->where('ticket_id', $ticket_id)
            ->order_by('created_at', 'ASC')
            ->get('ticket_logs')->result();

        $updates = $this->db
            ->where('ticket_id', $ticket_id)
            ->order_by('created_at', 'ASC')
            ->get('ticket_updates')->result();

        $timeline = [];
        foreach ($logs as $l) {
            $timeline[] = (object) [
                'type'       => 'log',
                'created_at' => $l->created_at,
                'message'    => $l->message ?: ('เปลี่ยนสถานะเป็น ' . $l->new_status),
                'images'     => [],
            ];
        }
        foreach ($updates as $u) {
            $timeline[] = (object) [
                'type'       => 'update',
                'created_at' => $u->created_at,
                'message'    => $u->message,
                'images'     => $u->images ? json_decode($u->images, true) : [],
            ];
        }

        usort($timeline, function ($a, $b) {
            return strtotime($a->created_at) <=> strtotime($b->created_at);
        });

        return $timeline;
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status']     = self::STATUS_PENDING;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_status($id, $status, $extra = [])
    {
        $update = array_merge(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], $extra);
        return $this->db->update($this->table, $update, ['id' => $id]);
    }

    public function assign_technician($id, $technician_id)
    {
        // เคลียร์ partner_id ทิ้งด้วย เผื่อ ticket นี้เคยถูกช่างส่งต่อ Partner มาก่อน
        // (ไม่งั้น partner_id เก่าจะค้างอยู่ ทำให้หน้าช่างเข้าใจผิดว่า Partner ยังเป็นเจ้าของงานอยู่)
        return $this->update_status($id, self::STATUS_ASSIGNED, [
            'technician_id' => $technician_id,
            'partner_id'    => null,
        ]);
    }

    public function assign_partner($id, $partner_id, $extra = [])
    {
        return $this->update_status($id, self::STATUS_WAIT_QUOTE, array_merge([
            'partner_id' => $partner_id,
        ], $extra));
    }

    public function set_quote($id, $quote_amount, $quote_detail, $quote_file = null)
    {
        // ช่าง/Partner เพิ่งเสนอราคา — ยังไม่ได้ผ่านการตรวจสอบ/ดีดราคาจาก Admin
        // และยังไม่ถูกส่งให้ลูกค้าเห็น จึงต้องพักที่ wait_review ก่อน ไม่ใช่ wait_confirm
        $data = [
            'status'               => self::STATUS_WAIT_REVIEW,
            'partner_quote_amount' => $quote_amount,
            'partner_quote_detail' => $quote_detail,
            'quote_amount'         => $quote_amount,
            'updated_at'           => date('Y-m-d H:i:s'),
        ];
        if ($quote_file !== null) {
            $data['quote_file'] = $quote_file;
        }
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function get_summary_counts()
    {
        $statuses = [
            self::STATUS_PENDING,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAIT_QUOTE,
            self::STATUS_WAIT_REVIEW,
            self::STATUS_WAIT_CONFIRM,
            self::STATUS_COMPLETED,
        ];
        $counts = [];
        foreach ($statuses as $s) {
            $counts[$s] = $this->db->where('status', $s)->count_all_results($this->table);
        }
        return $counts;
    }
}
