<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tickets extends Partner_Controller
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
            'partner_id' => $this->current_user['ref_id'],
        ]);
        $this->render('tickets/index', $data);
    }

    public function detail($id)
    {
        $this->Ticket_model->flag_overdue_repairs();

        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);
        $data['ticket'] = $ticket;
        $data['logs']   = $this->db
            ->where('ticket_id', $id)
            ->order_by('created_at', 'ASC')
            ->get('ticket_logs')->result();
        $this->render('tickets/detail', $data);
    }

    // ใหม่: รับงาน + กำหนดวัน (เฉพาะกรณีอยู่ในประกัน)
    public function accept($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $start = $this->input->post('start_date');
        $end   = $this->input->post('end_date');
        $note  = $this->input->post('note', TRUE);

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_IN_PROGRESS, [
            'tech_start_date' => $start,
            'tech_end_date'   => $end,
            'tech_note'       => $note,
        ]);

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $ticket->status,
            'new_status' => Ticket_model::STATUS_IN_PROGRESS,
            'message'    => 'รับงานแล้ว กำหนดวันซ่อม ' . date('d/m/Y', strtotime($start)) . ' ถึง ' . date('d/m/Y', strtotime($end)),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "🔧 รับงานซ่อม Ticket #{$id} แล้วครับ\n" .
                    "วันเริ่มซ่อม: " . date('d/m/Y', strtotime($start)) . "\n" .
                    "คาดว่าเสร็จ: " . date('d/m/Y', strtotime($end)) . "\n" .
                    "จะติดต่อนัดหมายอีกครั้งครับ"
            );
        }

        $this->session->set_flashdata('success', 'รับงานเรียบร้อยแล้ว');
        redirect(base_url('partner/tickets/detail/' . $id));
    }

    // ใหม่: แก้ไขวันที่กำหนด
    public function update_date($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $start = $this->input->post('start_date');
        $end   = $this->input->post('end_date');

        $this->Ticket_model->update_status($id, $ticket->status, [
            'tech_start_date' => $start,
            'tech_end_date'   => $end,
        ]);

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => $ticket->status,
            'new_status' => $ticket->status,
            'message'    => 'อัปเดตวันกำหนดเสร็จเป็น ' . date('d/m/Y', strtotime($end)),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ticket->line_uid) {
            $this->line_notify->push(
                $ticket->line_uid,
                "📅 อัปเดตกำหนดการซ่อม Ticket #{$id}\n" .
                    "วันเริ่มซ่อม: " . date('d/m/Y', strtotime($start)) . "\n" .
                    "คาดว่าเสร็จ: " . date('d/m/Y', strtotime($end))
            );
        }

        $this->session->set_flashdata('success', 'อัปเดตวันเรียบร้อยแล้ว');
        redirect(base_url('partner/tickets/detail/' . $id));
    }

    public function quote($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $today = date('Y-m-d');
        $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;
        if ($in_warranty) {
            $this->session->set_flashdata('error', 'อุปกรณ์นี้ยังอยู่ในประกัน ไม่สามารถแนบใบเสนอราคาได้');
            redirect(base_url('partner/tickets'));
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
                    redirect(base_url('partner/tickets/detail/' . $id));
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
                'old_status' => $ticket->status,
                'new_status' => Ticket_model::STATUS_WAIT_REVIEW,
                'message'    => 'Partner ส่งใบเสนอราคา ฿' . number_format($this->input->post('quote_amount'), 2) . ' รอ Admin ตรวจสอบ',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->session->set_flashdata('success', 'ส่งใบเสนอราคาเรียบร้อยแล้ว');
            redirect(base_url('partner/tickets/detail/' . $id));
        }
    }

    public function complete($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $note     = $this->input->post('note', TRUE) ?: $this->input->post('tech_note', TRUE);
        $tracking = $this->input->post('tracking_no', TRUE);

        // ถ้าอยู่ในประกัน → completed ปกติ (เหมือนช่าง)
        $today = date('Y-m-d');
        $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;

        if ($in_warranty) {
            $this->Ticket_model->update_status($id, Ticket_model::STATUS_COMPLETED, [
                'tech_note'  => $note,
                'closed_at'  => date('Y-m-d H:i:s'),
            ]);
            $this->db->insert('ticket_logs', [
                'ticket_id'  => $id,
                'user_id'    => $this->current_user['id'],
                'old_status' => Ticket_model::STATUS_IN_PROGRESS,
                'new_status' => Ticket_model::STATUS_COMPLETED,
                'message'    => 'ซ่อมเสร็จแล้ว (อยู่ในประกัน): ' . $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($ticket->line_uid) {
                $this->line_notify->push(
                    $ticket->line_uid,
                    "✅ ซ่อมเสร็จแล้วครับ Ticket #{$id}\n" .
                        "กรุณาติดต่อเจ้าหน้าที่เพื่อนัดรับเครื่องคืนครับ"
                );
            }
        } else {
            // หมดประกัน → เหมือนเดิม แจ้ง Admin ก่อน
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
                'message'    => 'Partner ซ่อมเสร็จแล้ว',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->session->set_flashdata('success', 'บันทึกผลการซ่อมเรียบร้อยแล้ว');
        redirect(base_url('partner/tickets'));
    }

    // ใหม่: ส่งต่อ Admin (เผื่อกรณีอยู่ในประกันแต่เกินความสามารถ)
    public function escalate($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        $this->_check_ownership($ticket);

        $note = $this->input->post('note', TRUE);

        $this->Ticket_model->update_status($id, Ticket_model::STATUS_ESCALATED, [
            'tech_note' => $note,
        ]);

        $this->db->insert('ticket_logs', [
            'ticket_id'  => $id,
            'user_id'    => $this->current_user['id'],
            'old_status' => Ticket_model::STATUS_IN_PROGRESS,
            'new_status' => Ticket_model::STATUS_ESCALATED,
            'message'    => 'ส่งต่อ Admin: ' . $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'แจ้ง Admin เรียบร้อยแล้ว');
        redirect(base_url('partner/tickets'));
    }

    private function _check_ownership($ticket)
    {
        if (!$ticket || $ticket->partner_id != $this->current_user['ref_id']) {
            show_error('คุณไม่มีสิทธิ์เข้าถึง Ticket นี้', 403);
        }
    }
}
