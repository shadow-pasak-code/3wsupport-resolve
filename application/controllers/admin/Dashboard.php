<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->model('Ticket_model');
        $this->Ticket_model->flag_overdue_repairs();

        // Summary
        $data['total_tickets']     = $this->db->count_all('tickets');
        $data['pending_tickets']   = $this->db->where('status', 'pending')->count_all_results('tickets');
        $data['active_tickets']    = $this->db->where_in('status', ['approved','assigned','in_progress','waiting_parts','wait_quote','wait_review','wait_confirm','quote_accepted','escalated'])->count_all_results('tickets');
        $data['completed_tickets'] = $this->db->where_in('status', ['completed','closed'])->count_all_results('tickets');
        $data['total_customers']   = $this->db->count_all('customers');
        $data['line_linked']       = $this->db->where('line_uid IS NOT NULL', NULL, FALSE)->count_all_results('customers');
        $data['total_devices']     = $this->db->count_all('devices');
        $data['warranty_valid']    = $this->db->where('warranty_end >=', date('Y-m-d'))->count_all_results('devices');
        $data['warranty_expired']  = $this->db->where('warranty_end <', date('Y-m-d'))->count_all_results('devices');

        // Ticket by status
        $status_rows = $this->db->select('status, COUNT(*) as cnt')->group_by('status')->get('tickets')->result();
        $data['ticket_by_status'] = $status_rows;

        // Ticket by month (12 เดือนล่าสุด)
        $monthly = $this->db
            ->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as cnt")
            ->where("created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)")
            ->group_by("DATE_FORMAT(created_at, '%Y-%m')")
            ->order_by('month', 'ASC')
            ->get('tickets')->result();
        $data['ticket_monthly'] = $monthly;

        // Ticket by type
        $data['hw_count'] = $this->db->where('ticket_type', 'hardware')->count_all_results('tickets');
        $data['sw_count'] = $this->db->where('ticket_type', 'software')->count_all_results('tickets');

        // Ticket by partner
        $data['ticket_by_partner'] = $this->db
            ->select('p.company_name, COUNT(t.id) as cnt')
            ->from('tickets t')
            ->join('partners p', 'p.id = t.partner_id', 'left')
            ->where_in('t.status', ['wait_quote','wait_review','wait_confirm','quote_accepted','in_progress','completed'])
            ->group_by('t.partner_id')
            ->get()->result();

        // Partner ซ่อมเสร็จแล้วรอ Admin แจ้งลูกค้า
        $data['partner_done_list'] = $this->db
            ->select('t.*, c.company_name as customer_name, d.name as device_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->where('t.status', 'partner_completed')
            ->order_by('t.updated_at', 'ASC')
            ->limit(10)
            ->get()->result();

        // Active tickets (กำลังดำเนินการ)
        $data['active_list'] = $this->db
            ->select('t.*, c.company_name as customer_name, d.name as device_name, u.name as technician_name, p.company_name as partner_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('users u', 'u.ref_id = t.technician_id AND u.role = "technician"', 'left')
            ->join('partners p', 'p.id = t.partner_id', 'left')
            ->where_in('t.status', ['assigned','in_progress','waiting_parts','wait_quote','wait_review','wait_confirm','quote_accepted','escalated'])
            ->order_by('t.created_at', 'ASC')
            ->limit(10)
            ->get()->result();

        $this->render('dashboard', $data);
    }

    public function report()
    {
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to   = $this->input->get('date_to')   ?: date('Y-m-d');
        $status    = $this->input->get('status')    ?: '';
        $type      = $this->input->get('type')      ?: '';

        $query = $this->db
            ->select('t.*, c.company_name as customer_name, c.phone,
                      d.name as device_name, d.serial_number,
                      u.name as technician_name, p.company_name as partner_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('users u', 'u.ref_id = t.technician_id AND u.role = "technician"', 'left')
            ->join('partners p', 'p.id = t.partner_id', 'left')
            ->where('t.created_at >=', $date_from . ' 00:00:00')
            ->where('t.created_at <=', $date_to   . ' 23:59:59');

        if ($status) $query->where('t.status', $status);
        if ($type)   $query->where('t.ticket_type', $type);

        $data['tickets']    = $query->order_by('t.created_at', 'DESC')->get()->result();
        $data['date_from']  = $date_from;
        $data['date_to']    = $date_to;
        $data['status']     = $status;
        $data['type']       = $type;
        $data['total']      = count($data['tickets']);
        $data['hw_count']   = count(array_filter((array)$data['tickets'], fn($t) => $t->ticket_type === 'hardware'));
        $data['sw_count']   = count(array_filter((array)$data['tickets'], fn($t) => $t->ticket_type === 'software'));

        $this->load->view('admin/report/tickets', $data);
    }
}