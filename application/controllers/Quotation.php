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

    public function partner($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket || !$ticket->quote_amount) show_404();

        $partner = $this->db->get_where('partners', ['id' => $ticket->partner_id])->row();

        $data['ticket']  = $ticket;
        $data['partner'] = $partner;

        $this->load->view('quotation/partner_view', $data);
    }
}
