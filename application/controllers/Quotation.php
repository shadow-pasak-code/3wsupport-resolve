<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quotation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('app_config');
        $this->load->model('Ticket_model');
    }

    public function view($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket || !$ticket->quote_amount) show_404();

        // ดึงใบเสนอราคาของ Admin ก่อน ถ้าไม่มีค่อยใช้ของ Partner
        $quotation = $this->db->get_where('quotations', ['ticket_id' => $id])->row();

        $data['ticket']    = $ticket;
        $data['quotation'] = $quotation;

        $this->load->view('quotation/view', $data);
    }

    // ใบเสนอราคาต้นฉบับที่ "ผู้ปฏิบัติงาน" เป็นคนออก — เป็นได้ทั้งช่างในบริษัทและ Partner ภายนอก
    // (ทั้งสองฝั่งเขียนลงคอลัมน์ partner_quote_* ชุดเดียวกันผ่าน Ticket_model::set_quote())
    public function partner($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket || !$ticket->quote_amount) show_404();

        $is_partner = !empty($ticket->partner_id);
        $partner    = $is_partner
            ? $this->db->get_where('partners', ['id' => $ticket->partner_id])->row()
            : null;

        $data['ticket']     = $ticket;
        $data['partner']    = $partner;
        $data['is_partner'] = $is_partner;

        if ($is_partner) {
            $data['issuer_label'] = 'ใบเสนอราคา (Partner)';
            $data['issuer_label_en'] = 'QUOTATION FROM PARTNER';
            $data['issuer_name']  = $partner->company_name ?? '—';
            $data['issuer_phone'] = $partner->phone ?? null;
        } else {
            $data['issuer_label'] = 'ใบเสนอราคา (จากช่าง)';
            $data['issuer_label_en'] = 'QUOTATION FROM TECHNICIAN';
            $data['issuer_name']  = $this->config->item('company_name');
            $data['issuer_phone'] = $this->config->item('company_phone');
        }

        $this->load->view('quotation/partner_view', $data);
    }
}
