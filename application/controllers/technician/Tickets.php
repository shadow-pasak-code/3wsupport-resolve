<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tickets extends Tech_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ticket_model');
        $this->load->library('Line_notify');
    }

    public function index()
    {
        $this->Ticket_model->flag_overdue_repairs();

        $data['tickets'] = $this->Ticket_model->get_all([
            'technician_id' => $this->current_user['ref_id'],
        ]);
        $this->render('tickets/index', $data);
    }

    public function detail($id)
    {
        $this->Ticket_model->flag_overdue_repairs();

        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $data['ticket']            = $ticket;
        $data['partners']          = $this->db->get_where('partners', ['is_active' => 1])->result();
        $data['repair_categories'] = $this->db->where('is_active', 1)->order_by('name', 'ASC')->get('repair_categories')->result();
        $data['timeline']          = $this->Ticket_model->get_timeline($id);

        $this->render('tickets/detail', $data);
    }

    // ส่งอัพเดทความคืบหน้าระหว่างซ่อม (รูป+ข้อความ) ให้ลูกค้าที่ผูก Line ไว้ — ใช้ได้เฉพาะช่วง in_progress ที่ยังเป็นเจ้าของงานอยู่
    public function send_update($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        if ($ticket->status !== Ticket_model::STATUS_IN_PROGRESS || !empty($ticket->partner_id)) {
            $this->session->set_flashdata('error', 'ส่งอัพเดทได้เฉพาะระหว่างที่กำลังซ่อมอยู่และยังไม่ได้ส่งต่อ Partner');
            redirect(base_url('tech/tickets/detail/' . $id));
        }

        $note   = $this->input->post('note', TRUE);
        $images = $this->_upload_ticket_images();

        if (!$note && empty($images)) {
            $this->session->set_flashdata('error', 'กรุณากรอกรายละเอียดหรือแนบรูปอย่างน้อย 1 อย่าง');
            redirect(base_url('tech/tickets/detail/' . $id));
        }

        // บันทึกลง ticket_updates อย่างเดียว — ไม่ insert ซ้ำลง ticket_logs เพราะ get_timeline() รวมสองตารางนี้เป็นเส้นเดียวกันอยู่แล้ว
        $this->db->insert('ticket_updates', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'message'    => $note,
            'images'     => $images ? json_encode($images, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $image_urls = array_map(function ($img) {
                return base_url('uploads/tickets/' . $img);
            }, $images);

            $this->line_notify->push_update(
                $ticket->line_uid,
                "🔧 อัพเดทความคืบหน้า Ticket #{$id}\n" . ($note ?: '(แนบรูปประกอบ)'),
                $image_urls
            );
        }

        $this->session->set_flashdata('success', 'ส่งอัพเดทให้ลูกค้าเรียบร้อยแล้ว');
        redirect(base_url('tech/tickets/detail/' . $id));
    }

    // รับงาน + เลือกหมวดหมู่การซ่อม (เฉพาะกรณีอยู่ในประกัน) — วันเสร็จคำนวณจากหมวดหมู่ ไม่ให้ช่างกำหนดวันเอง
    public function accept($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $note     = $this->input->post('note', TRUE);
        $category = $this->_get_repair_category();
        if (!$category) {
            $this->session->set_flashdata('error', 'กรุณาเลือกหมวดหมู่การซ่อม');
            redirect(base_url('tech/tickets/detail/' . $id));
        }

        $start = date('Y-m-d');
        $end   = date('Y-m-d', strtotime("+{$category->max_days} day"));

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_IN_PROGRESS, [
            'repair_category_id' => $category->id,
            'tech_start_date'    => $start,
            'tech_end_date'      => $end,
            'tech_note'          => $note,
        ]);

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $ticket->status,
            'new_status' => Ticket_model::STATUS_IN_PROGRESS,
            'message'    => 'ช่างรับงานแล้ว หมวดหมู่: ' . $category->name . ' (ไม่เกิน ' . $category->max_days . ' วัน) คาดว่าเสร็จ ' . date('d/m/Y', strtotime($end)),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "🔧 Ticket #{$id}\n" .
                    "ส่งซ่อมสำเร็จแล้ว รอเปลี่ยนไม่เกิน {$category->max_days} วัน คาดว่าจะเสร็จวันที่ " . $this->_thai_short_date($end) . "\n" .
                    "(โดยถ้ามีการเกินกว่า {$category->max_days} วันจะเปลี่ยนสถานะเป็น \"รออะไหล่\" แทน)"
            );
        }

        $this->session->set_flashdata('success', 'รับงานเรียบร้อยแล้ว');
        redirect(base_url('tech/tickets/detail/' . $id));
    }

    // ปรับหมวดหมู่การซ่อมระหว่างทาง (เช่น เลือกผิดหมวดตอนแรก) — วันเริ่มซ่อมเดิมไม่เปลี่ยน คำนวณวันเสร็จใหม่จากหมวดหมู่ที่เลือก
    public function update_date($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $category = $this->_get_repair_category();
        if (!$category) {
            $this->session->set_flashdata('error', 'กรุณาเลือกหมวดหมู่การซ่อม');
            redirect(base_url('tech/tickets/detail/' . $id));
        }

        $start = $ticket->tech_start_date ?: date('Y-m-d');
        $end   = date('Y-m-d', strtotime($start . " +{$category->max_days} day"));

        // ปรับหมวดหมู่ใหม่ = กลับมาซ่อมต่อ ถ้าเดิมเลย deadline จนเป็น "รออะไหล่" ไปแล้ว ให้กลับเป็น "กำลังซ่อม"
        $this->Ticket_model->update_status($id, Ticket_model::STATUS_IN_PROGRESS, [
            'repair_category_id' => $category->id,
            'tech_end_date'      => $end,
        ]);

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $ticket->status,
            'new_status' => Ticket_model::STATUS_IN_PROGRESS,
            'message'    => 'ช่างปรับหมวดหมู่การซ่อมเป็น: ' . $category->name . ' (ไม่เกิน ' . $category->max_days . ' วัน) คาดว่าเสร็จ ' . date('d/m/Y', strtotime($end)),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "📅 Ticket #{$id} (ขออนุญาตแก้ไขประเภทการซ่อม)\n" .
                    "หมวดหมู่ใหม่: {$category->name}\n" .
                    "รอเปลี่ยนไม่เกิน {$category->max_days} วัน คาดว่าจะเสร็จวันที่ " . $this->_thai_short_date($end) . "\n" .
                    "(โดยถ้ามีการเกินกว่า {$category->max_days} วันจะเปลี่ยนสถานะเป็น \"รออะไหล่\" แทน)"
            );
        }

        $this->session->set_flashdata('success', 'อัปเดตกำหนดการเรียบร้อยแล้ว');
        redirect(base_url('tech/tickets/detail/' . $id));
    }

    private function _get_repair_category()
    {
        $category_id = $this->input->post('repair_category_id');
        if (!$category_id) return null;
        return $this->db->get_where('repair_categories', ['id' => $category_id, 'is_active' => 1])->row();
    }

    // วันที่แบบไทยย่อ เช่น "13 ส.ค.69" สำหรับข้อความแจ้ง Line
    private function _thai_short_date($date)
    {
        $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $ts = strtotime($date);
        $be_year = ((int) date('Y', $ts) + 543) % 100;
        return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . sprintf('%02d', $be_year);
    }

    // ใหม่: ทำใบเสนอราคา (เฉพาะกรณีหมดประกัน)
    public function quote($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $today = date('Y-m-d');
        $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;
        if ($in_warranty) {
            $this->session->set_flashdata('error', 'อุปกรณ์นี้ยังอยู่ในประกัน ไม่สามารถแนบใบเสนอราคาได้');
            redirect(base_url('tech/tickets'));
        }

        if ($this->input->method() === 'post') {
            $quote_file = $ticket->quote_file;

            if (!empty($_FILES['quote_file']['name'])) {
                $this->load->helper('upload');
                $upload_path = FCPATH . 'uploads/quotations/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0755, TRUE);

                $this->load->library('upload', [
                    'upload_path'   => $upload_path,
                    'allowed_types' => 'pdf|doc|docx',
                    'max_size'      => 5120,
                    'encrypt_name'  => TRUE,
                ]);

                if ($this->upload->do_upload('quote_file')) {
                    $quote_file = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                    redirect(base_url('tech/tickets/detail/' . $id));
                }
            }

            $this->Ticket_model->set_quote(
                $id,
                $this->input->post('quote_amount'),
                $this->input->post('quote_detail'),
                $quote_file
            );

            $this->db->insert('ticket_logs', [
                'ticket_id'  => $id,
                'user_id'    => $this->current_user['id'],
                'old_status' => Ticket_model::STATUS_ASSIGNED,
                'new_status' => Ticket_model::STATUS_WAIT_REVIEW,
                'message'    => 'ช่างส่งใบเสนอราคา ฿' . number_format($this->input->post('quote_amount'), 2) . ' รอ Admin ตรวจสอบ',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->session->set_flashdata('success', 'ส่งใบเสนอราคาเรียบร้อยแล้ว');
            redirect(base_url('tech/tickets/detail/' . $id));
        }
    }

    public function complete($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $note     = $this->input->post('tech_note', TRUE) ?: $this->input->post('note', TRUE);
        $tracking = $this->input->post('tracking_no', TRUE);

        $today = date('Y-m-d');
        $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;

        if ($in_warranty) {
            // อยู่ในประกัน → completed ปกติ แจ้งลูกค้าทันที
            $this->Ticket_model->update_status($id, Ticket_model::STATUS_COMPLETED, [
                'tech_note'  => $note,
                'closed_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->db->insert('ticket_logs', [
                'ticket_id'  => $id,
                'user_id'    => $this->current_user['id'],
                'old_status' => Ticket_model::STATUS_IN_PROGRESS,
                'new_status' => Ticket_model::STATUS_COMPLETED,
                'message'    => 'ช่างซ่อมเสร็จแล้ว: ' . $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($ticket->line_uid) {
                $msg = "✅ ซ่อมเสร็จแล้วครับ Ticket #{$id}\n";
                if ($ticket->ticket_type === 'hardware') {
                    $msg .= "กรุณาติดต่อเจ้าหน้าที่เพื่อนัดรับเครื่องคืนครับ";
                } else {
                    $msg .= "แก้ไขปัญหาเรียบร้อยแล้วครับ หากมีปัญหาเพิ่มเติมสามารถแจ้งได้เลย";
                }
                $this->line_notify->push($ticket->line_uid, $msg);
            }
        } else {
            // หมดประกัน → แจ้ง Admin ก่อน (เหมือน Partner)
            $this->Ticket_model->update_status($id, 'partner_completed', [
                'tech_note'   => $note,
                'tracking_no' => $tracking,
                'closed_at'   => date('Y-m-d H:i:s'),
            ]);

            $this->db->insert('ticket_logs', [
                'ticket_id'  => $id,
                'user_id'    => $this->current_user['id'],
                'old_status' => $ticket->status,
                'new_status' => 'partner_completed',
                'message'    => 'ช่างซ่อมเสร็จแล้ว (หมดประกัน): ' . $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->session->set_flashdata('success', 'บันทึกผลการซ่อมเรียบร้อยแล้ว');
        redirect(base_url('tech/tickets'));
    }

    // ช่างซ่อมเองไม่ได้ → เลือก Partner ที่จะส่งต่อเอง พร้อมแนบรูปประกอบให้ Partner ดูก่อนรับงาน
    public function escalate($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $note       = $this->input->post('note', TRUE);
        $partner_id = $this->input->post('partner_id');

        if (!$partner_id) {
            $this->session->set_flashdata('error', 'กรุณาเลือก Partner ที่จะส่งต่องาน');
            redirect(base_url('tech/tickets/detail/' . $id));
        }

        $partner = $this->db->get_where('partners', ['id' => $partner_id])->row();
        $images  = $this->_upload_ticket_images();

        $today       = date('Y-m-d');
        $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;

        $extra = ['tech_note' => $note];
        if ($images) {
            $extra['images'] = json_encode($images, JSON_UNESCAPED_UNICODE);
        }

        if ($in_warranty) {
            // อยู่ในประกัน → Partner ทำงานเหมือนช่าง ไม่ต้องรอใบเสนอราคา
            $this->Ticket_model->update_status($id, Ticket_model::STATUS_ASSIGNED, array_merge($extra, [
                'partner_id' => $partner_id,
            ]));
            $new_status = Ticket_model::STATUS_ASSIGNED;
        } else {
            // หมดประกัน → Partner ต้องทำใบเสนอราคาก่อน
            $this->Ticket_model->assign_partner($id, $partner_id, $extra);
            $new_status = Ticket_model::STATUS_WAIT_QUOTE;
        }

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $ticket->status,
            'new_status' => $new_status,
            'message'    => 'ช่างส่งต่อ Partner: ' . ($partner->company_name ?? $partner_id) . ' — ' . $note
                . ($images ? ' (แนบรูป ' . count($images) . ' รูป)' : ''),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                $in_warranty
                    ? "🔧 Ticket #{$id} มอบหมายเจ้าหน้าที่แล้วครับ\nรอติดต่อนัดหมายครับ"
                    : "📋 Ticket #{$id} อยู่ระหว่างดำเนินการครับ\nกำลังประเมินราคา จะแจ้งให้ทราบเร็วๆ นี้ครับ"
            );
        }

        $this->session->set_flashdata('success', 'ส่งต่อให้ Partner เรียบร้อยแล้ว');
        redirect(base_url('tech/tickets/detail/' . $id));
    }

    // อัพโหลดรูปภาพประกอบการส่งต่อ (input name="images[]", เลือกได้หลายไฟล์)
    private function _upload_ticket_images()
    {
        $images = [];
        if (empty($_FILES['images']['name'][0])) {
            return $images;
        }

        $this->load->helper('upload');
        $upload_path = FCPATH . 'uploads/tickets/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0755, TRUE);

        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 8192,
            'encrypt_name'  => TRUE,
        ];
        $this->load->library('upload', $config);

        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['images']['name'][$i])) continue;

            $_FILES['ticket_image_single']['name']     = $_FILES['images']['name'][$i];
            $_FILES['ticket_image_single']['type']     = $_FILES['images']['type'][$i];
            $_FILES['ticket_image_single']['tmp_name'] = $_FILES['images']['tmp_name'][$i];
            $_FILES['ticket_image_single']['error']    = $_FILES['images']['error'][$i];
            $_FILES['ticket_image_single']['size']     = $_FILES['images']['size'][$i];

            $this->upload->initialize($config, TRUE);
            if ($this->upload->do_upload('ticket_image_single')) {
                $images[] = $this->upload->data('file_name');
            }
        }

        return $images;
    }

    private function _check_ownership($ticket)
    {
        if (!$ticket || $ticket->technician_id != $this->current_user['ref_id']) {
            show_error('คุณไม่มีสิทธิ์เข้าถึง Ticket นี้', 403);
        }
    }
}
