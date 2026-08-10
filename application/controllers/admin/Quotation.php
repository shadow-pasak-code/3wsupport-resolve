<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ticket_model');
    }

    public function view($id)
    {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket || !$ticket->quote_amount) show_404();

        // โหลดข้อมูล partner
        $partner = $this->db->get_where('partners', ['id' => $ticket->partner_id])->row();

        $data['ticket']  = $ticket;
        $data['partner'] = $partner;

        // โหลด view แบบไม่มี layout
        $this->load->view('admin/quotation/view', $data);
    }
}