<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tickets extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Ticket_model', 'User_model']);
        $this->load->library('Line_notify');
    }

    public function index()
    {
        $this->Ticket_model->flag_overdue_repairs();

        $status = $this->input->get('status');
        $type   = $this->input->get('type');

        $filters = [
            'status' => $status,
            'type'   => $type,
        ];

        $base_query = $this->db
            ->select('t.*, c.company_name as customer_name, c.line_uid,
                  d.name as device_name, d.serial_number, d.warranty_end,
                  u.name as technician_name, p.company_name as partner_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('users u', 'u.ref_id = t.technician_id AND u.role = "technician"', 'left')
            ->join('partners p', 'p.id = t.partner_id', 'left');

        if ($status) {
            $base_query->where('t.status', $status);
        }

        if ($type) {
            $base_query->where('t.ticket_type', $type);
        }

        if (!$status) {
            // ไม่ได้กรองสถานะ → โชว์ทุกใบเหมือนกันเสมอ (กดเมนูตรงๆ กับกด "กรอง" ทุกสถานะ ต้องได้ผลเท่ากัน)
            // เรียงงานที่ยังค้างอยู่ไว้บนสุด งานเสร็จ/ปิดไปท้ายสุด — ต้องมีครบทุกสถานะใน FIELD
            // เพราะสถานะที่ไม่อยู่ในลิสต์จะได้ค่า 0 แล้วเด้งไปบนสุดแทน
            $base_query->order_by("FIELD(t.status,
            'pending','approved','assigned','in_progress','waiting_parts',
            'wait_quote','wait_review','wait_confirm','quote_accepted','quote_rejected','escalated',
            'partner_completed','completed','closed'
        )", NULL, FALSE);
        } else {
            $base_query->order_by('t.created_at', 'DESC');
        }

        $data['tickets'] = $base_query->order_by('c.company_name', 'ASC')->get()->result();
        $data['filters'] = $filters;
        $this->render('tickets/index', $data);
    }

    public function detail($id)
    {
        $this->Ticket_model->flag_overdue_repairs();

        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $data['ticket']      = $ticket;
        $data['technicians'] = $this->db->get_where('technicians', ['is_active' => 1])->result();
        $data['timeline']    = $this->Ticket_model->get_timeline($id);

        $queue = $this->_build_technician_queue();
        $data['tech_queue']        = $queue;
        $data['suggested_tech_id'] = $queue[0]['id'] ?? null;

        $this->render('tickets/detail', $data);
    }

    // จัดอันดับคิวช่าง: ว่างวันนี้มาก่อน (เรียงตามงานค้างน้อยสุด) แล้วตามด้วยช่างที่ติดงาน (เรียงตามวันที่จะว่างเร็วสุด)
    private function _build_technician_queue()
    {
        $today = date('Y-m-d');

        $technicians = $this->db
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('technicians')->result();

        $busy_rows = $this->db
            ->select('t.technician_id, t.tech_start_date, t.tech_end_date, c.company_name as customer_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->where_in('t.status', ['assigned', 'in_progress'])
            ->where('t.technician_id IS NOT NULL', null, false)
            ->where('t.tech_start_date IS NOT NULL', null, false)
            ->where('t.tech_start_date <=', $today)
            ->where('COALESCE(t.tech_end_date, t.tech_start_date) >=', $today)
            ->get()->result();

        $busy_map = [];
        foreach ($busy_rows as $row) {
            $until = $row->tech_end_date ?: $row->tech_start_date;
            if (!isset($busy_map[$row->technician_id]) || $until < $busy_map[$row->technician_id]['until']) {
                $busy_map[$row->technician_id] = [
                    'customer_name' => $row->customer_name,
                    'until'         => $until,
                ];
            }
        }

        $count_rows = $this->db
            ->select('technician_id, COUNT(*) as cnt')
            ->from('tickets')
            ->where('technician_id IS NOT NULL', null, false)
            ->where_not_in('status', ['completed', 'closed'])
            ->group_by('technician_id')
            ->get()->result();
        $count_map = [];
        foreach ($count_rows as $row) {
            $count_map[$row->technician_id] = (int) $row->cnt;
        }

        $queue = [];
        foreach ($technicians as $tech) {
            $busy = $busy_map[$tech->id] ?? null;
            $queue[] = [
                'id'            => $tech->id,
                'name'          => $tech->name,
                'avatar'        => $tech->avatar,
                'busy'          => (bool) $busy,
                'busy_customer' => $busy['customer_name'] ?? null,
                'busy_until'    => $busy['until'] ?? null,
                'active_count'  => $count_map[$tech->id] ?? 0,
            ];
        }

        usort($queue, function ($a, $b) {
            if ($a['busy'] !== $b['busy']) {
                return $a['busy'] ? 1 : -1;
            }
            if (!$a['busy']) {
                return $a['active_count'] <=> $b['active_count'] ?: strcmp($a['name'], $b['name']);
            }
            return strcmp((string) $a['busy_until'], (string) $b['busy_until']);
        });

        return $queue;
    }

    public function approve($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_APPROVED);
        $this->_log($id, $ticket->status, Ticket_model::STATUS_APPROVED, 'Admin อนุมัติ Ticket');

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "✅ Ticket #{$id} ได้รับการอนุมัติแล้วครับ\n" .
                    "กำลังดำเนินการมอบหมายเจ้าหน้าที่\n" .
                    "จะแจ้งให้ทราบอีกครั้งครับ"
            );
        }

        $this->session->set_flashdata('success', 'อนุมัติ Ticket เรียบร้อยแล้ว');
        redirect(base_url('admin/tickets/detail/' . $id));
    }

    public function reject($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $reason = $this->input->post('reason', TRUE) ?: 'ไม่ผ่านการอนุมัติ';
        $this->Ticket_model->update_status($id, Ticket_model::STATUS_CLOSED, [
            'tech_note' => $reason,
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_log($id, $ticket->status, Ticket_model::STATUS_CLOSED, 'Admin ไม่อนุมัติ: ' . $reason);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "❌ Ticket #{$id} ไม่ผ่านการอนุมัติครับ\n" .
                    "เหตุผล: {$reason}\n" .
                    "กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามเพิ่มเติม"
            );
        }

        $this->session->set_flashdata('success', 'ปฏิเสธ Ticket เรียบร้อยแล้ว');
        redirect(base_url('admin/tickets'));
    }

    // มอบหมายช่างเสมอ (ไม่ว่าประกันจะหมดหรือไม่) — ช่างเป็นคนตัดสินใจเองในหน้ารับงานว่าจะรับหรือส่งต่อ Partner
    // และถ้าหมดประกัน ช่างจะเลือกหมวดหมู่การซ่อมก่อน แล้วค่อยกรอกใบเสนอราคาทีหลัง (ไม่ใช่ assign ตรงเข้า wait_quote แบบเดิมอีกต่อไป)
    public function assign($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $tech_id = $this->input->post('technician_id');

        if ($tech_id) {
            $tech = $this->db->get_where('technicians', ['id' => $tech_id])->row();

            $this->Ticket_model->assign_technician($id, $tech_id);
            $this->_log(
                $id,
                $ticket->status,
                Ticket_model::STATUS_ASSIGNED,
                'Assign ให้ช่าง: ' . ($tech->name ?? $tech_id)
            );

            if ($ticket->line_uid) {
                $this->line_notify->push(
                    $ticket->line_uid,
                    "🔧 Ticket #{$id} มอบหมายช่างแล้วครับ\n" .
                        "ช่าง: " . ($tech->name ?? '') . "\n" .
                        "รอช่างติดต่อนัดหมายครับ"
                );
            }
            $this->session->set_flashdata('success', 'มอบหมายช่างเรียบร้อยแล้ว');
        } else {
            $this->session->set_flashdata('error', 'กรุณาเลือกช่าง');
        }

        redirect(base_url('admin/tickets/detail/' . $id));
    }

    public function send_quote($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        log_message('error', 'SEND_QUOTE ticket_id=' . $id . ' line_uid=' . $ticket->line_uid . ' quote_amount=' . $ticket->quote_amount);
        if (!$ticket || !$ticket->quote_amount) {
            $this->session->set_flashdata('error', 'ยังไม่มีใบเสนอราคา');
            redirect(base_url('admin/tickets/detail/' . $id));
        }

        if (!$ticket->line_uid) {
            $this->session->set_flashdata('error', 'ลูกค้ายังไม่ได้ผูก Line OA');
            redirect(base_url('admin/tickets/detail/' . $id));
        }

        // Admin แก้ข้อความ "รายละเอียด" ที่จะโชว์ในใบเสนอราคาก่อนส่งจริงได้ (ถ้าส่งมา)
        $quote_detail   = $this->input->post('quote_detail', TRUE);
        $detail_changed = false;
        if ($quote_detail !== null && trim($quote_detail) !== '') {
            $detail_changed = trim($quote_detail) !== trim((string) $ticket->quote_detail);
            $this->db->update('tickets', ['quote_detail' => $quote_detail], ['id' => $id]);
            // no-op ถ้ายังไม่มีใบของแอดมิน (เคสใบจากช่างที่ส่งตรงได้เลย ไม่ต้องออกใบซ้ำ)
            $this->db->update('quotations', ['note' => $quote_detail], ['ticket_id' => $id]);
            $ticket->quote_detail = $quote_detail;
        } elseif (empty($ticket->quote_detail) && !empty($ticket->partner_quote_detail)) {
            // ส่งตรงจากใบต้นฉบับโดยไม่แก้อะไร — คัดลอกรายละเอียดมาไว้ให้หน้าใบเสนอราคาฝั่งลูกค้าใช้แสดงผล
            $this->db->update('tickets', ['quote_detail' => $ticket->partner_quote_detail], ['id' => $id]);
            $ticket->quote_detail = $ticket->partner_quote_detail;
        }

        $pdf_url = $ticket->quote_file
            ? base_url('uploads/quotations/' . $ticket->quote_file)
            : null;

        $flex = $this->_build_quote_flex($ticket, $pdf_url);
        $this->line_notify->push_flex(
            $ticket->line_uid,
            "ใบเสนอราคา Ticket #{$id}",
            $flex
        );

        // ตอนนี้ลูกค้าเห็นใบเสนอราคาแล้วจริงๆ ถึงจะเข้าสถานะ wait_confirm (รอลูกค้ายืนยัน)
        $this->Ticket_model->update_status($id, Ticket_model::STATUS_WAIT_CONFIRM);
        $log_msg = 'Admin ตรวจสอบใบเสนอราคาแล้ว และส่งขออนุมัติจากลูกค้าทาง Line (฿'
            . number_format($ticket->quote_amount, 2) . ')'
            . ($detail_changed ? ' — มีการแก้ไขรายละเอียดก่อนส่ง' : '');
        $this->_log($id, $ticket->status, Ticket_model::STATUS_WAIT_CONFIRM, $log_msg);
        $this->session->set_flashdata('success', 'ส่งใบเสนอราคาให้ลูกค้าเรียบร้อยแล้ว');
        redirect(base_url('admin/tickets/detail/' . $id));
    }

    public function close($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_CLOSED, [
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_log($id, $ticket->status, Ticket_model::STATUS_CLOSED, 'Admin ปิด Ticket');

        $this->session->set_flashdata('success', 'ปิด Ticket เรียบร้อยแล้ว');
        redirect(base_url('admin/tickets'));
    }

    private function _build_quote_flex($ticket, $pdf_url)
    {
        $actions = [
            ['type' => 'message', 'label' => '✅ ยืนยันซ่อม', 'text' => 'ยืนยัน:' . $ticket->id],
            ['type' => 'message', 'label' => '❌ ปฏิเสธ',     'text' => 'ปฏิเสธ:' . $ticket->id],
        ];

        array_unshift($actions, [
            'type'  => 'uri',
            'label' => '📄 ดูใบเสนอราคา',
            'uri'   => base_url('quotation/view/' . $ticket->id),
        ]);

        if ($pdf_url) {
            array_unshift($actions, [
                'type'  => 'uri',
                'label' => '📎 ดาวน์โหลดเอกสาร',
                'uri'   => $pdf_url,
            ]);
        }

        return [
            'type' => 'bubble',
            'body' => [
                'type'     => 'box',
                'layout'   => 'vertical',
                'contents' => [
                    ['type' => 'text', 'text' => '📄 ใบเสนอราคา', 'weight' => 'bold', 'size' => 'lg'],
                    ['type' => 'text', 'text' => "Ticket #{$ticket->id}", 'color' => '#888888', 'size' => 'sm'],
                    ['type' => 'separator', 'margin' => 'md'],
                    ['type' => 'box', 'layout' => 'vertical', 'margin' => 'md', 'contents' => [
                        ['type' => 'box', 'layout' => 'horizontal', 'contents' => [
                            ['type' => 'text', 'text' => 'อุปกรณ์',    'color' => '#888888', 'size' => 'sm', 'flex' => 2],
                            ['type' => 'text', 'text' => $ticket->device_name, 'size' => 'sm', 'flex' => 3, 'wrap' => true],
                        ]],
                        ['type' => 'box', 'layout' => 'horizontal', 'margin' => 'sm', 'contents' => [
                            ['type' => 'text', 'text' => 'รายละเอียด', 'color' => '#888888', 'size' => 'sm', 'flex' => 2],
                            ['type' => 'text', 'text' => !empty($ticket->quote_detail) ? $ticket->quote_detail : '—', 'size' => 'sm', 'flex' => 3, 'wrap' => true],
                        ]],
                        ['type' => 'box', 'layout' => 'horizontal', 'margin' => 'sm', 'contents' => [
                            ['type' => 'text', 'text' => 'ราคารวม',    'color' => '#888888', 'size' => 'sm', 'flex' => 2],
                            ['type' => 'text', 'text' => '฿' . number_format($ticket->quote_amount, 2), 'size' => 'sm', 'flex' => 3, 'color' => '#E8593C', 'weight' => 'bold'],
                        ]],
                    ]],
                    ['type' => 'text', 'text' => 'กรุณายืนยันหรือปฏิเสธใบเสนอราคานี้', 'size' => 'xs', 'color' => '#888888', 'margin' => 'md', 'wrap' => true],
                ],
            ],
            'footer' => [
                'type'     => 'box',
                'layout'   => 'vertical',
                'contents' => array_map(function ($a) {
                    return [
                        'type'   => 'button',
                        'action' => $a,
                        'style'  => strpos($a['label'], '✅') !== false ? 'primary' : (strpos($a['label'], '❌') !== false ? 'secondary' : 'link'),
                        'margin' => 'sm',
                    ];
                }, $actions),
            ],
        ];
    }

    private function _parse_quote_detail_lines($text)
    {
        $items = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (preg_match('/^(.+)\s+x(\d+)\s+@\s+฿([\d.]+)$/', $line, $m)) {
                $items[] = ['name' => trim($m[1]), 'qty' => (int)$m[2], 'price' => (float)$m[3]];
            } else {
                $items[] = ['name' => $line, 'qty' => 1, 'price' => 0];
            }
        }
        return $items;
    }

    private function _log($ticket_id, $old, $new, $msg = '')
    {
        $this->db->insert('ticket_logs', [
            'ticket_id'  => $ticket_id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $old,
            'new_status' => $new,
            'message'    => $msg,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function quotation($ticket_id)
    {
        $ticket = $this->Ticket_model->get_by_id($ticket_id);
        if (!$ticket) show_404();

        $quotation     = $this->db->get_where('quotations', ['ticket_id' => $ticket_id])->row();
        $prefill_items = [];

        if (!$quotation && $ticket->quote_detail) {
            // มีร่างใบเสนอราคาของ admin เองอยู่แล้ว (เช่นเคยออกไว้ก่อนหน้า) ใช้อันนี้ก่อน
            $prefill_items = $this->_parse_quote_detail_lines($ticket->quote_detail);
        } elseif (!$quotation && $ticket->partner_quote_detail) {
            // ยังไม่เคยออกใบเสนอราคาเอง แต่ช่าง/Partner ตีราคามาแล้ว ใช้เป็นราคาตั้งต้นให้ admin ตรวจสอบ
            $prefill_items = $this->_parse_quote_detail_lines($ticket->partner_quote_detail);
        } elseif (!$quotation && $ticket->partner_quote_amount) {
            $origin_label = !empty($ticket->partner_id) ? 'Partner' : 'ช่าง';
            $prefill_items = [['name' => 'ค่าซ่อม (อ้างอิงราคาที่' . $origin_label . 'เสนอ)', 'qty' => 1, 'price' => (float)$ticket->partner_quote_amount]];
        }

        $data['ticket']        = $ticket;
        $data['quotation']     = $quotation;
        $data['prefill_items'] = $prefill_items;
        // ช่างในบริษัท → ล็อกยอดรวมไว้เท่าต้นฉบับ (ดีดราคาไม่ได้) / Partner → บวกส่วนต่างได้แต่ห้ามต่ำกว่า
        $data['is_partner_quote'] = !empty($ticket->partner_id);
        $this->render('tickets/quotation', $data);
    }

    public function save_quotation($ticket_id)
    {
        if ($this->input->method() !== 'post') redirect(base_url('admin/tickets/detail/' . $ticket_id));

        $ticket = $this->Ticket_model->get_by_id($ticket_id);
        if (!$ticket) show_404();

        $items      = $this->input->post('items');
        $vat        = (int)$this->input->post('vat');
        $note       = $this->input->post('note', TRUE);
        $subtotal   = (float)$this->input->post('subtotal');
        $vat_amount = (float)$this->input->post('vat_amount');
        $total      = (float)$this->input->post('total');

        // ที่มาของใบเสนอราคาต้นฉบับต่างกัน กฎเรื่องราคาก็ต่างกัน:
        //  - Partner (ผู้รับเหมาภายนอก) → บริษัทบวกส่วนต่างได้ แต่ห้ามต่ำกว่าที่ Partner เสนอ
        //  - ช่างในบริษัท → ราคาที่ช่างตีมาคือราคาบริษัทอยู่แล้ว แอดมิน "ดีดราคา" ไม่ได้ ตรวจสอบ/แก้ข้อความได้อย่างเดียว
        //    ถ้าตัวเลขผิดจริงต้องตีกลับให้ช่างแก้มาใหม่ ไม่ใช่แอดมินแก้ยอดเอง
        if ($ticket->partner_quote_amount) {
            $original = (float) $ticket->partner_quote_amount;

            if (!empty($ticket->partner_id)) {
                if ($total < $original) {
                    $this->session->set_flashdata('error',
                        'ยอดรวมต้องไม่ต่ำกว่าราคาที่ Partner เสนอ (฿' . number_format($original, 2) . ')');
                    redirect(base_url('admin/tickets/quotation/' . $ticket_id));
                }
            } elseif (abs($total - $original) >= 0.01) {
                $this->session->set_flashdata('error',
                    'ยอดรวมต้องเท่ากับราคาที่ช่างเสนอ (฿' . number_format($original, 2) . ') — '
                        . 'แอดมินแก้ไขได้เฉพาะรายละเอียด/ข้อความ หากราคาไม่ถูกต้องกรุณาให้ช่างแก้ใบเสนอราคามาใหม่');
                redirect(base_url('admin/tickets/quotation/' . $ticket_id));
            }
        }

        $existing = $this->db->get_where('quotations', ['ticket_id' => $ticket_id])->row();

        $qdata = [
            'ticket_id'  => $ticket_id,
            'items'      => json_encode($items, JSON_UNESCAPED_UNICODE),
            'vat'        => $vat,
            'subtotal'   => $subtotal,
            'vat_amount' => $vat_amount,
            'total'      => $total,
            'note'       => $note,
            'created_by' => $this->current_user['id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->update('quotations', $qdata, ['ticket_id' => $ticket_id]);
        } else {
            $qdata['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('quotations', $qdata);
            // ใบเสนอราคาที่ Admin เพิ่งออก ยังไม่ได้ถูกส่งให้ลูกค้าดู/ยืนยัน
            // ต้องกด "ส่งให้ลูกค้าผ่าน Line" (send_quote) ก่อน ถึงจะเข้าสถานะ wait_confirm จริง
            $this->Ticket_model->update_status($ticket_id, Ticket_model::STATUS_WAIT_REVIEW, [
                'quote_amount' => $total,
                'quote_detail' => $note,
            ]);
        }

        $this->db->update('tickets', [
            'quote_amount' => $total,
            'updated_at'   => date('Y-m-d H:i:s'),
        ], ['id' => $ticket_id]);

        // บันทึกลงไทม์ไลน์ให้เห็นว่าแอดมินตรวจแล้วแก้อะไรบ้าง (ยอดเดิม → ยอดใหม่ ถ้ามีการเปลี่ยน)
        if ($existing) {
            $prev_total = (float) $existing->total;
            $log_msg = abs($total - $prev_total) >= 0.01
                ? 'Admin แก้ไขใบเสนอราคา: ฿' . number_format($prev_total, 2) . ' → ฿' . number_format($total, 2)
                : 'Admin แก้ไขรายละเอียดใบเสนอราคา (ยอดรวมคงเดิม ฿' . number_format($total, 2) . ')';
        } else {
            $origin  = (float) ($ticket->partner_quote_amount ?: 0);
            $log_msg = 'Admin ตรวจสอบใบเสนอราคาแล้ว ฿' . number_format($total, 2);
            if ($origin && abs($total - $origin) >= 0.01) {
                $log_msg .= ' (ต้นฉบับ ฿' . number_format($origin, 2) . ')';
            }
        }
        $this->_log($ticket_id, $ticket->status, $ticket->status, $log_msg);

        $this->session->set_flashdata('success', ($existing ? 'แก้ไข' : 'บันทึกการตรวจสอบ') . 'ใบเสนอราคาเรียบร้อยแล้ว');
        redirect(base_url('admin/tickets/detail/' . $ticket_id));
    }

    public function modal($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) {
            echo '<div class="text-center text-red-400 py-8">ไม่พบข้อมูล</div>';
            return;
        }

        $logs = $this->db
            ->where('ticket_id', $id)
            ->order_by('created_at', 'ASC')
            ->get('ticket_logs')->result();

        $admin_quotation = $this->db->get_where('quotations', ['ticket_id' => $id])->row();

        $data['ticket']          = $ticket;
        $data['logs']            = $logs;
        $data['admin_quotation'] = $admin_quotation;

        $this->load->view('admin/tickets/modal_detail', $data);
    }

    public function notify_complete($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) show_404();

        $message = $this->input->post('message', TRUE);

        if ($ticket->line_uid && $message) {
            $this->line_notify->push($ticket->line_uid, $message);
        }

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_COMPLETED);
        $this->_log($id, 'partner_completed', Ticket_model::STATUS_COMPLETED, 'Admin แจ้งลูกค้าว่าซ่อมเสร็จแล้ว');
        $this->session->set_flashdata('success', 'แจ้งลูกค้าเรียบร้อยแล้ว');
        redirect(base_url('admin/tickets/detail/' . $id));
    }
}
