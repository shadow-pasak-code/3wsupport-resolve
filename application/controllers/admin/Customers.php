<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model');
        $this->load->model('Device_model');
        $this->load->helper('upload');
    }

    public function index()
    {
        $customers = $this->Customer_model->get_all();
        foreach ($customers as $c) {
            $c->devices = $this->db
                ->select('d.*, p.company_name as partner_name, p.id as partner_id')
                ->from('devices d')
                ->join('partners p', 'p.id = d.partner_id', 'left')
                ->where('d.customer_id', $c->id)
                ->get()->result();
        }

        $data['customers'] = $customers;
        $data['partners']  = $this->db->where('is_active', 1)->get('partners')->result();
        $this->render('customers/index', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            // บันทึกลูกค้า
            $customer_id = $this->Customer_model->create([
                'company_name' => $this->input->post('company_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'address'      => $this->input->post('address', TRUE),
                'note'         => $this->input->post('note', TRUE),
            ]);

            // บันทึกอุปกรณ์ทุกรายการ
            $devices = $this->input->post('devices');
            if (!empty($devices)) {
                foreach ($devices as $d) {
                    // รับ SN จาก hidden field ที่ JS set ให้
                    $sn = !empty($d['serial_number'])
                        ? trim($d['serial_number'])
                        : (!empty($d['serial_suffix']) ? 'SN-' . strtoupper(trim($d['serial_suffix'])) : '');

                    // ข้ามถ้าไม่มีชื่อหรือ S/N
                    if (empty($d['name']) || empty($sn)) continue;

                    // เช็คซ้ำฝั่ง server
                    $dup = $this->db->where('serial_number', $sn)->count_all_results('devices');
                    if ($dup > 0) {
                        $this->session->set_flashdata('error', "Serial Number {$sn} ซ้ำในระบบ กรุณาตรวจสอบใหม่");
                        redirect(base_url('admin/customers/add'));
                    }

                    $this->Device_model->create([
                        'customer_id'   => $customer_id,
                        'name'          => $d['name'],
                        'serial_number' => $sn,
                        'device_type'   => $d['device_type'] ?? 'hardware',
                        'purchase_date' => !empty($d['purchase_date']) ? $d['purchase_date'] : NULL,
                        'warranty_end'  => !empty($d['warranty_end']) ? $d['warranty_end'] : NULL,
                        'partner_id'    => !empty($d['partner_id']) ? $d['partner_id'] : NULL,
                    ]);
                }
            }

            $this->session->set_flashdata('success', 'เพิ่มลูกค้าและอุปกรณ์เรียบร้อยแล้ว');
            redirect(base_url('admin/customers'));
        }
        $data['equipment_list'] = $this->db
            ->where('is_active', 1)
            ->order_by('device_type, name')
            ->get('equipment')->result();
        $data['partners'] = $this->db
            ->where('is_active', 1)
            ->get('partners')->result();
        $this->render('customers/form', $data);
    }

    public function edit($id)
    {
        $customer = $this->Customer_model->get_by_id($id);
        if (!$customer) show_404();

        if ($this->input->method() === 'post') {
            // อัปเดตข้อมูลลูกค้า
            $this->Customer_model->update($id, [
                'company_name' => $this->input->post('company_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'address'      => $this->input->post('address', TRUE),
                'note'         => $this->input->post('note', TRUE),
            ]);

            // อัปเดตอุปกรณ์ที่มีอยู่แล้ว
            $existing_devices = $this->input->post('existing_devices');
            if (!empty($existing_devices)) {
                foreach ($existing_devices as $device_id => $d) {
                    $sn = !empty($d['serial_number'])
                        ? trim($d['serial_number'])
                        : (!empty($d['serial_suffix']) ? 'SN-' . strtoupper(trim($d['serial_suffix'])) : '');

                    if (empty($sn)) continue;

                    // เช็คซ้ำ ยกเว้น device ตัวเอง
                    $dup = $this->db
                        ->where('serial_number', $sn)
                        ->where('id !=', $device_id)
                        ->count_all_results('devices');
                    if ($dup > 0) {
                        $this->session->set_flashdata('error', "Serial Number {$sn} ซ้ำในระบบ กรุณาตรวจสอบใหม่");
                        redirect(base_url('admin/customers/edit/' . $id));
                    }

                    $this->Device_model->update($device_id, [
                        'name'          => $d['name'],
                        'serial_number' => $sn,
                        'device_type'   => $d['device_type'] ?? 'hardware',
                        'purchase_date' => !empty($d['purchase_date']) ? $d['purchase_date'] : NULL,
                        'warranty_end'  => !empty($d['warranty_end']) ? $d['warranty_end'] : NULL,
                        'partner_id'    => !empty($d['partner_id']) ? $d['partner_id'] : NULL,
                    ]);
                }
            }

            // เพิ่มอุปกรณ์ใหม่
            $new_devices = $this->input->post('devices');
            if (!empty($new_devices)) {
                foreach ($new_devices as $d) {
                    $sn = !empty($d['serial_number'])
                        ? trim($d['serial_number'])
                        : (!empty($d['serial_suffix']) ? 'SN-' . strtoupper(trim($d['serial_suffix'])) : '');

                    if (empty($d['name']) || empty($sn)) continue;

                    // เช็คซ้ำ
                    $dup = $this->db->where('serial_number', $sn)->count_all_results('devices');
                    if ($dup > 0) {
                        $this->session->set_flashdata('error', "Serial Number {$sn} ซ้ำในระบบ กรุณาตรวจสอบใหม่");
                        redirect(base_url('admin/customers/edit/' . $id));
                    }

                    $this->Device_model->create([
                        'customer_id'   => $id,
                        'name'          => $d['name'],
                        'serial_number' => $sn,
                        'device_type'   => $d['device_type'] ?? 'hardware',
                        'purchase_date' => !empty($d['purchase_date']) ? $d['purchase_date'] : NULL,
                        'warranty_end'  => !empty($d['warranty_end']) ? $d['warranty_end'] : NULL,
                        'partner_id'    => !empty($d['partner_id']) ? $d['partner_id'] : NULL,
                    ]);
                }
            }

            $this->session->set_flashdata('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
            redirect(base_url('admin/customers'));
        }

        $data['customer']        = $customer;
        $data['devices']         = $this->Device_model->get_by_customer($id);
        $data['equipment_list']  = $this->db->where('is_active', 1)->order_by('device_type, name')->get('equipment')->result();
        $data['partners']        = $this->db->where('is_active', 1)->get('partners')->result();
        $this->render('customers/form', $data);
    }

    public function delete($id)
    {
        // เช็ค tickets ที่ผูกอยู่
        $ticket_count = $this->db->where('customer_id', $id)->count_all_results('tickets');
        if ($ticket_count > 0) {
            $this->session->set_flashdata('error', "ไม่สามารถลบได้ เพราะมี Ticket {$ticket_count} รายการผูกอยู่กับลูกค้านี้ กรุณาปิด Ticket ทั้งหมดก่อน");
            redirect(base_url('admin/customers'));
        }

        // ลบ devices ของลูกค้าก่อน แล้วค่อยลบลูกค้า
        $this->db->delete('devices', ['customer_id' => $id]);
        $this->Customer_model->delete($id);
        $this->session->set_flashdata('success', 'ลบลูกค้าเรียบร้อยแล้ว');
        redirect(base_url('admin/customers'));
    }

    public function reset_line($id)
    {
        $this->Customer_model->update($id, ['line_uid' => NULL]);
        $this->session->set_flashdata('success', 'รีเซ็ต Line UID เรียบร้อยแล้ว');
        redirect(base_url('admin/customers/edit/' . $id));
    }
}
